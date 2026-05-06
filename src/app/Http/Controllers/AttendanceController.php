<?php
namespace App\Http\Controllers;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', Auth::id())
            ->where('work_date', $today->toDateString())
            ->first();

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

        $attendance = Attendance::where('user_id', Auth::id())
            ->where('work_date', $today->toDateString())
            ->first();

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
    private function getStatusLabel($status)
    {
        return match ($status) {
            'working' => '出勤中',
            'on_break' => '休憩中',
            'finished' => '退勤済',
            default => '勤務外',
        };
    }
}