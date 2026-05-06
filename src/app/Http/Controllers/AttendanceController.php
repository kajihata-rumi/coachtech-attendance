<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Carbon\CarbonPeriod;

class AttendanceController extends Controller
{
    public function index()
    {
        $attendance = $this->getTodayAttendance();
        $status = $attendance ? $attendance->status : 'before_work';

        return view('attendance.index', [
            'attendance' => $attendance,
            'status' => $status,
            'statusLabel' => $this->getStatusLabel($status),
            'date' => Carbon::now()->isoFormat('YYYY年M月D日(ddd)'),
            'time' => Carbon::now()->format('H:i'),
        ]);
    }

    public function clockIn()
    {
        $today = Carbon::today();

        $attendance = $this->getTodayAttendance();

        if ($attendance) {
            return redirect('/attendance');
        }

        Attendance::create([
            'user_id' => Auth::id(),
            'work_date' => $today->toDateString(),
            'clock_in' => Carbon::now()->format('H:i'),
            'status' => 'working',
        ]);

        return redirect('/attendance');
    }

    public function clockOut()
{
    $attendance = $this->getTodayAttendance();

    if (!$attendance || $attendance->status !== 'working') {
        return redirect('/attendance');
    }

    $activeBreak = BreakTime::where('attendance_id', $attendance->id)
        ->whereNull('break_end')
        ->first();

    if ($activeBreak) {
        return redirect('/attendance');
    }

    $attendance->update([
        'clock_out' => Carbon::now()->format('H:i'),
        'status' => 'completed',
    ]);

    return redirect('/attendance');
}

public function attendanceList()
{
    $targetMonth = request('month')
        ? Carbon::parse(request('month'))
        : Carbon::now();

    $startOfMonth = $targetMonth->copy()->startOfMonth();
    $endOfMonth = $targetMonth->copy()->endOfMonth();

    $attendances = Attendance::with('breakTimes')
        ->where('user_id', Auth::id())
        ->whereBetween('work_date', [
            $startOfMonth->toDateString(),
            $endOfMonth->toDateString(),
        ])
        ->get()
        ->keyBy('work_date');

    $days = CarbonPeriod::create($startOfMonth, $endOfMonth);

    return view('attendance.list', [
        'targetMonth' => $targetMonth,
        'days' => $days,
        'attendances' => $attendances,
    ]);
}
    public function breakStart()
    {
        $attendance = $this->getTodayAttendance();

        if (!$attendance || $attendance->status !== 'working') {
            return redirect('/attendance');
        }

        $activeBreak = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->first();

        if (!$activeBreak) {
            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_start' => Carbon::now()->format('H:i'),
            ]);

            $attendance->update([
                'status' => 'on_break',
            ]);
        }

        return redirect('/attendance');
    }

    public function breakEnd()
    {
        $attendance = $this->getTodayAttendance();

        if (!$attendance || $attendance->status !== 'on_break') {
            return redirect('/attendance');
        }

        $breakTime = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->latest()
            ->first();

        if ($breakTime) {
            $breakTime->update([
                'break_end' => Carbon::now()->format('H:i'),
            ]);

            $attendance->update([
                'status' => 'working',
            ]);
        }

        return redirect('/attendance');
    }

    public function show(Attendance $attendance)
{
    if ($attendance->user_id !== Auth::id()) {
        abort(403);
    }

    $attendance->load('breakTimes');

    return view('attendance.detail', [
        'attendance' => $attendance,
        'user' => Auth::user(),
    ]);
}
    private function getTodayAttendance()
    {
        $today = Carbon::today();

        return Attendance::where('user_id', Auth::id())
            ->where('work_date', $today->toDateString())
            ->first();
    }

    private function getStatusLabel($status)
    {
        return match ($status) {
            'working' => '出勤中',
            'on_break' => '休憩中',
            'completed' => '退勤済',
            default => '勤務外',
        };
    }
}