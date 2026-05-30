<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_all_users_attendance_information_is_displayed_for_selected_date(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-15 09:00:00'));

        $admin = $this->createAdminUser();

        $userA = $this->createGeneralUser('山田太郎');
        $userB = $this->createGeneralUser('西玲奈');

        $this->actingAs($admin);

        $attendanceAId = $this->createAttendance(
            userId: $userA->id,
            workDate: '2026-05-15',
            clockIn: '09:00:00',
            clockOut: '18:00:00'
        );

        $attendanceBId = $this->createAttendance(
            userId: $userB->id,
            workDate: '2026-05-15',
            clockIn: '10:00:00',
            clockOut: '19:00:00'
        );

        $this->createBreakTime($attendanceAId, '12:00:00', '13:00:00');
        $this->createBreakTime($attendanceBId, '14:00:00', '15:00:00');

        $response = $this->get(route('admin.attendance.list', [
            'date' => '2026-05-15',
        ]));

        $response->assertStatus(200);

        $response->assertSee('山田太郎');
        $response->assertSee('西玲奈');

        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('10:00');
        $response->assertSee('19:00');

        $response->assertSee('1:00');
        $response->assertSee('8:00');
    }

    public function test_current_date_is_displayed_on_admin_attendance_list(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-15 09:00:00'));

        $admin = $this->createAdminUser();

        $this->actingAs($admin);

        $response = $this->get(route('admin.attendance.list'));

        $response->assertStatus(200);
        $response->assertSee('2026年5月15日');
        $response->assertSee('2026/05/15');
    }

    public function test_previous_day_attendance_information_is_displayed(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-15 09:00:00'));

        $admin = $this->createAdminUser();
        $user = $this->createGeneralUser('山田太郎');

        $this->actingAs($admin);

        $previousAttendanceId = $this->createAttendance(
            userId: $user->id,
            workDate: '2026-05-14',
            clockIn: '09:00:00',
            clockOut: '18:00:00'
        );

        $this->createAttendance(
            userId: $user->id,
            workDate: '2026-05-15',
            clockIn: '10:00:00',
            clockOut: '19:00:00'
        );

        $this->createBreakTime($previousAttendanceId, '12:00:00', '13:00:00');

        $response = $this->get(route('admin.attendance.list', [
            'date' => '2026-05-14',
        ]));

        $response->assertStatus(200);
        $response->assertSee('2026年5月14日');
        $response->assertSee('2026/05/14');
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertDontSee('10:00');
        $response->assertDontSee('19:00');
    }

    public function test_next_day_attendance_information_is_displayed(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-15 09:00:00'));

        $admin = $this->createAdminUser();
        $user = $this->createGeneralUser('山田太郎');

        $this->actingAs($admin);

        $this->createAttendance(
            userId: $user->id,
            workDate: '2026-05-15',
            clockIn: '09:00:00',
            clockOut: '18:00:00'
        );

        $nextAttendanceId = $this->createAttendance(
            userId: $user->id,
            workDate: '2026-05-16',
            clockIn: '10:00:00',
            clockOut: '19:00:00'
        );

        $this->createBreakTime($nextAttendanceId, '14:00:00', '15:00:00');

        $response = $this->get(route('admin.attendance.list', [
            'date' => '2026-05-16',
        ]));

        $response->assertStatus(200);
        $response->assertSee('2026年5月16日');
        $response->assertSee('2026/05/16');
        $response->assertSee('10:00');
        $response->assertSee('19:00');

        $response->assertDontSee('09:00');
        $response->assertDontSee('18:00');
    }

    private function createAdminUser(): User
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        return $admin;
    }

    private function createGeneralUser(string $name): User
    {
        /** @var User $user */
        $user = User::factory()->create([
            'name' => $name,
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        return $user;
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