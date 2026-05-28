<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceClockOutTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_clock_out_button_works_correctly(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-01 08:00:00'));

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        /** @var User $user */
$user = User::factory()->create([
    'email_verified_at' => now(),
]);

        $this->actingAs($user);

        $this->post(route('attendance.clock_in'));

        Carbon::setTestNow(Carbon::parse('2026-06-01 18:00:00'));

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤');

        $this->post(route('attendance.clock_out'));

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');
        $response->assertSee('お疲れ様でした。');
    }

    public function test_clock_out_time_is_displayed_on_attendance_list(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-01 08:00:00'));

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        /** @var User $user */
$user = User::factory()->create([
    'email_verified_at' => now(),
]);

        $this->actingAs($user);

        $this->post(route('attendance.clock_in'));

        Carbon::setTestNow(Carbon::parse('2026-06-01 18:00:00'));

        $this->post(route('attendance.clock_out'));

        $response = $this->get(route('attendance.list'));

        $response->assertStatus(200);
        $response->assertSee('18:00');
    }
}