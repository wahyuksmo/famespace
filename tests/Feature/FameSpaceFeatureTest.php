<?php

namespace Tests\Feature;

use App\Events\ChoreEvent;
use App\Models\Chore;
use App\Models\Event;
use App\Models\Family;
use App\Models\Finance;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\TimelineMoment;
use App\Models\User;
use App\Modules\Dashboard\Services\DashboardOverviewService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FameSpaceFeatureTest extends TestCase
{
    public function test_landing_page_can_be_rendered(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('FamKoji');
    }

    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Selamat Datang Kembali');
    }

    public function test_demo_login_works(): void
    {
        $user = User::where('email', 'ayah@keluarga.id')->first();
        if ($user) {
            $response = $this->post('/demo-login', [
                'email' => 'ayah@keluarga.id',
            ]);
            $response->assertRedirect('/dashboard');
            $this->assertAuthenticatedAs($user);
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_dashboard_accessible_when_authenticated(): void
    {
        $user = User::where('email', 'ayah@keluarga.id')->first();
        if ($user) {
            $response = $this->actingAs($user)->get('/dashboard');
            $response->assertStatus(200);
            $response->assertSee($user->family->name);
            $response->assertSee('view-overview');
            $response->assertSee('jquery-3.7.1.min.js');
            $response->assertSee('global-ajax-progress');
            $response->assertSee('global-ajax-loader');
            $response->assertSee('cashflowChart');
            $response->assertSee('categoryDonutChart');
            $response->assertSee('memberPointsChart');
            $response->assertSee('choreRingChart');
            $response->assertDontSee('toggleTask(');

            $response->assertSee('Linimasa');
            $response->assertSee(route('timeline.index'));

            $this->actingAs($user)->get('/chores')->assertStatus(200)->assertSee('view-chores');
            $this->actingAs($user)->get('/finances')->assertStatus(200)->assertSee('view-finance');
            $this->actingAs($user)->get('/members')->assertStatus(200)->assertSee('view-members');
        }
    }

    public function test_dashboard_renders_for_free_and_pro_tiers(): void
    {
        $user = User::where('email', 'ayah@keluarga.id')->first();
        if ($user) {
            $proPlan = Plan::where('code', 'pro_monthly')->first();
            Subscription::updateOrCreate(
                ['family_id' => $user->family_id],
                [
                    'plan_id' => $proPlan->id,
                    'status' => 'active',
                    'starts_at' => now(),
                    'expires_at' => now()->addMonth(),
                ]
            );

            $responsePro = $this->actingAs($user)->get('/dashboard');
            $responsePro->assertStatus(200);
            $responsePro->assertSee('Paket FamKoji Pro Aktif');

            Subscription::where('family_id', $user->family_id)->delete();
            $responseFree = $this->actingAs($user)->get('/dashboard');
            $responseFree->assertStatus(200);
            $responseFree->assertSee('Paket Free');
            $responseFree->assertSee('Fitur Pro');
            $responseFree->assertSee('openPaywallModal');
            $responseFree->assertSee('Upgrade ke Pro');

            Subscription::create([
                'family_id' => $user->family_id,
                'plan_id' => $proPlan->id,
                'status' => 'active',
                'starts_at' => now(),
                'expires_at' => now()->addMonth(),
            ]);
        }
    }

    public function test_chore_toggle_awards_points(): void
    {
        $user = User::where('email', 'budi@keluarga.id')->first();
        if (! $user) {
            $this->assertTrue(true);

            return;
        }

        $chore = Chore::where('assigned_to_user_id', $user->id)->first();
        if ($chore) {
            $initialPoints = $user->points;
            $wasPending = $chore->status === 'pending';

            $response = $this->actingAs($user)->patchJson("/chores/{$chore->id}/toggle");
            $response->assertStatus(200);
            $response->assertJson(['success' => true]);
            $this->assertNotNull($response->json('pending_count'));

            $user->refresh();
            if ($wasPending) {
                $this->assertEquals($initialPoints + $chore->points, $user->points);
            }
        }
    }

    public function test_pro_invoice_generation_and_simulation(): void
    {
        $user = User::where('email', 'ayah@keluarga.id')->first();
        if (! $user) {
            $this->assertTrue(true);

            return;
        }

        // 1. Generate Invoice
        $response = $this->actingAs($user)->postJson('/subscriptions/invoice');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $invoiceNumber = $response->json('invoice.invoice_number');
        $this->assertNotEmpty($invoiceNumber);

        // 2. Simulate Payment
        $payResponse = $this->actingAs($user)->postJson("/subscriptions/simulate-pay/{$invoiceNumber}");
        $payResponse->assertStatus(200);
        $payResponse->assertJson(['success' => true]);

        // 3. Family should be Pro
        $user->family->refresh();
        $this->assertTrue($user->family->isPro());
    }

    public function test_lifetime_subscription_plan_and_purchase_flow(): void
    {
        $lifetimePlan = Plan::where('code', 'lifetime')->first();
        $this->assertNotNull($lifetimePlan);
        $this->assertEquals(150000.00, (float) $lifetimePlan->price);
        $this->assertTrue($lifetimePlan->isPro());
        $this->assertTrue($lifetimePlan->isLifetime());

        $user = User::where('email', 'ayah@keluarga.id')->first();
        if (! $user) {
            return;
        }

        // Generate Invoice for Lifetime
        $response = $this->actingAs($user)->postJson('/subscriptions/invoice', [
            'plan_code' => 'lifetime',
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertEquals(150000.00, (float) $response->json('invoice.amount'));

        $invoiceNumber = $response->json('invoice.invoice_number');

        // Pay for Lifetime
        $payResponse = $this->actingAs($user)->postJson("/subscriptions/simulate-pay/{$invoiceNumber}");
        $payResponse->assertStatus(200);
        $payResponse->assertJson(['success' => true]);

        $user->family->refresh();
        $this->assertTrue($user->family->isPro());

        // Can access pro route
        $eventsResponse = $this->actingAs($user)->get('/events');
        $eventsResponse->assertStatus(200);

        // Landing page shows lifetime plan
        $landing = $this->get('/');
        $landing->assertStatus(200);
        $landing->assertSee('Family Lifetime');
        $landing->assertSee('150.000');
    }

    public function test_dashboard_pagination_renders(): void
    {
        $user = User::where('email', 'ayah@keluarga.id')->first();
        $response = $this->actingAs($user)->get('/finances');
        $response->assertStatus(200);
        $response->assertSee('Menampilkan');
        $response->assertSee('transaksi');
        $response->assertSee('data-page="2"', false);
        $response->assertDontSee('finances/paginate/2');
    }

    public function test_chores_crud(): void
    {
        $user = User::where('email', 'ayah@keluarga.id')->first();

        // 1. Create
        $createResponse = $this->actingAs($user)->post('/chores', [
            'title' => 'Tugas Test CRUD',
            'points' => 30,
            'due_date' => now()->toDateString(),
        ]);
        $createResponse->assertRedirect();

        $chore = Chore::where('title', 'Tugas Test CRUD')->first();
        $this->assertNotNull($chore);

        // 2. Update
        $updateResponse = $this->actingAs($user)->put("/chores/{$chore->id}", [
            'title' => 'Tugas Test CRUD Updated',
            'points' => 45,
            'due_date' => now()->toDateString(),
        ]);
        $updateResponse->assertRedirect();
        $chore->refresh();
        $this->assertEquals('Tugas Test CRUD Updated', $chore->title);
        $this->assertEquals(45, $chore->points);

        // 3. Delete
        $deleteResponse = $this->actingAs($user)->delete("/chores/{$chore->id}");
        $deleteResponse->assertRedirect();
        $this->assertNull(Chore::find($chore->id));
    }

    public function test_finances_crud(): void
    {
        $user = User::where('email', 'ayah@keluarga.id')->first();

        // 1. Create
        $createResponse = $this->actingAs($user)->post('/finances', [
            'type' => 'expense',
            'amount' => '75000',
            'description' => 'Beli camilan sore',
            'transaction_date' => now()->toDateString(),
        ]);
        $createResponse->assertRedirect();

        $finance = Finance::where('description', 'Beli camilan sore')->first();
        $this->assertNotNull($finance);
        $this->assertEquals(75000, (int) $finance->amount);

        // 2. Update
        $updateResponse = $this->actingAs($user)->put("/finances/{$finance->id}", [
            'type' => 'expense',
            'amount' => '85000',
            'description' => 'Beli camilan sore premium',
            'transaction_date' => now()->toDateString(),
        ]);
        $updateResponse->assertRedirect();
        $finance->refresh();
        $this->assertEquals('Beli camilan sore premium', $finance->description);
        $this->assertEquals(85000, (int) $finance->amount);

        // 3. Delete
        $deleteResponse = $this->actingAs($user)->delete("/finances/{$finance->id}");
        $deleteResponse->assertRedirect();
        $this->assertNull(Finance::find($finance->id));
    }

    public function test_events_crud_for_pro_user(): void
    {
        $user = User::where('email', 'ayah@keluarga.id')->first();

        // Ensure user family is pro
        $proPlan = Plan::where('code', 'pro_monthly')->first();
        Subscription::updateOrCreate(
            ['family_id' => $user->family_id],
            [
                'plan_id' => $proPlan->id,
                'status' => 'active',
                'starts_at' => now(),
                'expires_at' => now()->addMonth(),
            ]
        );

        // 1. Create
        $createResponse = $this->actingAs($user)->post('/events', [
            'title' => 'Rapat Keluarga Rutin',
            'start_time' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'location' => 'Ruang Tengah',
        ]);
        $createResponse->assertRedirect();

        $event = Event::where('title', 'Rapat Keluarga Rutin')->first();
        $this->assertNotNull($event);

        // 2. Update
        $updateResponse = $this->actingAs($user)->put("/events/{$event->id}", [
            'title' => 'Rapat Keluarga Spesial',
            'start_time' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'location' => 'Teras Belakang',
        ]);
        $updateResponse->assertRedirect();
        $event->refresh();
        $this->assertEquals('Rapat Keluarga Spesial', $event->title);
        $this->assertEquals('Teras Belakang', $event->location);

        // 3. Delete
        $deleteResponse = $this->actingAs($user)->delete("/events/{$event->id}");
        $deleteResponse->assertRedirect();
        $this->assertNull(Event::find($event->id));
    }

    public function test_timeline_crud_for_pro_user(): void
    {
        $user = User::where('email', 'ayah@keluarga.id')->first();

        // Ensure user family is pro
        $proPlan = Plan::where('code', 'pro_monthly')->first();
        Subscription::updateOrCreate(
            ['family_id' => $user->family_id],
            [
                'plan_id' => $proPlan->id,
                'status' => 'active',
                'starts_at' => now(),
                'expires_at' => now()->addMonth(),
            ]
        );

        // 1. Create
        $createResponse = $this->actingAs($user)->post('/timeline', [
            'title' => 'Sepeda Santai Pagi',
            'story' => 'Keliling komplek bersama anak-anak',
            'location_name' => 'Taman Gasibu Bandung',
            'latitude' => -6.9004,
            'longitude' => 107.6186,
            'event_date' => now()->toDateString(),
        ]);
        $createResponse->assertRedirect();

        $moment = TimelineMoment::where('title', 'Sepeda Santai Pagi')->first();
        $this->assertNotNull($moment);
        $this->assertEquals('Taman Gasibu Bandung', $moment->location_name);
        $this->assertEquals(-6.9004, (float) $moment->latitude);
        $this->assertEquals(107.6186, (float) $moment->longitude);

        // 2. Update
        $updateResponse = $this->actingAs($user)->put("/timeline/{$moment->id}", [
            'title' => 'Sepeda Santai & Kuliner Pagi',
            'story' => 'Sarapan bubur ayam sehabis gowes',
            'location_name' => 'Gasibu & Lapangan Saparua',
            'latitude' => -6.9080,
            'longitude' => 107.6200,
            'event_date' => now()->toDateString(),
        ]);
        $updateResponse->assertRedirect();
        $moment->refresh();
        $this->assertEquals('Sepeda Santai & Kuliner Pagi', $moment->title);
        $this->assertEquals('Sarapan bubur ayam sehabis gowes', $moment->story);
        $this->assertEquals('Gasibu & Lapangan Saparua', $moment->location_name);
        $this->assertEquals(-6.9080, (float) $moment->latitude);

        // 3. Delete
        $deleteResponse = $this->actingAs($user)->delete("/timeline/{$moment->id}");
        $deleteResponse->assertRedirect();
        $this->assertNull(TimelineMoment::find($moment->id));
    }

    public function test_all_modules_ajax_crud(): void
    {
        $user = User::where('email', 'ayah@keluarga.id')->first();

        // Ensure user family is pro
        $proPlan = Plan::where('code', 'pro_monthly')->first();
        Subscription::updateOrCreate(
            ['family_id' => $user->family_id],
            [
                'plan_id' => $proPlan->id,
                'status' => 'active',
                'starts_at' => now(),
                'expires_at' => now()->addMonth(),
            ]
        );

        // 1. AJAX Chores
        $cStore = $this->actingAs($user)->postJson('/chores', [
            'title' => 'AJAX Chore Test',
            'points' => 25,
            'due_date' => now()->toDateString(),
        ]);
        $cStore->assertStatus(200)->assertJson(['success' => true]);
        $this->assertNotNull($cStore->json('pending_count'));
        $choreId = Chore::where('title', 'AJAX Chore Test')->value('id');

        $cUpdate = $this->actingAs($user)->putJson("/chores/{$choreId}", [
            'title' => 'AJAX Chore Test Updated',
            'points' => 35,
            'due_date' => now()->toDateString(),
        ]);
        $cUpdate->assertStatus(200)->assertJson(['success' => true]);

        $cDelete = $this->actingAs($user)->deleteJson("/chores/{$choreId}");
        $cDelete->assertStatus(200)->assertJson(['success' => true]);

        // 2. AJAX Finances
        $fStore = $this->actingAs($user)->postJson('/finances', [
            'type' => 'income',
            'amount' => '500000',
            'description' => 'AJAX Bonus Project',
            'transaction_date' => now()->toDateString(),
        ]);
        $fStore->assertStatus(200)->assertJson(['success' => true]);
        $finId = Finance::where('description', 'AJAX Bonus Project')->value('id');

        $fUpdate = $this->actingAs($user)->putJson("/finances/{$finId}", [
            'type' => 'income',
            'amount' => '600000',
            'description' => 'AJAX Bonus Project Updated',
            'transaction_date' => now()->toDateString(),
        ]);
        $fUpdate->assertStatus(200)->assertJson(['success' => true]);

        $fDelete = $this->actingAs($user)->deleteJson("/finances/{$finId}");
        $fDelete->assertStatus(200)->assertJson(['success' => true]);

        // 3. AJAX Events
        $eStore = $this->actingAs($user)->postJson('/events', [
            'title' => 'AJAX Family Gathering',
            'start_time' => now()->addDays(4)->format('Y-m-d H:i:s'),
            'location' => 'Bandung',
        ]);
        $eStore->assertStatus(200)->assertJson(['success' => true]);
        $eventId = Event::where('title', 'AJAX Family Gathering')->value('id');

        $eUpdate = $this->actingAs($user)->putJson("/events/{$eventId}", [
            'title' => 'AJAX Family Gathering Updated',
            'start_time' => now()->addDays(4)->format('Y-m-d H:i:s'),
            'location' => 'Lembang',
        ]);
        $eUpdate->assertStatus(200)->assertJson(['success' => true]);

        $eDelete = $this->actingAs($user)->deleteJson("/events/{$eventId}");
        $eDelete->assertStatus(200)->assertJson(['success' => true]);

        // 4. AJAX Timeline
        $tStore = $this->actingAs($user)->postJson('/timeline', [
            'title' => 'AJAX Trip Moment',
            'story' => 'Momen asyik akhir pekan',
            'location_name' => 'Ciater Subang',
            'latitude' => -6.7410,
            'longitude' => 107.6530,
            'event_date' => now()->toDateString(),
        ]);
        $tStore->assertStatus(200)->assertJson(['success' => true]);
        $momentId = TimelineMoment::where('title', 'AJAX Trip Moment')->value('id');

        // Test Map Points endpoint
        $mapPoints = $this->actingAs($user)->getJson('/timeline/map-points');
        $mapPoints->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['data' => [['id', 'title', 'location_name', 'latitude', 'longitude']]]);

        // Test Map Points search filtering
        $searchMapPoints = $this->actingAs($user)->getJson('/timeline/map-points?search=AJAX');
        $searchMapPoints->assertStatus(200)
            ->assertJson(['success' => true]);
        $this->assertNotEmpty($searchMapPoints->json('data'));

        $emptyMapPoints = $this->actingAs($user)->getJson('/timeline/map-points?search=NonExistentPlaceXYZ');
        $emptyMapPoints->assertStatus(200)
            ->assertJson(['success' => true]);
        $this->assertEmpty($emptyMapPoints->json('data'));

        $tUpdate = $this->actingAs($user)->putJson("/timeline/{$momentId}", [
            'title' => 'AJAX Trip Moment Updated',
            'story' => 'Momen asyik akhir pekan super seru',
            'location_name' => 'Pemandian Air Panas Ciater',
            'latitude' => -6.7420,
            'longitude' => 107.6540,
            'event_date' => now()->toDateString(),
        ]);
        $tUpdate->assertStatus(200)->assertJson(['success' => true]);

        $tDelete = $this->actingAs($user)->deleteJson("/timeline/{$momentId}");
        $tDelete->assertStatus(200)->assertJson(['success' => true]);

        // 5. AJAX with X-Requested-With only (no Accept: application/json)
        Chore::where('title', 'XHR Chore Test')->delete();
        $xhrResponse = $this->actingAs($user)->post('/chores', [
            'title' => 'XHR Chore Test',
            'points' => 20,
        ], ['X-Requested-With' => 'XMLHttpRequest']);
        $xhrResponse->assertStatus(200)->assertJson(['success' => true]);
        Chore::where('title', 'XHR Chore Test')->delete();

        // 6. AJAX Pagination fetch with X-Requested-With returns JSON data without html
        $ajaxPage = $this->actingAs($user)->get('/finances/paginate/2', [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);
        $ajaxPage->assertStatus(200);
        $ajaxPage->assertJsonStructure([
            'success',
            'current_page',
            'data',
            'total',
        ]);
        $this->assertArrayNotHasKey('html', $ajaxPage->json());

        // 7. Separate paginate endpoints return paginated data JSON without HTML
        $choresPaginate = $this->actingAs($user)->getJson('/chores/paginate/2');
        $choresPaginate->assertStatus(200)->assertJsonStructure(['current_page', 'data', 'total']);
        $this->assertEquals(2, $choresPaginate->json('current_page'));
        $this->assertArrayNotHasKey('html', $choresPaginate->json());

        $financesPaginate = $this->actingAs($user)->getJson('/finances/paginate/2');
        $financesPaginate->assertStatus(200)->assertJsonStructure(['current_page', 'data', 'total']);
        $this->assertEquals(2, $financesPaginate->json('current_page'));
        $this->assertArrayNotHasKey('html', $financesPaginate->json());

        $eventsPaginate = $this->actingAs($user)->getJson('/events/paginate/2');
        $eventsPaginate->assertStatus(200)->assertJsonStructure(['current_page', 'data', 'total']);
        $this->assertEquals(2, $eventsPaginate->json('current_page'));
        $this->assertArrayNotHasKey('html', $eventsPaginate->json());

        $timelinePaginate = $this->actingAs($user)->getJson('/timeline/paginate/2');
        $timelinePaginate->assertStatus(200)->assertJsonStructure(['current_page', 'data', 'total']);
        $this->assertEquals(2, $timelinePaginate->json('current_page'));
        $this->assertArrayNotHasKey('html', $timelinePaginate->json());
    }

    public function test_ui_pagination_and_tab_switching_across_all_modules(): void
    {
        $user = User::where('email', 'ayah@keluarga.id')->first();

        // Ensure user family is pro for pro module testing
        $proPlan = Plan::where('code', 'pro_monthly')->first();
        Subscription::updateOrCreate(
            ['family_id' => $user->family_id],
            [
                'plan_id' => $proPlan->id,
                'status' => 'active',
                'starts_at' => now(),
                'expires_at' => now()->addMonth(),
            ]
        );

        // 1. Check all modules render their own view on direct URL
        $modules = [
            '/dashboard' => 'view-overview',
            '/chores' => 'view-chores',
            '/finances' => 'view-finance',
            '/timeline' => 'view-timeline',
            '/events' => 'view-calendar',
            '/members' => 'view-members',
        ];

        foreach ($modules as $url => $viewId) {
            $resp = $this->actingAs($user)->get($url);
            $resp->assertStatus(200);
            $resp->assertSee($viewId);
            $this->assertEquals(1, substr_count($resp->getContent(), "id=\"{$viewId}\""), "Duplicate view detected for {$viewId}");
        }

        // 2. Check direct non-AJAX pagination redirect to module pages
        $redirectChecks = [
            '/chores/paginate/2' => route('chores.index', ['page' => 2]),
            '/finances/paginate/2' => route('finances.index', ['page' => 2]),
            '/events/paginate/2' => route('events.index', ['page' => 2]),
            '/timeline/paginate/2' => route('timeline.index', ['page' => 2]),
        ];

        foreach ($redirectChecks as $from => $to) {
            $redirectResp = $this->actingAs($user)->get($from);
            $redirectResp->assertRedirect($to);
        }

        // 3. Check module views have separate pushed scripts
        $choreView = view('modules.chore.index', [
            'chores' => collect(),
            'allChores' => collect(),
            'members' => collect(),
            'user' => $user,
            'family' => $user->family,
        ])->render();
        $this->assertStringContainsString('toggleTask', $choreView);
        $this->assertStringContainsString('openEditChoreModal', $choreView);
        $this->assertStringContainsString("moduleFormHandlers['task']", $choreView);
        $this->assertStringContainsString('fetchChoresPage', $choreView);

        $financeView = view('modules.finance.index', [
            'finance' => ['balance' => 0, 'income' => 0, 'expense' => 0, 'transactions' => collect()],
            'categories' => collect(),
            'user' => $user,
            'family' => $user->family,
        ])->render();
        $this->assertStringContainsString('openEditFinanceModal', $financeView);
        $this->assertStringContainsString("moduleFormHandlers['expense']", $financeView);
        $this->assertStringContainsString('fetchFinancePage', $financeView);
    }

    public function test_per_module_modals_and_datatables_serverside(): void
    {
        $user = User::where('email', 'ayah@keluarga.id')->first();
        if (! $user) {
            $this->assertTrue(true);

            return;
        }

        // 1. Verify layout has DataTables assets and no old mega-modal
        $financesPage = $this->actingAs($user)->get('/finances');
        $financesPage->assertStatus(200);
        $financesPage->assertSee('vendor/datatables/dataTables.min.js', false);
        $financesPage->assertSee('id="finance-modal"', false);
        $financesPage->assertSee('id="combobox-category-root"', false);
        $financesPage->assertSee('id="upload-dropzone"', false);
        $financesPage->assertSee('id="upload-preview-card"', false);
        $financesPage->assertDontSee('id="quick-modal"', false);

        // 2. Verify chore module modal
        $choresPage = $this->actingAs($user)->get('/chores');
        $choresPage->assertStatus(200);
        $choresPage->assertSee('id="chore-modal"', false);
        $choresPage->assertSee('id="combobox-category-root"', false);
        $choresPage->assertSee('id="combobox-assignee-root"', false);
        $choresPage->assertSee('id="upload-dropzone"', false);
        $choresPage->assertSee('id="upload-preview-card"', false);
        $choresPage->assertSee('id="sidebar-chore-count"', false);
        $choresPage->assertDontSee('id="quick-modal"', false);

        // 3. Verify calendar module modal
        $calendarPage = $this->actingAs($user)->get('/events');
        $calendarPage->assertStatus(200);
        $calendarPage->assertSee('id="calendar-modal"', false);
        $calendarPage->assertSee('id="combobox-category-root"', false);
        $calendarPage->assertSee('id="combobox-assignee-root"', false);
        $calendarPage->assertSee('id="upload-dropzone"', false);
        $calendarPage->assertSee('id="upload-preview-card"', false);
        $calendarPage->assertDontSee('id="quick-modal"', false);

        // 4. Verify timeline module modal
        $timelinePage = $this->actingAs($user)->get('/timeline');
        $timelinePage->assertStatus(200);
        $timelinePage->assertSee('id="timeline-modal"', false);
        $timelinePage->assertSee('id="combobox-category-root"', false);
        $timelinePage->assertSee('id="combobox-assignee-root"', false);
        $timelinePage->assertSee('id="upload-dropzone"', false);
        $timelinePage->assertSee('id="upload-preview-card"', false);
        $timelinePage->assertSee('id="timeline-main-map"', false);
        $timelinePage->assertSee('id="map-search-input"', false);
        $timelinePage->assertSee('id="timeline-picker-map"', false);
        $timelinePage->assertSee('id="picker-search-query"', false);
        $timelinePage->assertSee('id="timeline-input-location"', false);
        $timelinePage->assertDontSee('id="quick-modal"', false);

        // 5. Verify DataTables Server-Side responses for all 4 modules
        $financeDt = $this->actingAs($user)->getJson('/finances/paginate?draw=1&start=0&length=5');
        $financeDt->assertStatus(200)
            ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
        $this->assertEquals(1, $financeDt->json('draw'));

        $choreDt = $this->actingAs($user)->getJson('/chores/paginate?draw=2&start=0&length=5');
        $choreDt->assertStatus(200)
            ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
        $this->assertEquals(2, $choreDt->json('draw'));

        $calendarDt = $this->actingAs($user)->getJson('/events/paginate?draw=3&start=0&length=5');
        $calendarDt->assertStatus(200)
            ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
        $this->assertEquals(3, $calendarDt->json('draw'));

        $timelineDt = $this->actingAs($user)->getJson('/timeline/paginate?draw=4&start=0&length=4');
        $timelineDt->assertStatus(200)
            ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
        $this->assertEquals(4, $timelineDt->json('draw'));
    }

    public function test_chore_and_finance_file_upload_and_preview(): void
    {
        Storage::fake('public');
        $user = User::where('email', 'ayah@keluarga.id')->first();

        Chore::where('title', 'Tugas dengan foto panduan')->delete();
        Finance::where('description', 'Beli sabun cuci')->delete();

        // 1. Chore upload & view
        $choreFile = UploadedFile::fake()->image('tugas_cuci.jpg');
        $choreRes = $this->actingAs($user)->post('/chores', [
            'title' => 'Tugas dengan foto panduan',
            'points' => 25,
            'due_date' => now()->toDateString(),
            'attachment' => $choreFile,
        ]);
        $choreRes->assertRedirect();

        $savedChore = Chore::where('title', 'Tugas dengan foto panduan')->latest('id')->first();
        $this->assertNotNull($savedChore);
        $this->assertNotNull($savedChore->attachment_path);
        Storage::disk('public')->assertExists($savedChore->attachment_path);

        $choreDt = $this->actingAs($user)->getJson('/chores/paginate?draw=1&start=0&length=10');
        $choreDt->assertStatus(200);
        $foundChore = collect($choreDt->json('data'))->firstWhere('id', $savedChore->id);
        $this->assertNotNull($foundChore);
        $this->assertNotNull($foundChore['attachment_url']);

        // 2. Finance upload & view
        $financeFile = UploadedFile::fake()->image('struk_belanja.jpg');
        $financeRes = $this->actingAs($user)->post('/finances', [
            'type' => 'expense',
            'amount' => '75000',
            'description' => 'Beli sabun cuci',
            'transaction_date' => now()->toDateString(),
            'receipt' => $financeFile,
        ]);
        $financeRes->assertRedirect();

        $savedFinance = Finance::where('description', 'Beli sabun cuci')->latest('id')->first();
        $this->assertNotNull($savedFinance);
        $this->assertNotNull($savedFinance->receipt_path);
        Storage::disk('public')->assertExists($savedFinance->receipt_path);

        $financeDt = $this->actingAs($user)->getJson('/finances/paginate?draw=1&start=0&length=10');
        $financeDt->assertStatus(200);
        $foundFinance = collect($financeDt->json('data'))->firstWhere('id', $savedFinance->id);
        $this->assertNotNull($foundFinance);
        $this->assertNotNull($foundFinance['receipt_url']);

        // 3. Layout contains universal image preview modal
        $financesPage = $this->actingAs($user)->get('/finances');
        $financesPage->assertSee('id="image-preview-modal"', false);
        $financesPage->assertSee('showImagePreview', false);

        $choresPage = $this->actingAs($user)->get('/chores');
        $choresPage->assertSee('id="image-preview-modal"', false);
        $choresPage->assertSee('showImagePreview', false);

        $savedChore->delete();
        $savedFinance->delete();
    }

    public function test_dashboard_handles_large_dataset_without_overflow(): void
    {
        $user = User::where('email', 'ayah@keluarga.id')->first();
        if (! $user) {
            $this->assertTrue(true);

            return;
        }

        $overviewService = app(DashboardOverviewService::class);
        $data = $overviewService->getData($user->family, $user, 'overview', 1);

        $this->assertLessThanOrEqual(7, count($data['cashflowLabels']));
        $this->assertLessThanOrEqual(6, $data['expensesByCategory']->count());

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('custom-scrollbar');
    }

    public function test_transaction_and_try_catch_error_handling(): void
    {
        $user = User::where('email', 'ayah@keluarga.id')->first();
        if (! $user) {
            $this->assertTrue(true);

            return;
        }

        // 1. Non-existent chore update returns JSON error 422 without uncaught 500
        $response = $this->actingAs($user)->putJson('/chores/999999', [
            'title' => 'Tugas Invalid',
            'points' => 10,
        ]);
        $response->assertStatus(422);
        $response->assertJson(['success' => false]);

        // 2. Non-existent event update returns JSON error 422
        $eventResponse = $this->actingAs($user)->putJson('/events/999999', [
            'title' => 'Event Invalid',
            'start_time' => now()->toDateTimeString(),
        ]);
        $eventResponse->assertStatus(422);
        $eventResponse->assertJson(['success' => false]);
    }

    public function test_chore_realtime_broadcast_event(): void
    {
        \Illuminate\Support\Facades\Event::fake([ChoreEvent::class]);

        $user = User::where('email', 'ayah@keluarga.id')->firstOrFail();
        $family = $user->family;

        $this->actingAs($user)->postJson('/chores', [
            'title' => 'Tugas Realtime Test',
            'points' => 25,
        ])->assertStatus(200);

        \Illuminate\Support\Facades\Event::assertDispatched(ChoreEvent::class, function ($event) use ($family) {
            $channels = $event->broadcastOn();

            return (string) $event->familyId === (string) $family->id
                && $channels[0]->name === 'private-family.'.$family->id
                && $event->broadcastAs() === 'ChoreEvent';
        });
    }

    public function test_photo_upload_exceeding_5mb_is_allowed(): void
    {
        Storage::fake('public');
        $user = User::where('email', 'ayah@keluarga.id')->firstOrFail();
        $largePhoto = UploadedFile::fake()->image('mobile_camera_shot.jpg')->size(10240); // 10 MB

        $response = $this->actingAs($user)->post('/finances', [
            'type' => 'expense',
            'amount' => 50000,
            'description' => 'Beli perlengkapan besar',
            'transaction_date' => now()->toDateString(),
            'receipt' => $largePhoto,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('finances.index'));
    }
}
