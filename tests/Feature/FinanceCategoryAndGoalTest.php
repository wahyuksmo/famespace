<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Family;
use App\Models\Finance;
use App\Models\FinanceGoal;
use App\Models\User;
use App\Models\Wkcal;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class FinanceCategoryAndGoalTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected Family $family;

    protected function setUp(): void
    {
        parent::setUp();

        $this->family = Family::create([
            'name' => 'Keluarga Uji Budget',
            'invite_code' => strtoupper(Str::random(8)),
            'monthly_budget' => 10000000.00,
        ]);

        $this->user = User::create([
            'family_id' => $this->family->id,
            'name' => 'Ayah Tester',
            'email' => 'tester.'.Str::random(6).'@keluarga.id',
            'password' => bcrypt('password'),
            'role' => 'Ayah',
            'points' => 100,
        ]);
    }

    public function test_user_can_create_custom_category_with_monthly_budget(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('finances.categories.store'), [
            'name' => 'Skincare & Perawatan',
            'type' => 'expense',
            'icon' => '💄',
            'monthly_budget' => '1.500.000',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('m_categories', [
            'family_id' => $this->family->id,
            'name' => 'Skincare & Perawatan',
            'monthly_budget' => 1500000.00,
        ]);
    }

    public function test_user_can_update_category_budget(): void
    {
        $cat = Category::create([
            'family_id' => $this->family->id,
            'name' => 'Kebutuhan Dapur',
            'type' => 'expense',
            'monthly_budget' => 2000000.00,
        ]);

        $response = $this->actingAs($this->user)->putJson(route('finances.categories.update', $cat->id), [
            'name' => 'Kebutuhan Dapur & Bahan Pokok',
            'type' => 'expense',
            'monthly_budget' => '2.500.000',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $cat->refresh();
        $this->assertEquals('Kebutuhan Dapur & Bahan Pokok', $cat->name);
        $this->assertEquals(2500000.00, (float) $cat->monthly_budget);
    }

    public function test_user_can_create_and_update_finance_goal(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('finances.goals.store'), [
            'title' => 'Liburan ke Raja Ampat',
            'target_amount' => '20.000.000',
            'current_amount' => '1.000.000',
            'icon' => '🏖️',
            'note' => 'Target liburan akhir tahun',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $goal = FinanceGoal::where('family_id', $this->family->id)->where('title', 'Liburan ke Raja Ampat')->first();
        $this->assertNotNull($goal);
        $this->assertEquals(20000000.00, (float) $goal->target_amount);
        $this->assertEquals(1000000.00, (float) $goal->current_amount);

        // Update goal
        $updateResp = $this->actingAs($this->user)->putJson(route('finances.goals.update', $goal->id), [
            'title' => 'Liburan ke Raja Ampat & Labuan Bajo',
            'target_amount' => '25.000.000',
            'current_amount' => '2.000.000',
            'icon' => '🏖️',
        ]);

        $updateResp->assertStatus(200);
        $goal->refresh();
        $this->assertEquals('Liburan ke Raja Ampat & Labuan Bajo', $goal->title);
        $this->assertEquals(25000000.00, (float) $goal->target_amount);
    }

    public function test_user_can_deposit_to_finance_goal_and_creates_cash_expense(): void
    {
        $goal = FinanceGoal::create([
            'family_id' => $this->family->id,
            'title' => 'Dana Darurat 2026',
            'target_amount' => 10000000.00,
            'current_amount' => 2000000.00,
            'icon' => '🛡️',
        ]);

        $response = $this->actingAs($this->user)->postJson(route('finances.goals.deposit', $goal->id), [
            'amount' => '1.500.000',
            'note' => 'Bonus proyek ayah',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $goal->refresh();
        $this->assertEquals(3500000.00, (float) $goal->current_amount);

        // Pastikan tercatat transaksi kas tabungan (bukan pengeluaran biasa)
        $this->assertDatabaseHas('t_finances', [
            'family_id' => $this->family->id,
            'user_id' => $this->user->id,
            'type' => 'saving',
            'amount' => 1500000.00,
        ]);
    }

    public function test_finance_index_displays_category_budgets_and_goals(): void
    {
        Category::create([
            'family_id' => $this->family->id,
            'name' => 'Transportasi Harian',
            'type' => 'expense',
            'monthly_budget' => 800000.00,
        ]);

        FinanceGoal::create([
            'family_id' => $this->family->id,
            'title' => 'Beli Sepeda Lipat',
            'target_amount' => 3000000.00,
            'current_amount' => 1500000.00,
            'icon' => '🚲',
        ]);

        $response = $this->actingAs($this->user)->get(route('finances.index'));

        $response->assertStatus(200);
        $response->assertSee('Transportasi Harian');
        $response->assertSee('Beli Sepeda Lipat');
        $response->assertSee('Riwayat Anggaran Bulanan');
        $response->assertSee('Target Tabungan Bersama');
    }

    public function test_user_can_set_and_view_historical_monthly_budget_for_specific_period(): void
    {
        $cat = Category::create([
            'family_id' => $this->family->id,
            'name' => 'Kuliner Lebaran',
            'type' => 'expense',
            'monthly_budget' => 1000000.00,
        ]);

        $period = now()->subMonth()->format('Y-m');

        $response = $this->actingAs($this->user)->postJson(route('finances.period_budgets.store'), [
            'category_id' => $cat->id,
            'period' => $period,
            'budget_amount' => '3.500.000',
            'notes' => 'Pagu khusus bulan lalu',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('t_category_budgets', [
            'family_id' => $this->family->id,
            'category_id' => $cat->id,
            'period' => $period,
            'budget_amount' => 3500000.00,
            'notes' => 'Pagu khusus bulan lalu',
        ]);

        // Akses halaman kas dengan filter period
        $indexResp = $this->actingAs($this->user)->get(route('finances.index', ['period' => $period]));
        $indexResp->assertStatus(200);
        $indexResp->assertSee('Kuliner Lebaran');
        $indexResp->assertSee('Anggaran Khusus');
    }

    public function test_budget_datatable_server_side_pagination_and_search(): void
    {
        Category::create([
            'family_id' => $this->family->id,
            'name' => 'Belanja Sayur Segar',
            'type' => 'expense',
            'monthly_budget' => 500000.00,
        ]);

        Category::create([
            'family_id' => $this->family->id,
            'name' => 'Bensin & Tol Mobil',
            'type' => 'expense',
            'monthly_budget' => 750000.00,
        ]);

        $response = $this->actingAs($this->user)->getJson(route('finances.budgets.paginate', [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'search' => ['value' => 'Sayur'],
            'period' => now()->format('Y-m'),
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data' => [
                '*' => ['id', 'name', 'monthly_budget', 'formatted_budget', 'spent', 'remaining', 'pct'],
            ],
            'period',
        ]);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Belanja Sayur Segar', $data[0]['name']);
    }

    public function test_category_datatable_server_side_pagination_and_search(): void
    {
        Category::create([
            'family_id' => $this->family->id,
            'name' => 'Investasi Saham & Reksadana',
            'type' => 'income',
            'monthly_budget' => 0.00,
        ]);

        Category::create([
            'family_id' => $this->family->id,
            'name' => 'Langganan Internet Wifi',
            'type' => 'expense',
            'monthly_budget' => 450000.00,
        ]);

        $response = $this->actingAs($this->user)->getJson(route('finances.categories.paginate', [
            'draw' => 2,
            'start' => 0,
            'length' => 10,
            'search' => ['value' => 'Internet'],
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data' => [
                '*' => ['id', 'name', 'type', 'type_label', 'icon', 'formatted_budget', 'is_custom'],
            ],
        ]);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Langganan Internet Wifi', $data[0]['name']);
        $this->assertEquals('expense', $data[0]['type']);
    }

    public function test_budget_periods_are_sourced_from_wkcal(): void
    {
        Wkcal::create([
            'calendar_date' => '2028-05-01',
            'calendar_year' => 2028,
            'calendar_month' => 5,
            'day_of_week' => 1,
            'day_name' => 'Senin',
            'is_working_day' => 'Y',
            'event_name' => 'Hari Kerja',
        ]);

        $response = $this->actingAs($this->user)->get(route('finances.index'));
        $response->assertStatus(200);

        $availablePeriods = $response->viewData('availablePeriods');
        $this->assertNotEmpty($availablePeriods);

        $periods = array_column($availablePeriods, 'period');
        $this->assertContains('2028-05', $periods);

        $firstPeriod = $availablePeriods[0]['period'];
        $this->assertEquals('2028-05', $firstPeriod);
    }

    public function test_finance_summary_and_report_data_endpoints(): void
    {
        // 1. Catat income, expense, saving
        Finance::create([
            'family_id' => $this->family->id,
            'user_id' => $this->user->id,
            'type' => 'income',
            'amount' => 10000000,
            'description' => 'Gaji Bulanan',
            'transaction_date' => now()->toDateString(),
        ]);

        Finance::create([
            'family_id' => $this->family->id,
            'user_id' => $this->user->id,
            'type' => 'expense',
            'amount' => 4000000,
            'description' => 'Belanja Bulanan',
            'transaction_date' => now()->toDateString(),
        ]);

        Finance::create([
            'family_id' => $this->family->id,
            'user_id' => $this->user->id,
            'type' => 'saving',
            'amount' => 2000000,
            'description' => 'Nabung Darurat',
            'transaction_date' => now()->toDateString(),
        ]);

        // 2. Test summary endpoint
        $summaryResp = $this->actingAs($this->user)->getJson(route('finances.summary'));
        $summaryResp->assertStatus(200);
        $summaryResp->assertJson([
            'success' => true,
            'income' => 10000000,
            'expense' => 4000000,
            'saving' => 2000000,
            'balance' => 4000000,
        ]);

        // 3. Test report endpoint
        $reportResp = $this->actingAs($this->user)->getJson(route('finances.report_data', [
            'mode' => 'monthly',
            'period' => now()->format('Y-m'),
        ]));
        $reportResp->assertStatus(200);
        $reportResp->assertJsonPath('report.total_income', 10000000);
        $reportResp->assertJsonPath('report.total_expense', 4000000);
        $reportResp->assertJsonPath('report.total_saving', 2000000);
        $reportResp->assertJsonPath('report.pct_saving_of_income', 20);
        $reportResp->assertJsonPath('report.pct_expense_of_income', 40);
        $reportResp->assertJsonPath('report.pct_remaining_of_income', 40);
    }
}
