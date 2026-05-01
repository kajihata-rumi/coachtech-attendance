<?php

namespace Database\Seeders;

use App\Models\AttendanceCorrectionRequest;
use App\Models\BreakCorrectionRequest;
use App\Models\BreakTime;
use Illuminate\Database\Seeder;

class BreakCorrectionRequestSeeder extends Seeder
{
    public function run()
    {
        $correctionRequests = AttendanceCorrectionRequest::all();

        foreach ($correctionRequests as $correctionRequest) {
            $breakTime = BreakTime::where('attendance_id', $correctionRequest->attendance_id)->first();

            BreakCorrectionRequest::create([
                'attendance_correction_request_id' => $correctionRequest->id,
                'break_time_id' => $breakTime ? $breakTime->id : null,
                'requested_break_start' => '12:00:00',
                'requested_break_end' => '13:00:00',
            ]);
        }
    }
}