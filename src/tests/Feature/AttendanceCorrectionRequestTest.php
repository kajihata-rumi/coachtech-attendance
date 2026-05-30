<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AttendanceCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_clock_in_after_clock_out_displays_validation_message(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-15 09:00:00'));

        $user = $this->createVerifiedUser();

        $this->actingAs($user);

        $attendanceId = $this->createAttendance(
            userId: $user->id,
            workDate: '2026-05-01',
            clockIn: '09:00:00',
            clockOut: '18:00:00'
        );

        $response = $this
            ->from(route('attendance.show', $attendanceId))
            ->post(route('attendance.correction.store', $attendanceId), [
                'clock_in' => '19:00',
                'clock_out' => '18:00',
                'breaks' => [
                    [
                        'id' => null,
                        'start' => '12:00',
                        'end' => '13:00',
                    ],
                ],
                'reason' => '出勤時間修正のため',
            ]);

        $response->assertSessionHasErrors([
            'clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_break_start_after_clock_out_displays_validation_message(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-15 09:00:00'));

        $user = $this->createVerifiedUser();

        $this->actingAs($user);

        $attendanceId = $this->createAttendance(
            userId: $user->id,
            workDate: '2026-05-01',
            clockIn: '09:00:00',
            clockOut: '18:00:00'
        );

        $response = $this
            ->from(route('attendance.show', $attendanceId))
            ->post(route('attendance.correction.store', $attendanceId), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    [
                        'id' => null,
                        'start' => '19:00',
                        'end' => '20:00',
                    ],
                ],
                'reason' => '休憩時間修正のため',
            ]);

        $response->assertSessionHasErrors([
            'breaks.0.start' => '休憩時間が不適切な値です',
        ]);
    }

    public function test_break_end_after_clock_out_displays_validation_message(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-15 09:00:00'));

        $user = $this->createVerifiedUser();

        $this->actingAs($user);

        $attendanceId = $this->createAttendance(
            userId: $user->id,
            workDate: '2026-05-01',
            clockIn: '09:00:00',
            clockOut: '18:00:00'
        );

        $response = $this
            ->from(route('attendance.show', $attendanceId))
            ->post(route('attendance.correction.store', $attendanceId), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    [
                        'id' => null,
                        'start' => '12:00',
                        'end' => '19:00',
                    ],
                ],
                'reason' => '休憩終了時間修正のため',
            ]);

        $response->assertSessionHasErrors([
            'breaks.0.end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_reason_required_displays_validation_message(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-15 09:00:00'));

        $user = $this->createVerifiedUser();

        $this->actingAs($user);

        $attendanceId = $this->createAttendance(
            userId: $user->id,
            workDate: '2026-05-01',
            clockIn: '09:00:00',
            clockOut: '18:00:00'
        );

        $response = $this
            ->from(route('attendance.show', $attendanceId))
            ->post(route('attendance.correction.store', $attendanceId), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    [
                        'id' => null,
                        'start' => '12:00',
                        'end' => '13:00',
                    ],
                ],
                'reason' => '',
            ]);

        $response->assertSessionHasErrors([
            'reason' => '備考を記入してください',
        ]);
    }

public function test_attendance_correction_request_is_created(): void
{
    Carbon::setTestNow(Carbon::parse('2026-05-15 09:00:00'));

    $user = $this->createVerifiedUser();

    $this->actingAs($user);

    $attendanceId = $this->createAttendance(
        userId: $user->id,
        workDate: '2026-05-01',
        clockIn: '09:00:00',
        clockOut: '18:00:00'
    );

    $response = $this
        ->from(route('attendance.show', $attendanceId))
        ->post(route('attendance.correction.store', $attendanceId), [
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'breaks' => [
                [
                    'id' => null,
                    'start' => '13:00',
                    'end' => '14:00',
                ],
            ],
            'reason' => '電車遅延のため',
        ]);

    $response->assertRedirect(route('attendance.show', $attendanceId));

    $correctionRequest = DB::table('attendance_correction_requests')
        ->where('attendance_id', $attendanceId)
        ->where('user_id', $user->id)
        ->where('status', 'pending')
        ->first();

    $this->assertNotNull($correctionRequest);
    $this->assertSame('10:00', substr($correctionRequest->requested_clock_in, 0, 5));
    $this->assertSame('19:00', substr($correctionRequest->requested_clock_out, 0, 5));
    $this->assertSame('電車遅延のため', $correctionRequest->reason);
}

public function test_pending_correction_requests_are_displayed_on_request_list(): void
{
    Carbon::setTestNow(Carbon::parse('2026-05-15 09:00:00'));

    $user = $this->createVerifiedUser('山田太郎');
    $otherUser = $this->createVerifiedUser('佐藤花子');

    $this->actingAs($user);

    $attendanceId = $this->createAttendance(
        userId: $user->id,
        workDate: '2026-05-01',
        clockIn: '09:00:00',
        clockOut: '18:00:00'
    );

    $otherAttendanceId = $this->createAttendance(
        userId: $otherUser->id,
        workDate: '2026-05-02',
        clockIn: '09:00:00',
        clockOut: '18:00:00'
    );

    $this->createCorrectionRequest(
        attendanceId: $attendanceId,
        userId: $user->id,
        status: 'pending',
        reason: '電車遅延のため'
    );

    $this->createCorrectionRequest(
        attendanceId: $otherAttendanceId,
        userId: $otherUser->id,
        status: 'pending',
        reason: '私用のため'
    );

    $response = $this->get(route('stamp_correction_request.list', [
        'tab' => 'pending',
    ]));

    $response->assertStatus(200);
    $response->assertSee('承認待ち');
    $response->assertSee('山田太郎');
    $response->assertSee('2026/05/01');
    $response->assertSee('電車遅延のため');

    $response->assertDontSee('佐藤花子');
    $response->assertDontSee('私用のため');
}

public function test_approved_correction_requests_are_displayed_on_request_list(): void
{
    Carbon::setTestNow(Carbon::parse('2026-05-15 09:00:00'));

    $user = $this->createVerifiedUser('山田太郎');
    $otherUser = $this->createVerifiedUser('佐藤花子');

    $this->actingAs($user);

    $attendanceId = $this->createAttendance(
        userId: $user->id,
        workDate: '2026-05-01',
        clockIn: '09:00:00',
        clockOut: '18:00:00'
    );

    $otherAttendanceId = $this->createAttendance(
        userId: $otherUser->id,
        workDate: '2026-05-02',
        clockIn: '09:00:00',
        clockOut: '18:00:00'
    );

    $this->createCorrectionRequest(
        attendanceId: $attendanceId,
        userId: $user->id,
        status: 'approved',
        reason: '電車遅延のため'
    );

    $this->createCorrectionRequest(
        attendanceId: $otherAttendanceId,
        userId: $otherUser->id,
        status: 'approved',
        reason: '私用のため'
    );

    $response = $this->get(route('stamp_correction_request.list', [
        'tab' => 'approved',
    ]));

    $response->assertStatus(200);
    $response->assertSee('承認済み');
    $response->assertSee('山田太郎');
    $response->assertSee('2026/05/01');
    $response->assertSee('電車遅延のため');

    $response->assertDontSee('佐藤花子');
    $response->assertDontSee('私用のため');
}

public function test_correction_request_detail_link_opens_attendance_detail_page(): void
{
    Carbon::setTestNow(Carbon::parse('2026-05-15 09:00:00'));

    $user = $this->createVerifiedUser('山田太郎');

    $this->actingAs($user);

    $attendanceId = $this->createAttendance(
        userId: $user->id,
        workDate: '2026-05-01',
        clockIn: '09:00:00',
        clockOut: '18:00:00'
    );

    $this->createCorrectionRequest(
        attendanceId: $attendanceId,
        userId: $user->id,
        status: 'pending',
        reason: '電車遅延のため'
    );

    $listResponse = $this->get(route('stamp_correction_request.list', [
        'tab' => 'pending',
    ]));

    $listResponse->assertStatus(200);
    $listResponse->assertSee('詳細');
    $listResponse->assertSee(route('attendance.show', [
        'attendance' => $attendanceId,
    ]), false);

    $detailResponse = $this->get(route('attendance.show', [
        'attendance' => $attendanceId,
    ]));

    $detailResponse->assertStatus(200);
    $detailResponse->assertSee('勤怠詳細');
    $detailResponse->assertSee('山田太郎');
}

    private function createVerifiedUser(string $name = '山田太郎'): User
{
    /** @var User $user */
    $user = User::factory()->create([
        'name' => $name,
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
    string $reason
): int {
    return DB::table('attendance_correction_requests')->insertGetId([
        'attendance_id' => $attendanceId,
        'user_id' => $userId,
        'requested_clock_in' => '10:00:00',
        'requested_clock_out' => '19:00:00',
        'reason' => $reason,
        'status' => $status,
        'approved_at' => $status === 'approved' ? now() : null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}
}
