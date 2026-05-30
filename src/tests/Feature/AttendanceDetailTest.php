<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_attendance_detail_name_is_login_user_name(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-15 09:00:00'));

        /** @var User $user */
        $user = User::factory()->create([
            'name' => '山田太郎',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $attendanceId = $this->createAttendance(
            userId: $user->id,
            workDate: '2026-05-01',
            clockIn: '09:00:00',
            clockOut: '18:00:00'
        );

        $response = $this->get(route('attendance.show', $attendanceId));

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
    }

    public function test_attendance_detail_date_is_selected_date(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-15 09:00:00'));

        /** @var User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $attendanceId = $this->createAttendance(
            userId: $user->id,
            workDate: '2026-05-01',
            clockIn: '09:00:00',
            clockOut: '18:00:00'
        );

        $response = $this->get(route('attendance.show', $attendanceId));

        $response->assertStatus(200);
        $response->assertSee('2026年');
        $response->assertSee('5月1日');
    }

    public function test_attendance_detail_clock_in_and_clock_out_are_displayed(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-15 09:00:00'));

        /** @var User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $attendanceId = $this->createAttendance(
            userId: $user->id,
            workDate: '2026-05-01',
            clockIn: '09:00:00',
            clockOut: '18:00:00'
        );

        $response = $this->get(route('attendance.show', $attendanceId));

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_attendance_detail_break_time_is_displayed(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-15 09:00:00'));

        /** @var User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $attendanceId = $this->createAttendance(
            userId: $user->id,
            workDate: '2026-05-01',
            clockIn: '09:00:00',
            clockOut: '18:00:00'
        );

        $this->createBreakTime(
            attendanceId: $attendanceId,
            breakStart: '12:00:00',
            breakEnd: '13:00:00'
        );

        $response = $this->get(route('attendance.show', $attendanceId));

        $response->assertStatus(200);
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }

    private function createAttendance(
        int $userId,
        string $workDate,
        string $clockIn,
        string $clockOut
    ): int {
        return DB::table('attendances')->insertGetId([
            'user_id' => $userId,
            'work_date' => $workDate,
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'status' => 'left_work',
            'note' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createBreakTime(
        int $attendanceId,
        string $breakStart,
        string $breakEnd
    ): void {
        DB::table('break_times')->insert([
            'attendance_id' => $attendanceId,
            'break_start' => $breakStart,
            'break_end' => $breakEnd,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}