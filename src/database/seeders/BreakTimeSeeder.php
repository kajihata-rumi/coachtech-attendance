<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Database\Seeder;

class BreakTimeSeeder extends Seeder
{
    public function run()
    {
        $attendances = Attendance::all();

        foreach ($attendances as $attendance) {
            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_start' => '12:00:00',
                'break_end' => '13:00:00',
            ]);
        }
    }
}