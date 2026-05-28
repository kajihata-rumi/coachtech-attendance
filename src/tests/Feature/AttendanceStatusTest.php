<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_status_is_off_duty_when_user_has_no_attendance_today(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-01 08:00:00'));

        /** @var User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    public function test_status_is_working_when_user_has_clocked_in(): void
{
    Carbon::setTestNow(Carbon::parse('2026-06-01 08:00:00'));

    /** @var User $user */
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)->post(route('attendance.clock_in'));

    $response = $this->actingAs($user)->get('/attendance');

    $response->assertStatus(200);
    $response->assertSee('出勤中');
}

public function test_status_is_on_break_when_user_has_started_break(): void
{
    Carbon::setTestNow(Carbon::parse('2026-06-01 08:00:00'));

    /** @var User $user */
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)->post(route('attendance.clock_in'));

    Carbon::setTestNow(Carbon::parse('2026-06-01 12:00:00'));

    $this->actingAs($user)->post(route('attendance.break_start'));

    $response = $this->actingAs($user)->get('/attendance');

    $response->assertStatus(200);
    $response->assertSee('休憩中');
}

public function test_status_is_done_when_user_has_clocked_out(): void
{
    Carbon::setTestNow(Carbon::parse('2026-06-01 08:00:00'));

    /** @var User $user */
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)->post(route('attendance.clock_in'));

    Carbon::setTestNow(Carbon::parse('2026-06-01 18:00:00'));

    $this->actingAs($user)->post(route('attendance.clock_out'));

    $response = $this->actingAs($user)->get('/attendance');

    $response->assertStatus(200);
    $response->assertSee('退勤済');
}
}