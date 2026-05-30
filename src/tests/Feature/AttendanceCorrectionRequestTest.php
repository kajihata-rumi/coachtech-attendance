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

    private function createVerifiedUser(): User
    {
        /** @var User $user */
        $user = User::factory()->create([
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
}