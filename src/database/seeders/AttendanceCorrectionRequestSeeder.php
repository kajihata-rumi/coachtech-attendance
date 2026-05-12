<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\AttendanceCorrectionRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class AttendanceCorrectionRequestSeeder extends Seeder
{
    public function run()
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $reina = User::where('email', 'reina.n@coachtech.com')->firstOrFail();

        $pendingDates = [
            '2023-06-01',
            '2023-06-02',
            '2023-06-03',
            '2023-06-04',
            '2023-06-05',
            '2023-06-06',
            '2023-06-07',
            '2023-06-08',
            '2023-06-09',
        ];

        foreach ($pendingDates as $date) {
            $attendance = Attendance::firstOrCreate(
                [
                    'user_id' => $reina->id,
                    'work_date' => $date,
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
                'work_date' => '2023-06-10',
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