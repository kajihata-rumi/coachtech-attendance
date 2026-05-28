<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_own_attendance_records_are_displayed_on_attendance_list(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-15 09:00:00'));

        /** @var User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        /** @var User $otherUser */
        $otherUser = User::factory()->create([
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

        $this->createAttendance(
            userId: $user->id,
            workDate: '2026-05-04',
            clockIn: '09:30:00',
            clockOut: '18:30:00'
        );

        $this->createAttendance(
            userId: $otherUser->id,
            workDate: '2026-05-05',
            clockIn: '10:00:00',
            clockOut: '19:00:00'
        );

        $response = $this->get(route('attendance.list'));

        $response->assertStatus(200);
        $response->assertSee('05/01');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('1:00');
        $response->assertSee('8:00');
        $response->assertSee('05/04');
        $response->assertSee('09:30');
        $response->assertSee('18:30');
        $response->assertDontSee('10:00');
        $response->assertDontSee('19:00');
    }

    public function test_current_month_is_displayed_on_attendance_list(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-15 09:00:00'));

        /** @var User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.list'));

        $response->assertStatus(200);
        $response->assertSee('2026/05');
    }

    public function test_previous_month_attendance_information_is_displayed(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-15 09:00:00'));

        /** @var User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $this->createAttendance(
            userId: $user->id,
            workDate: '2026-04-10',
            clockIn: '09:00:00',
            clockOut: '18:00:00'
        );

        $response = $this->get(route('attendance.list', [
            'month' => '2026-04',
        ]));

        $response->assertStatus(200);
        $response->assertSee('2026/04');
        $response->assertSee('04/10');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_next_month_attendance_information_is_displayed(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-15 09:00:00'));

        /** @var User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $this->createAttendance(
            userId: $user->id,
            workDate: '2026-06-10',
            clockIn: '09:00:00',
            clockOut: '18:00:00'
        );

        $response = $this->get(route('attendance.list', [
            'month' => '2026-06',
        ]));

        $response->assertStatus(200);
        $response->assertSee('2026/06');
        $response->assertSee('06/10');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_attendance_detail_page_is_displayed_from_attendance_list(): void
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

        $listResponse = $this->get(route('attendance.list'));

        $listResponse->assertStatus(200);
        $listResponse->assertSee('詳細');

        $detailResponse = $this->get(route('attendance.show', $attendanceId));

        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('勤怠詳細');
        $detailResponse->assertSee('09:00');
        $detailResponse->assertSee('18:00');
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