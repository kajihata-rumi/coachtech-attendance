<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceBreakTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_break_start_button_works_correctly(): void
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
        $response->assertSee('休憩入');

        Carbon::setTestNow(Carbon::parse('2026-06-01 12:00:00'));

        $this->actingAs($user)->post(route('attendance.break_start'));

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩中');
        $response->assertSee('休憩戻');
    }

    public function test_user_can_start_break_multiple_times_per_day(): void
{
    Carbon::setTestNow(Carbon::parse('2026-06-01 08:00:00'));

    /** @var User $user */
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)->post(route('attendance.clock_in'));

    Carbon::setTestNow(Carbon::parse('2026-06-01 12:00:00'));
    $this->actingAs($user)->post(route('attendance.break_start'));

    Carbon::setTestNow(Carbon::parse('2026-06-01 13:00:00'));
    $this->actingAs($user)->post(route('attendance.break_end'));

    Carbon::setTestNow(Carbon::parse('2026-06-01 15:00:00'));
    $this->actingAs($user)->post(route('attendance.break_start'));

    $response = $this->actingAs($user)->get('/attendance');

    $response->assertStatus(200);
    $response->assertSee('休憩中');
    $response->assertSee('休憩戻');
}

public function test_break_end_button_works_correctly(): void
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
    $response->assertSee('休憩戻');

    Carbon::setTestNow(Carbon::parse('2026-06-01 13:00:00'));

    $this->actingAs($user)->post(route('attendance.break_end'));

    $response = $this->actingAs($user)->get('/attendance');

    $response->assertStatus(200);
    $response->assertSee('出勤中');
    $response->assertSee('休憩入');
}

public function test_user_can_end_break_multiple_times_per_day(): void
{
    Carbon::setTestNow(Carbon::parse('2026-06-01 08:00:00'));

    /** @var User $user */
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)->post(route('attendance.clock_in'));

    Carbon::setTestNow(Carbon::parse('2026-06-01 12:00:00'));
    $this->actingAs($user)->post(route('attendance.break_start'));

    Carbon::setTestNow(Carbon::parse('2026-06-01 13:00:00'));
    $this->actingAs($user)->post(route('attendance.break_end'));

    Carbon::setTestNow(Carbon::parse('2026-06-01 15:00:00'));
    $this->actingAs($user)->post(route('attendance.break_start'));

    Carbon::setTestNow(Carbon::parse('2026-06-01 15:30:00'));
    $this->actingAs($user)->post(route('attendance.break_end'));

    $response = $this->actingAs($user)->get('/attendance');

    $response->assertStatus(200);
    $response->assertSee('出勤中');
    $response->assertSee('休憩入');
}

public function test_break_time_is_displayed_on_attendance_list(): void
{
    Carbon::setTestNow(Carbon::parse('2026-06-01 08:00:00'));

    /** @var User $user */
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)->post(route('attendance.clock_in'));

    Carbon::setTestNow(Carbon::parse('2026-06-01 12:00:00'));
    $this->actingAs($user)->post(route('attendance.break_start'));

    Carbon::setTestNow(Carbon::parse('2026-06-01 13:00:00'));
    $this->actingAs($user)->post(route('attendance.break_end'));

    $response = $this->actingAs($user)->get(route('attendance.list'));

    $response->assertStatus(200);
    $response->assertSee('1:00');
}
}