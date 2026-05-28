<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AttendanceClockInTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_clock_in_button_works_correctly(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-01 08:00:00'));

        /** @var User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('勤務外');
        $response->assertSee('出勤');

        $this->actingAs($user)->post(route('attendance.clock_in'));

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    public function test_user_can_clock_in_only_once_per_day(): void
{
    Carbon::setTestNow(Carbon::parse('2026-06-01 08:00:00'));

    /** @var User $user */
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)->post(route('attendance.clock_in'));

    Carbon::setTestNow(Carbon::parse('2026-06-01 18:00:00'));

    $this->actingAs($user)->post(route('attendance.clock_out'));

    Carbon::setTestNow(Carbon::parse('2026-06-01 20:00:00'));

    $this->actingAs($user)->post(route('attendance.clock_in'));

    $this->assertSame(1, DB::table('attendances')
        ->where('user_id', $user->id)
        ->count());

    $response = $this->actingAs($user)->get('/attendance');

    $response->assertStatus(200);
    $response->assertSee('退勤済');
}

public function test_clock_in_time_is_displayed_on_attendance_list(): void
{
    Carbon::setTestNow(Carbon::parse('2026-06-01 08:00:00'));

    /** @var User $user */
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)->post(route('attendance.clock_in'));

    $response = $this->actingAs($user)->get(route('attendance.list'));

    $response->assertStatus(200);
    $response->assertSee('08:00');
}
}