<?php

namespace Tests\Feature;

use App\Models\Family;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\User;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SuperAdminFeatureTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SuperAdminSeeder::class);
    }

    public function test_super_admin_can_login_with_exact_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'wahyuganteng@admin.com',
            'password' => '@Admin1234!',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
        $this->assertTrue(auth()->user()->isSuperAdmin());
    }

    public function test_regular_user_cannot_access_admin_portal(): void
    {
        $family = Family::create(['name' => 'Keluarga Budi', 'invite_code' => 'BUDI12']);
        $user = User::create([
            'name' => 'Budi',
            'email' => 'budi_test@keluarga.id',
            'password' => Hash::make('password123'),
            'role' => 'ayah',
            'family_id' => $family->id,
        ]);

        $response = $this->actingAs($user)->get('/admin');
        $response->assertStatus(403);
    }

    public function test_user_can_submit_qris_payment_and_pro_is_activated_optimistically(): void
    {
        Storage::fake('public');

        $family = Family::create(['name' => 'Keluarga Santoso', 'invite_code' => 'SANTO1']);
        $user = User::create([
            'name' => 'Santoso',
            'email' => 'santoso_test@keluarga.id',
            'password' => Hash::make('password123'),
            'role' => 'ayah',
            'family_id' => $family->id,
        ]);

        // 1. Without proof image -> 422
        $failResponse = $this->actingAs($user)->postJson(route('subscription.qris_pay'), [
            'plan_code' => 'pro_monthly',
            'transfer_time' => '14:45 WIB',
        ]);
        $failResponse->assertStatus(422);
        $failResponse->assertJsonValidationErrors(['proof_image']);

        // 2. With proof image -> 200 & activated
        $file = UploadedFile::fake()->image('struk_transfer.jpg');
        $response = $this->actingAs($user)->postJson(route('subscription.qris_pay'), [
            'plan_code' => 'pro_monthly',
            'transfer_time' => '14:45 WIB',
            'proof_image' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Check subscription is active
        $subscription = Subscription::where('family_id', $family->id)->latest('id')->first();
        $this->assertNotNull($subscription);
        $this->assertTrue($subscription->plan->isPro());

        // Check payment record is recorded
        $payment = SubscriptionPayment::where('family_id', $family->id)->latest('id')->first();
        $this->assertNotNull($payment);
        $this->assertEquals('paid', $payment->status);
        $this->assertEquals('14:45 WIB', $payment->transfer_time);
        $this->assertNotNull($payment->proof_image);
        Storage::disk('public')->assertExists($payment->proof_image);
    }

    public function test_admin_can_verify_payment(): void
    {
        $admin = User::where('email', 'wahyuganteng@admin.com')->first();
        $family = Family::create(['name' => 'Keluarga Rahmat', 'invite_code' => 'RAHM01']);
        $user = User::create([
            'name' => 'Rahmat',
            'email' => 'rahmat_test@keluarga.id',
            'password' => Hash::make('password123'),
            'role' => 'ayah',
            'family_id' => $family->id,
        ]);
        $plan = Plan::where('code', 'pro_monthly')->first();

        $payment = SubscriptionPayment::create([
            'invoice_number' => 'INV-TEST-VERIFY',
            'family_id' => $family->id,
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'amount' => $plan->price,
            'payment_method' => 'QRIS Statis',
            'status' => 'paid',
            'transfer_time' => '10:00 WIB',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.payments.verify', $payment->id));
        $response->assertSessionHas('success');

        $payment->refresh();
        $this->assertEquals('verified', $payment->status);
        $this->assertEquals($admin->id, $payment->verified_by);
        $this->assertNotNull($payment->verified_at);
    }

    public function test_admin_can_revert_fraudulent_payment_to_free_tier(): void
    {
        $admin = User::where('email', 'wahyuganteng@admin.com')->first();
        $family = Family::create(['name' => 'Keluarga Fiktif', 'invite_code' => 'FIKT01']);
        $user = User::create([
            'name' => 'Penipu',
            'email' => 'penipu_test@keluarga.id',
            'password' => Hash::make('password123'),
            'role' => 'ayah',
            'family_id' => $family->id,
        ]);
        $proPlan = Plan::where('code', 'pro_monthly')->first();

        // Initially activated as Pro
        $sub = Subscription::create([
            'family_id' => $family->id,
            'plan_id' => $proPlan->id,
            'status' => 'active',
            'starts_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);

        $payment = SubscriptionPayment::create([
            'invoice_number' => 'INV-TEST-REVERT',
            'family_id' => $family->id,
            'user_id' => $user->id,
            'plan_id' => $proPlan->id,
            'amount' => $proPlan->price,
            'payment_method' => 'QRIS Statis',
            'status' => 'paid',
            'transfer_time' => '12:00 WIB',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.payments.revert', $payment->id), [
            'admin_notes' => 'Tidak ada dana masuk di mutasi bank.',
        ]);

        $response->assertSessionHas('success');

        // Check payment updated
        $payment->refresh();
        $this->assertEquals('reverted', $payment->status);
        $this->assertEquals('Tidak ada dana masuk di mutasi bank.', $payment->admin_notes);

        // Check family subscription reverted to free
        $latestSub = Subscription::with('plan')->where('family_id', $family->id)->latest('id')->first();
        $this->assertEquals('free', $latestSub->plan->code);
    }

    public function test_admin_can_update_plan_details(): void
    {
        $admin = User::where('email', 'wahyuganteng@admin.com')->first();
        $plan = Plan::where('code', 'pro_monthly')->first();

        $response = $this->actingAs($admin)->put(route('admin.plans.update', $plan->id), [
            'name' => 'FamSpace Pro Sultan',
            'price' => 25000,
            'duration_days' => 30,
            'is_active' => '1',
        ]);

        $response->assertSessionHas('success');
        $plan->refresh();
        $this->assertEquals('FamSpace Pro Sultan', $plan->name);
        $this->assertEquals(25000, (int) $plan->price);
    }

    public function test_admin_can_manually_override_family_plan(): void
    {
        $admin = User::where('email', 'wahyuganteng@admin.com')->first();
        $family = Family::create(['name' => 'Keluarga Override', 'invite_code' => 'OVER01']);

        $response = $this->actingAs($admin)->post(route('admin.families.override_plan', $family->id), [
            'plan_code' => 'lifetime',
        ]);

        $response->assertSessionHas('success');
        $latestSub = Subscription::with('plan')->where('family_id', $family->id)->latest('id')->first();
        $this->assertEquals('lifetime', $latestSub->plan->code);
    }
}
