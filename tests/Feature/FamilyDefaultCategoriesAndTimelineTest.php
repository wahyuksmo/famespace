<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Family;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class FamilyDefaultCategoriesAndTimelineTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected Family $family;

    protected function setUp(): void
    {
        parent::setUp();

        $this->family = Family::create([
            'name' => 'Keluarga Baru Testing',
            'invite_code' => strtoupper(Str::random(8)),
            'monthly_budget' => 15000000.00,
        ]);

        $proPlan = Plan::firstOrCreate(['code' => 'pro_monthly'], [
            'name' => 'Famespace Pro',
            'price' => 50000.00,
            'duration_days' => 30,
            'features' => ['overview', 'chores', 'finance', 'members', 'calendar', 'timeline', 'export'],
        ]);

        Subscription::create([
            'family_id' => $this->family->id,
            'plan_id' => $proPlan->id,
            'status' => 'active',
            'starts_at' => now(),
            'expires_at' => now()->addMonth(),
        ]);

        $this->user = User::create([
            'family_id' => $this->family->id,
            'name' => 'Kepala Keluarga',
            'email' => 'family.test.'.Str::random(6).'@keluarga.id',
            'password' => bcrypt('password'),
            'role' => 'Ayah',
            'points' => 100,
            'can_view_all_finances' => true,
        ]);
    }

    public function test_family_creation_automatically_seeds_default_categories(): void
    {
        // 1. Check Expense Categories
        $expectedExpenses = [
            'Belanja / Kebutuhan Pokok',
            'Tagihan',
            'Servis Kendaraan',
            'Hiburan',
            'Kesehatan',
            'Pendidikan',
            'Transportasi',
            'Sedekah',
            'Hadiah',
            'Keluarga',
            'Lain-lain',
        ];

        foreach ($expectedExpenses as $exp) {
            $this->assertDatabaseHas('m_categories', [
                'family_id' => $this->family->id,
                'name' => $exp,
                'type' => 'expense',
            ]);
        }

        // 2. Check Income Categories
        $expectedIncomes = [
            'Gaji',
            'Usaha',
            'Bonus',
            'Hadiah',
            'Refund/Cashback',
            'Komisi',
            'Lain-lain',
        ];

        foreach ($expectedIncomes as $inc) {
            $this->assertDatabaseHas('m_categories', [
                'family_id' => $this->family->id,
                'name' => $inc,
                'type' => 'income',
            ]);
        }

        // 3. Check Timeline Categories
        $this->assertDatabaseHas('m_categories', [
            'family_id' => $this->family->id,
            'name' => 'Wisata Alam',
            'type' => 'timeline',
        ]);
    }

    public function test_user_can_create_timeline_moment_with_existing_category(): void
    {
        $category = Category::where('family_id', $this->family->id)
            ->where('type', 'timeline')
            ->where('name', 'Wisata Alam')
            ->first();

        $this->assertNotNull($category);

        $response = $this->actingAs($this->user)->postJson(route('timeline.store'), [
            'title' => 'Piknik Kebun Raya',
            'category_id' => $category->id,
            'event_date' => now()->toDateString(),
            'story' => 'Menikmati udara sejuk dan bersepeda bersama.',
            'location_name' => 'Kebun Raya Bogor',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('t_timeline_moments', [
            'family_id' => $this->family->id,
            'title' => 'Piknik Kebun Raya',
            'category_id' => $category->id,
        ]);
    }

    public function test_user_can_create_timeline_moment_with_new_category_on_the_fly(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('timeline.store'), [
            'title' => 'Camping Gunung Pancar',
            'category_id' => 'Camping & Petualangan',
            'event_date' => now()->toDateString(),
            'story' => 'Mendirikan tenda di antara pohon pinus.',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('m_categories', [
            'family_id' => $this->family->id,
            'name' => 'Camping & Petualangan',
            'type' => 'timeline',
        ]);

        $createdCat = Category::where('family_id', $this->family->id)
            ->where('name', 'Camping & Petualangan')
            ->first();

        $this->assertDatabaseHas('t_timeline_moments', [
            'family_id' => $this->family->id,
            'title' => 'Camping Gunung Pancar',
            'category_id' => $createdCat->id,
        ]);
    }

    public function test_user_can_add_timeline_category_via_endpoint(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('timeline.categories.store'), [
            'name' => 'Wisata Religi & Ziarah',
            'icon' => '🕌',
            'color' => '#10B981',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('m_categories', [
            'family_id' => $this->family->id,
            'name' => 'Wisata Religi & Ziarah',
            'type' => 'timeline',
        ]);
    }

    public function test_timeline_categories_do_not_leak_into_finance_category_pagination(): void
    {
        $response = $this->actingAs($this->user)->getJson(route('finances.categories.paginate'));

        $response->assertStatus(200);
        $data = $response->json('data');

        foreach ($data as $catItem) {
            $this->assertContains($catItem['type'], ['income', 'expense']);
            $this->assertNotEquals('timeline', $catItem['type']);
        }
    }
}
