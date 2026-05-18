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

}
