<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\AttendanceCorrectionRequest;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;

class AttendanceCorrectionRequestSeeder extends Seeder
{
    public function run()
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $reina = User::where('email', 'reina.n@coachtech.com')->firstOrFail();

        $workDates = collect(CarbonPeriod::create(
            now()->startOfMonth(),
            now()->endOfMonth()
        ))
            ->reject(fn ($date) => $date->isWeekend())
            ->take(10)
            ->values();

        $pendingDates = $workDates->take(9);
        $approvedDate = $workDates->last();

        foreach ($pendingDates as $date) {
            $attendance = Attendance::firstOrCreate(
                [
                    'user_id' => $reina->id,
                    'work_date' => $date->format('Y-m-d'),
                ],
                [
                    'clock_in' => '09:00:00',
                    'clock_out' => '18:00:00',
                    'status' => '退勤済',
                    'note' => null,
                ]
            );

            AttendanceCorrectionRequest::create([
                'attendance_id' => $attendance->id,
                'user_id' => $reina->id,
                'requested_clock_in' => '09:00:00',
                'requested_clock_out' => '18:00:00',
                'reason' => '遅延のため',
                'status' => 'pending',
                'approved_by' => null,
                'approved_at' => null,
            ]);
        }

        $approvedAttendance = Attendance::firstOrCreate(
            [
                'user_id' => $reina->id,
                'work_date' => $approvedDate->format('Y-m-d'),
            ],
            [
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
                'status' => '退勤済',
                'note' => null,
            ]
        );

        AttendanceCorrectionRequest::create([
            'attendance_id' => $approvedAttendance->id,
            'user_id' => $reina->id,
            'requested_clock_in' => '09:30:00',
            'requested_clock_out' => '18:30:00',
            'reason' => '打刻修正のため',
            'status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);
    }
}
