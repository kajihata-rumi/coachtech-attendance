<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_pending_correction_requests_are_displayed_on_admin_request_list(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-15 09:00:00'));

        $admin = $this->createAdminUser();

        $userA = $this->createGeneralUser('山田 太郎', 'taro@example.com');
        $userB = $this->createGeneralUser('西 玲奈', 'reina@example.com');

        $attendanceAId = $this->createAttendance(
            userId: $userA->id,
            workDate: '2026-05-01',
            clockIn: '09:00:00',
            clockOut: '18:00:00'
        );

        $attendanceBId = $this->createAttendance(
            userId: $userB->id,
            workDate: '2026-05-02',
            clockIn: '09:00:00',
            clockOut: '18:00:00'
        );

        $approvedAttendanceId = $this->createAttendance(
            userId: $userA->id,
            workDate: '2026-05-03',
            clockIn: '09:00:00',
            clockOut: '18:00:00'
        );

        $this->createCorrectionRequest(
            attendanceId: $attendanceAId,
            userId: $userA->id,
            status: 'pending',
            reason: '電車遅延のため'
        );

        $this->createCorrectionRequest(
            attendanceId: $attendanceBId,
            userId: $userB->id,
            status: 'pending',
            reason: '私用のため'
        );

        $this->createCorrectionRequest(
            attendanceId: $approvedAttendanceId,
            userId: $userA->id,
            status: 'approved',
            reason: '承認済みの理由'
        );

        $this->actingAs($admin);

        $response = $this->get(route('stamp_correction_request.list', [
            'tab' => 'pending',
        ]));

        $response->assertStatus(200);

        $response->assertSee('承認待ち');
        $response->assertSee('山田 太郎');
        $response->assertSee('西 玲奈');
        $response->assertSee('2026/05/01');
        $response->assertSee('2026/05/02');
        $response->assertSee('電車遅延のため');
        $response->assertSee('私用のため');

        $response->assertDontSee('承認済みの理由');
    }

    public function test_approved_correction_requests_are_displayed_on_admin_request_list(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-15 09:00:00'));

        $admin = $this->createAdminUser();

        $userA = $this->createGeneralUser('山田 太郎', 'taro@example.com');
        $userB = $this->createGeneralUser('西 玲奈', 'reina@example.com');

        $attendanceAId = $this->createAttendance(
            userId: $userA->id,
            workDate: '2026-05-01',
            clockIn: '09:00:00',
            clockOut: '18:00:00'
        );

        $attendanceBId = $this->createAttendance(
            userId: $userB->id,
            workDate: '2026-05-02',
            clockIn: '09:00:00',
            clockOut: '18:00:00'
        );

        $pendingAttendanceId = $this->createAttendance(
            userId: $userA->id,
            workDate: '2026-05-03',
            clockIn: '09:00:00',
            clockOut: '18:00:00'
        );

        $this->createCorrectionRequest(
            attendanceId: $attendanceAId,
            userId: $userA->id,
            status: 'approved',
            reason: '電車遅延のため'
        );

        $this->createCorrectionRequest(
            attendanceId: $attendanceBId,
            userId: $userB->id,
            status: 'approved',
            reason: '私用のため'
        );

        $this->createCorrectionRequest(
            attendanceId: $pendingAttendanceId,
            userId: $userA->id,
            status: 'pending',
            reason: '承認待ちの理由'
        );

        $this->actingAs($admin);

        $response = $this->get(route('stamp_correction_request.list', [
            'tab' => 'approved',
        ]));

        $response->assertStatus(200);

        $response->assertSee('承認済み');
        $response->assertSee('山田 太郎');
        $response->assertSee('西 玲奈');
        $response->assertSee('2026/05/01');
        $response->assertSee('2026/05/02');
        $response->assertSee('電車遅延のため');
        $response->assertSee('私用のため');

        $response->assertDontSee('承認待ちの理由');
    }

    public function test_correction_request_detail_is_displayed_for_admin(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-15 09:00:00'));

        $admin = $this->createAdminUser();

        $user = $this->createGeneralUser('山田 太郎', 'taro@example.com');

        $attendanceId = $this->createAttendance(
            userId: $user->id,
            workDate: '2026-05-01',
            clockIn: '09:00:00',
            clockOut: '18:00:00'
        );

        $correctionRequestId = $this->createCorrectionRequest(
            attendanceId: $attendanceId,
            userId: $user->id,
            status: 'pending',
            reason: '電車遅延のため',
            requestedClockIn: '10:00:00',
            requestedClockOut: '19:00:00'
        );

        $this->actingAs($admin);

        $response = $this->get(route('stamp_correction_request.approve', $correctionRequestId));

        $response->assertStatus(200);

        $response->assertSee('勤怠詳細');
        $response->assertSee('山田 太郎');
        $response->assertSee('2026年');
        $response->assertSee('5月1日');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
        $response->assertSee('電車遅延のため');
    }

    public function test_admin_can_approve_correction_request(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-15 09:00:00'));

        $admin = $this->createAdminUser();

        $user = $this->createGeneralUser('山田 太郎', 'taro@example.com');

        $attendanceId = $this->createAttendance(
            userId: $user->id,
            workDate: '2026-05-01',
            clockIn: '09:00:00',
            clockOut: '18:00:00'
        );

        $correctionRequestId = $this->createCorrectionRequest(
            attendanceId: $attendanceId,
            userId: $user->id,
            status: 'pending',
            reason: '電車遅延のため',
            requestedClockIn: '10:00:00',
            requestedClockOut: '19:00:00'
        );

        $this->actingAs($admin);

        $response = $this->patch(route('stamp_correction_request.approve.update', $correctionRequestId));

        $response->assertRedirect(route('stamp_correction_request.list', [
            'tab' => 'approved',
        ]));

        $this->assertDatabaseHas('attendances', [
            'id' => $attendanceId,
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
            'note' => '電車遅延のため',
        ]);

        $this->assertDatabaseHas('attendance_correction_requests', [
            'id' => $correctionRequestId,
            'status' => 'approved',
        ]);

        $correctionRequest = DB::table('attendance_correction_requests')
            ->where('id', $correctionRequestId)
            ->first();

        $this->assertNotNull($correctionRequest->approved_at);
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

    private function createCorrectionRequest(
        int $attendanceId,
        int $userId,
        string $status,
        string $reason,
        string $requestedClockIn = '10:00:00',
        string $requestedClockOut = '19:00:00'
    ): int {
        return DB::table('attendance_correction_requests')->insertGetId([
            'attendance_id' => $attendanceId,
            'user_id' => $userId,
            'requested_clock_in' => $requestedClockIn,
            'requested_clock_out' => $requestedClockOut,
            'reason' => $reason,
            'status' => $status,
            'approved_at' => $status === 'approved' ? now() : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
