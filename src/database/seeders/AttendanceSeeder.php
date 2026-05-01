<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        $users = User::where('role', 'user')->get();

        $period = CarbonPeriod::create('2023-06-01', '2023-06-30');

        foreach ($users as $user) {
            foreach ($period as $date) {
                if ($date->isWeekend()) {
                    continue;
                }

                Attendance::create([
                    'user_id' => $user->id,
                    'work_date' => $date->format('Y-m-d'),
                    'clock_in' => '09:00:00',
                    'clock_out' => '18:00:00',
                    'status' => 'completed',
                    'note' => null,
                ]);
            }
        }
    }
}