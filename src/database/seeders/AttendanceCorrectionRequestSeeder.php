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
        $admin = User::where('role', 'admin')->first();

        $reina = User::where('email', 'reina.n@coachtech.com')->first();
        $taro = User::where('email', 'taro.y@coachtech.com')->first();
        $issei = User::where('email', 'issei.m@coachtech.com')->first();

        $reinaAttendance = Attendance::where('user_id', $reina->id)
            ->where('work_date', '2023-06-01')
            ->first();

        $taroAttendance = Attendance::where('user_id', $taro->id)
            ->where('work_date', '2023-06-02')
            ->first();

        $isseiAttendance = Attendance::where('user_id', $issei->id)
            ->where('work_date', '2023-06-05')
            ->first();

        AttendanceCorrectionRequest::create([
            'attendance_id' => $reinaAttendance->id,
            'user_id' => $reina->id,
            'requested_clock_in' => '09:00:00',
            'requested_clock_out' => '18:00:00',
            'reason' => '遅延のため',
            'status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
        ]);

        AttendanceCorrectionRequest::create([
            'attendance_id' => $taroAttendance->id,
            'user_id' => $taro->id,
            'requested_clock_in' => '09:30:00',
            'requested_clock_out' => '18:30:00',
            'reason' => '電車遅延のため',
            'status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
        ]);

        AttendanceCorrectionRequest::create([
            'attendance_id' => $isseiAttendance->id,
            'user_id' => $issei->id,
            'requested_clock_in' => '09:00:00',
            'requested_clock_out' => '18:00:00',
            'reason' => '打刻修正のため',
            'status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);
    }
}