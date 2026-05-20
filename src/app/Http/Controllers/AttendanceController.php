<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceCorrectionStoreRequest;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\AttendanceCorrectionRequest;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

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

    $pendingCorrectionRequest = AttendanceCorrectionRequest::with('breakCorrectionRequests')
        ->where('attendance_id', $attendance->id)
        ->where('user_id', Auth::id())
        ->where('status', 'pending')
        ->latest()
        ->first();

    return view('attendance.detail', [
        'attendance' => $attendance,
        'user' => Auth::user(),
        'pendingCorrectionRequest' => $pendingCorrectionRequest,
    ]);
}
public function storeCorrection(AttendanceCorrectionStoreRequest $request, Attendance $attendance)
{
    if ($attendance->user_id !== Auth::id()) {
        abort(403);
    }

    $validated = $request->validated();

    DB::transaction(function () use ($validated, $attendance) {
        $correctionRequest = AttendanceCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => Auth::id(),
            'requested_clock_in' => $validated['clock_in'],
            'requested_clock_out' => $validated['clock_out'],
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        foreach ($validated['breaks'] ?? [] as $break) {
            $start = $break['start'] ?? null;
            $end = $break['end'] ?? null;

            if (empty($start) && empty($end)) {
                continue;
            }

            $correctionRequest->breakCorrectionRequests()->create([
                'break_time_id' => !empty($break['id']) ? $break['id'] : null,
                'requested_break_start' => $start,
                'requested_break_end' => $end,
            ]);
        }
    });

    return redirect()->route('attendance.show', $attendance);
}
public function correctionRequestList()
{
    $user = auth()->user();
    $isAdmin = $user->role === 'admin';

    $pendingRequestsQuery = DB::table('attendance_correction_requests')
        ->join('attendances', 'attendance_correction_requests.attendance_id', '=', 'attendances.id')
        ->join('users', 'attendance_correction_requests.user_id', '=', 'users.id')
        ->where('attendance_correction_requests.status', 'pending')
        ->select(
            'attendance_correction_requests.*',
            'attendances.work_date as attendance_date',
            'users.name as user_name'
        );

    if (! $isAdmin) {
        $pendingRequestsQuery->where('attendance_correction_requests.user_id', $user->id);
    }

    $pendingRequests = $pendingRequestsQuery
        ->orderBy('attendance_correction_requests.created_at', 'desc')
        ->get();

    $approvedRequestsQuery = DB::table('attendance_correction_requests')
        ->join('attendances', 'attendance_correction_requests.attendance_id', '=', 'attendances.id')
        ->join('users', 'attendance_correction_requests.user_id', '=', 'users.id')
        ->where('attendance_correction_requests.status', 'approved')
        ->select(
            'attendance_correction_requests.*',
            'attendances.work_date as attendance_date',
            'users.name as user_name'
        );

    if (! $isAdmin) {
        $approvedRequestsQuery->where('attendance_correction_requests.user_id', $user->id);
    }

    $approvedRequests = $approvedRequestsQuery
        ->orderBy('attendance_correction_requests.created_at', 'desc')
        ->get();

    return view('stamp_correction_request.list', compact(
        'pendingRequests',
        'approvedRequests',
        'isAdmin'
    ));
}

public function approve($attendance_correct_request_id)
{
    $correctionRequest = DB::table('attendance_correction_requests')
        ->join('attendances', 'attendance_correction_requests.attendance_id', '=', 'attendances.id')
        ->join('users', 'attendance_correction_requests.user_id', '=', 'users.id')
        ->where('attendance_correction_requests.id', $attendance_correct_request_id)
        ->select(
            'attendance_correction_requests.*',
            'attendances.work_date as attendance_date',
            'users.name as user_name'
        )
        ->first();

    abort_if(!$correctionRequest, 404);

    return view('stamp_correction_request.approve', compact('correctionRequest'));
}

public function approveUpdate($attendance_correct_request_id)
{
    DB::transaction(function () use ($attendance_correct_request_id) {
        $correctionRequest = DB::table('attendance_correction_requests')
            ->where('id', $attendance_correct_request_id)
            ->first();

        abort_if(!$correctionRequest, 404);

        if ($correctionRequest->status === 'approved') {
            return;
        }

        DB::table('attendances')
            ->where('id', $correctionRequest->attendance_id)
            ->update([
                'clock_in' => $correctionRequest->requested_clock_in,
                'clock_out' => $correctionRequest->requested_clock_out,
                'note' => $correctionRequest->reason,
                'updated_at' => now(),
            ]);

        $breakCorrectionRequests = DB::table('break_correction_requests')
            ->where('attendance_correction_request_id', $correctionRequest->id)
            ->get();

        foreach ($breakCorrectionRequests as $breakCorrectionRequest) {
            if ($breakCorrectionRequest->break_time_id) {
                DB::table('break_times')
                    ->where('id', $breakCorrectionRequest->break_time_id)
                    ->update([
                        'break_start' => $breakCorrectionRequest->requested_break_start,
                        'break_end' => $breakCorrectionRequest->requested_break_end,
                        'updated_at' => now(),
                    ]);

                continue;
            }

            if (
                $breakCorrectionRequest->requested_break_start ||
                $breakCorrectionRequest->requested_break_end
            ) {
                DB::table('break_times')
                    ->insert([
                        'attendance_id' => $correctionRequest->attendance_id,
                        'break_start' => $breakCorrectionRequest->requested_break_start,
                        'break_end' => $breakCorrectionRequest->requested_break_end,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
            }
        }

        DB::table('attendance_correction_requests')
            ->where('id', $attendance_correct_request_id)
            ->update([
                'status' => 'approved',
                'approved_at' => now(),
                'updated_at' => now(),
            ]);
    });

    return redirect()
        ->route('stamp_correction_request.list', ['tab' => 'approved']);
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