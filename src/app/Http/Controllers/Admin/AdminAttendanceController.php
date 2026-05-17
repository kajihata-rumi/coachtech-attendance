<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminAttendanceUpdateRequest;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;


class AdminAttendanceController extends Controller
{
    public function index()
    {
        $targetDate = request('date')
            ? Carbon::parse(request('date'))
            : Carbon::today();

        $users = User::where('role', 'user')
            ->with([
                'attendances' => function ($query) use ($targetDate) {
                    $query->where('work_date', $targetDate->toDateString())
                        ->with('breakTimes');
                },
            ])
            ->get();

        $attendanceRows = $users->map(function ($user) {
            $attendance = $user->attendances->first();

            if (!$attendance) {
                return [
                    'user' => $user,
                    'attendance' => null,
                    'clock_in' => '',
                    'clock_out' => '',
                    'break_time' => '',
                    'total_time' => '',
                ];
            }

            $breakMinutes = $this->calculateBreakMinutes($attendance);
            $totalMinutes = $this->calculateTotalMinutes($attendance, $breakMinutes);

            return [
                'user' => $user,
                'attendance' => $attendance,
                'clock_in' => $this->formatTime($attendance->clock_in),
                'clock_out' => $this->formatTime($attendance->clock_out),
                'break_time' => $this->formatMinutes($breakMinutes),
                'total_time' => $this->formatMinutes($totalMinutes),
            ];
        });

        return view('admin.attendance.list', [
            'targetDate' => $targetDate,
            'previousDate' => $targetDate->copy()->subDay(),
            'nextDate' => $targetDate->copy()->addDay(),
            'attendanceRows' => $attendanceRows,
        ]);
    }


    public function show(Attendance $attendance)
{
    $attendance->load(['user', 'breakTimes']);

    return view('admin.attendance.detail', [
        'attendance' => $attendance,
        'user' => $attendance->user,
    ]);
}
    private function calculateBreakMinutes($attendance)
    {
        return $attendance->breakTimes->sum(function ($breakTime) {
            if (!$breakTime->break_start || !$breakTime->break_end) {
                return 0;
            }

            return Carbon::parse($breakTime->break_start)
                ->diffInMinutes(Carbon::parse($breakTime->break_end));
        });
    }

public function update(AdminAttendanceUpdateRequest $request, Attendance $attendance)
{
    $attendance->update([
        'clock_in' => $request->input('clock_in'),
        'clock_out' => $request->input('clock_out'),
        'note' => $request->input('note'),
    ]);

    $breakInputs = $request->input('breaks', []);
    $breakTimes = $attendance->breakTimes()->orderBy('id')->get();

    foreach ([0, 1] as $index) {
        $breakInput = $breakInputs[$index] ?? [];

        $start = $breakInput['start'] ?? null;
        $end = $breakInput['end'] ?? null;

        $breakTime = $breakTimes->get($index);

        if ($start || $end) {
            if ($breakTime) {
                $breakTime->update([
                    'break_start' => $start,
                    'break_end' => $end,
                ]);
            } else {
                $attendance->breakTimes()->create([
                    'break_start' => $start,
                    'break_end' => $end,
                ]);
            }
        } elseif ($breakTime) {
            $breakTime->delete();
        }
    }

    return redirect()->route('admin.attendance.show', $attendance);
}
    private function calculateTotalMinutes($attendance, $breakMinutes)
    {
        if (!$attendance->clock_in || !$attendance->clock_out) {
            return 0;
        }

        $workMinutes = Carbon::parse($attendance->clock_in)
            ->diffInMinutes(Carbon::parse($attendance->clock_out));

        return max($workMinutes - $breakMinutes, 0);
    }

    private function formatTime($time)
    {
        return $time ? Carbon::parse($time)->format('H:i') : '';
    }

    private function formatMinutes($minutes)
    {
        if ($minutes <= 0) {
            return '';
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        return $hours . ':' . str_pad($remainingMinutes, 2, '0', STR_PAD_LEFT);
    }
}