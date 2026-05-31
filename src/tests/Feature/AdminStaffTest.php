<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminStaffTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_general_users_name_and_email_are_displayed_on_admin_staff_list(): void
    {
        $admin = $this->createAdminUser('管理者', 'admin@example.com');

        $userA = $this->createGeneralUser('山田太郎', 'taro@example.com');
        $userB = $this->createGeneralUser('西玲奈', 'reina@example.com');

        $otherAdmin = $this->createAdminUser('別管理者', 'other-admin@example.com');

        $this->actingAs($admin);

        $response = $this->get(route('admin.staff.list'));

        $response->assertStatus(200);

        $response->assertSee($userA->name);
        $response->assertSee($userA->email);
        $response->assertSee($userB->name);
        $response->assertSee($userB->email);

        $response->assertDontSee($otherAdmin->name);
        $response->assertDontSee($otherAdmin->email);
    }

    public function test_selected_user_attendance_information_is_displayed(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-15 09:00:00'));

        $admin = $this->createAdminUser();
        $user = $this->createGeneralUser('山田太郎', 'taro@example.com');
        $otherUser = $this->createGeneralUser('西玲奈', 'reina@example.com');

        $attendanceId = $this->createAttendance(
            userId: $user->id,
            workDate: '2026-05-15',
            clockIn: '09:00:00',
            clockOut: '18:00:00'
        );

        $this->createBreakTime($attendanceId, '12:00:00', '13:00:00');

        $this->createAttendance(
            userId: $otherUser->id,
            workDate: '2026-05-15',
            clockIn: '10:00:00',
            clockOut: '19:00:00'
        );

        $this->actingAs($admin);

        $response = $this->get(route('admin.attendance.staff', [
            'id' => $user->id,
            'month' => '2026-05',
        ]));

        $response->assertStatus(200);

        $response->assertSee('山田太郎さんの勤怠');
        $response->assertSee('2026/05');
        $response->assertSee('05/15');

        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('1:00');
        $response->assertSee('8:00');

        $response->assertDontSee('10:00');
        $response->assertDontSee('19:00');
    }

    public function test_previous_month_attendance_information_is_displayed(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-15 09:00:00'));

        $admin = $this->createAdminUser();
        $user = $this->createGeneralUser('山田太郎', 'taro@example.com');

        $previousAttendanceId = $this->createAttendance(
            userId: $user->id,
            workDate: '2026-04-15',
            clockIn: '08:00:00',
            clockOut: '17:00:00'
        );

        $this->createBreakTime($previousAttendanceId, '12:00:00', '13:00:00');

        $this->createAttendance(
            userId: $user->id,
            workDate: '2026-05-15',
            clockIn: '09:00:00',
            clockOut: '18:00:00'
        );

        $this->actingAs($admin);

        $response = $this->get(route('admin.attendance.staff', [
            'id' => $user->id,
            'month' => '2026-04',
        ]));

        $response->assertStatus(200);

        $response->assertSee('2026/04');
        $response->assertSee('04/15');
        $response->assertSee('08:00');
        $response->assertSee('17:00');

        $response->assertDontSee('09:00');
        $response->assertDontSee('18:00');
    }

    public function test_next_month_attendance_information_is_displayed(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-15 09:00:00'));

        $admin = $this->createAdminUser();
        $user = $this->createGeneralUser('山田太郎', 'taro@example.com');

        $this->createAttendance(
            userId: $user->id,
            workDate: '2026-05-15',
            clockIn: '09:00:00',
            clockOut: '18:00:00'
        );

        $nextAttendanceId = $this->createAttendance(
            userId: $user->id,
            workDate: '2026-06-15',
            clockIn: '10:00:00',
            clockOut: '19:00:00'
        );

        $this->createBreakTime($nextAttendanceId, '14:00:00', '15:00:00');

        $this->actingAs($admin);

        $response = $this->get(route('admin.attendance.staff', [
            'id' => $user->id,
            'month' => '2026-06',
        ]));

        $response->assertStatus(200);

        $response->assertSee('2026/06');
        $response->assertSee('06/15');
        $response->assertSee('10:00');
        $response->assertSee('19:00');

        $response->assertDontSee('09:00');
        $response->assertDontSee('18:00');
    }

    public function test_attendance_detail_page_can_be_accessed_from_staff_attendance_list(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-15 09:00:00'));

        $admin = $this->createAdminUser();
        $user = $this->createGeneralUser('山田太郎', 'taro@example.com');

        $attendanceId = $this->createAttendance(
            userId: $user->id,
            workDate: '2026-05-15',
            clockIn: '09:00:00',
            clockOut: '18:00:00'
        );

        $this->actingAs($admin);

        $response = $this->get(route('admin.attendance.staff', [
            'id' => $user->id,
            'month' => '2026-05',
        ]));

        $response->assertStatus(200);
        $response->assertSee('/admin/attendance/' . $attendanceId, false);

        $detailResponse = $this->get(route('admin.attendance.show', $attendanceId));

        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('山田太郎');
        $detailResponse->assertSee('2026年');
        $detailResponse->assertSee('5月15日');
        $detailResponse->assertSee('09:00');
        $detailResponse->assertSee('18:00');
    }

    private function createAdminUser(
        string $name = '管理者',
        string $email = 'admin@example.com'
    ): User {
        /** @var User $admin */
        $admin = User::factory()->create([
            'name' => $name,
            'email' => $email,
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        return $admin;
    }

    private function createGeneralUser(
        string $name,
        string $email
    ): User {
        /** @var User $user */
        $user = User::factory()->create([
            'name' => $name,
            'email' => $email,
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
