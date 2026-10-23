<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wkcal;
use Tests\TestCase;

class WkcalCalendarTest extends TestCase
{
    protected function getProUser(): User
    {
        return User::where('email', 'ayah@keluarga.id')->firstOrFail();
    }

    public function test_m_wkcal_table_has_generated_records_for_2026(): void
    {
        $count = Wkcal::where('calendar_year', 2026)->count();
        $this->assertEquals(365, $count);

        // Verify working days and holidays count
        $workingDays = Wkcal::where('calendar_year', 2026)->where('is_working_day', 'Y')->count();
        $holidays = Wkcal::where('calendar_year', 2026)->where('is_working_day', 'N')->count();

        $this->assertGreaterThan(200, $workingDays);
        $this->assertGreaterThan(100, $holidays);

        // Verify prominent national holiday exists
        $independenceDay = Wkcal::where('calendar_date', '2026-08-17')->first();
        $this->assertNotNull($independenceDay);
        $this->assertEquals('N', $independenceDay->is_working_day);
        $this->assertStringContainsString('Kemerdekaan', $independenceDay->event_name);
    }

    public function test_wkcal_generate_command_executes_successfully(): void
    {
        $this->artisan('wkcal:generate 2026')
            ->expectsOutputToContain('Berhasil generate')
            ->assertSuccessful();

        $this->assertEquals(365, Wkcal::where('calendar_year', 2026)->count());
    }

    public function test_calendar_page_with_2026_and_filter_parameters(): void
    {
        $user = $this->getProUser();

        // Test normal view
        $response = $this->actingAs($user)->get('/events?year=2026&month=10&filter=all');
        $response->assertStatus(200);
        $response->assertSee('Master Kalender 2026 Aktif');
        $response->assertSee('Oktober 2026');
        $response->assertSee('Hari Kerja');
        $response->assertSee('Libur Nasional');

        // Test filter by holidays
        $holidayResponse = $this->actingAs($user)->get('/events?year=2026&month=8&filter=holidays');
        $holidayResponse->assertStatus(200);
        $holidayResponse->assertSee('Agustus 2026');
        $holidayResponse->assertSee('Kemerdekaan');

        // Test filter by workdays
        $workdayResponse = $this->actingAs($user)->get('/events?year=2026&month=10&filter=workdays');
        $workdayResponse->assertStatus(200);

        // Test filter by events
        $eventResponse = $this->actingAs($user)->get('/events?year=2026&month=10&filter=events');
        $eventResponse->assertStatus(200);
    }
}
