<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_selected_attendance_information_is_displayed_on_admin_detail_page(): void
    {
        $admin = $this->createAdminUser();
        $user = $this->createGeneralUser('山田太郎');

        $attendanceId = $this->createAttendance(
            userId: $user->id,
            workDate: '2026-05-29',
            clockIn: '09:00:00',
            clockOut: '18:00:00'
        );

        $this->createBreakTime($attendanceId, '12:00:00', '13:00:00');

        $this->actingAs($admin);

        $response = $this->get(route('admin.attendance.show', $attendanceId));

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('2026年');
        $response->assertSee('5月29日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }

    public function test_error_message_is_displayed_when_clock_in_is_after_clock_out(): void
    {
        $admin = $this->createAdminUser();
        $user = $this->createGeneralUser('山田太郎');

        $attendanceId = $this->createAttendance(
            userId: $user->id,
            workDate: '2026-05-29',
            clockIn: '09:00:00',
            clockOut: '18:00:00'
        );

        $this->actingAs($admin);

        $response = $this->patch(route('admin.attendance.update', $attendanceId), [
            'clock_in' => '19:00',
            'clock_out' => '18:00',
            'breaks' => [
                [
                    'start' => '',
                    'end' => '',
                ],
            ],
            'note' => 'テスト',
        ]);

        $response->assertSessionHasErrors([
            'clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_error_message_is_displayed_when_break_start_is_after_clock_out(): void
    {
        $admin = $this->createAdminUser();
        $user = $this->createGeneralUser('山田太郎');

        $attendanceId = $this->createAttendance(
            userId: $user->id,
            workDate: '2026-05-29',
            clockIn: '09:00:00',
            clockOut: '18:00:00'
        );

        $this->actingAs($admin);

        $response = $this->patch(route('admin.attendance.update', $attendanceId), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                [
                    'start' => '19:00',
                    'end' => '',
                ],
            ],
            'note' => 'テスト',
        ]);

        $response->assertSessionHasErrors([
            'breaks.0.start' => '休憩時間が不適切な値です',
        ]);
    }

    public function test_error_message_is_displayed_when_break_end_is_after_clock_out(): void
    {
        $admin = $this->createAdminUser();
        $user = $this->createGeneralUser('山田太郎');

        $attendanceId = $this->createAttendance(
            userId: $user->id,
            workDate: '2026-05-29',
            clockIn: '09:00:00',
            clockOut: '18:00:00'
        );

        $this->actingAs($admin);

        $response = $this->patch(route('admin.attendance.update', $attendanceId), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                [
                    'start' => '12:00',
                    'end' => '19:00',
                ],
            ],
            'note' => 'テスト',
        ]);

        $response->assertSessionHasErrors([
            'breaks.0.end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_error_message_is_displayed_when_note_is_empty(): void
    {
        $admin = $this->createAdminUser();
        $user = $this->createGeneralUser('山田太郎');

        $attendanceId = $this->createAttendance(
            userId: $user->id,
            workDate: '2026-05-29',
            clockIn: '09:00:00',
            clockOut: '18:00:00'
        );

        $this->actingAs($admin);

        $response = $this->patch(route('admin.attendance.update', $attendanceId), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                [
                    'start' => '12:00',
                    'end' => '13:00',
                ],
            ],
            'note' => '',
        ]);

        $response->assertSessionHasErrors([
            'note' => '備考を記入してください',
        ]);
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
