<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminStaffController extends Controller
{
    public function index()
    {
        $staffs = User::where('role', 'user')
            ->orderBy('id')
            ->get();

        return view('admin.staff.list', compact('staffs'));
    }

    public function attendance($id)
{
    $staff = User::where('role', 'user')->findOrFail($id);

    $currentMonth = request('month')
        ? Carbon::parse(request('month'))
        : Carbon::now();

    $attendances = Attendance::with('breakTimes')
        ->where('user_id', $staff->id)
        ->whereYear('work_date', $currentMonth->year)
        ->whereMonth('work_date', $currentMonth->month)
        ->orderBy('work_date')
        ->get()
        ->keyBy(function ($attendance) {
            return Carbon::parse($attendance->work_date)->format('Y-m-d');
        });

    $dates = [];

    for (
        $date = $currentMonth->copy()->startOfMonth();
        $date->lte($currentMonth->copy()->endOfMonth());
        $date->addDay()
    ) {
        $dates[] = $date->copy();
    }

    return view('admin.attendance.staff', compact('staff', 'currentMonth', 'attendances', 'dates'));
}

public function exportCsv($id)
{
    $staff = User::where('role', 'user')->findOrFail($id);

    $currentMonth = request('month')
        ? Carbon::parse(request('month'))
        : Carbon::now();

    $attendances = Attendance::with('breakTimes')
        ->where('user_id', $staff->id)
        ->whereYear('work_date', $currentMonth->year)
        ->whereMonth('work_date', $currentMonth->month)
        ->orderBy('work_date')
        ->get()
        ->keyBy(function ($attendance) {
            return Carbon::parse($attendance->work_date)->format('Y-m-d');
        });

    $dates = [];

    for (
        $date = $currentMonth->copy()->startOfMonth();
        $date->lte($currentMonth->copy()->endOfMonth());
        $date->addDay()
    ) {
        $dates[] = $date->copy();
    }

    $fileName = 'staff_attendance_' . $staff->id . '_' . $currentMonth->format('Y_m') . '.csv';

    return response()->streamDownload(function () use ($dates, $attendances) {
        $stream = fopen('php://output', 'w');

        echo "\xEF\xBB\xBF";

        fputcsv($stream, ['日付', '出勤', '退勤', '休憩', '合計']);

        foreach ($dates as $date) {
            $dateKey = $date->format('Y-m-d');
            $attendance = $attendances->get($dateKey);
            $week = ['日', '月', '火', '水', '木', '金', '土'][$date->dayOfWeek];

            $breakMinutes = 0;
            $totalMinutes = null;

            if ($attendance) {
                foreach ($attendance->breakTimes as $breakTime) {
                    if ($breakTime->break_start && $breakTime->break_end) {
                        $breakMinutes += Carbon::parse($breakTime->break_start)
                            ->diffInMinutes(Carbon::parse($breakTime->break_end));
                    }
                }

                if ($attendance->clock_in && $attendance->clock_out) {
                    $workMinutes = Carbon::parse($attendance->clock_in)
                        ->diffInMinutes(Carbon::parse($attendance->clock_out));

                    $totalMinutes = $workMinutes - $breakMinutes;
                }
            }

            $breakTimeText = $breakMinutes > 0
                ? floor($breakMinutes / 60) . ':' . str_pad($breakMinutes % 60, 2, '0', STR_PAD_LEFT)
                : '';

            $totalTimeText = $totalMinutes !== null
                ? floor($totalMinutes / 60) . ':' . str_pad($totalMinutes % 60, 2, '0', STR_PAD_LEFT)
                : '';

            fputcsv($stream, [
                $date->format('m/d') . '(' . $week . ')',
                $attendance && $attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : '',
                $attendance && $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '',
                $breakTimeText,
                $totalTimeText,
            ]);
        }

        fclose($stream);
    }, $fileName, [
        'Content-Type' => 'text/csv',
    ]);
}
}
