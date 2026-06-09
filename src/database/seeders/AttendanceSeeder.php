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

        $today = now()->startOfDay();

        $period = CarbonPeriod::create(
            $today->copy()->startOfMonth(),
            $today
        );

        foreach ($users as $user) {
            foreach ($period as $date) {
                if ($date->isWeekend()) {
                    continue;
                }

                $isToday = $date->isSameDay($today);

                Attendance::create([
                    'user_id' => $user->id,
                    'work_date' => $date->format('Y-m-d'),
                    'clock_in' => '09:00:00',
                    'clock_out' => $isToday ? null : '18:00:00',
                    'status' => $isToday ? 'working' : 'completed',
                    'note' => null,
                ]);
            }
        }
    }
}
