<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/admin/login', [LoginController::class, 'showLoginForm']);
Route::post('/admin/login', [LoginController::class, 'login']);
Route::post('/admin/logout', [LoginController::class, 'logout'])
    ->name('admin.logout');

Route::get('/home', function () {
    return redirect('/attendance');
})->middleware('auth');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])
    ->name('attendance.index');

    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])
        ->name('attendance.clock_in');

    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])
    ->name('attendance.clock_out');

    Route::post('/attendance/break-start', [AttendanceController::class, 'breakStart'])
    ->name('attendance.break_start');

    Route::post('/attendance/break-end', [AttendanceController::class, 'breakEnd'])
    ->name('attendance.break_end');

    Route::get('/attendance/list', [AttendanceController::class, 'attendanceList'])
    ->name('attendance.list');

    Route::get('/attendance/detail/{attendance}', [AttendanceController::class, 'show'])
    ->name('attendance.show');

    Route::post('/attendance/detail/{attendance}', [AttendanceController::class, 'storeCorrection'])
    ->name('attendance.correction.store');

    Route::get('/stamp_correction_request/list', [AttendanceController::class, 'correctionRequestList'])
    ->name('stamp_correction_request.list');
});

    Route::get('/admin/attendance/list', function () {
        return view('tmp-page', ['title' => '管理者：勤怠一覧画面']);
    });

    Route::get('/admin/staff/list', function () {
        return view('tmp-page', ['title' => '管理者：スタッフ一覧画面']);
    });

    Route::get('/admin/stamp_correction_request/list', function () {
        return view('tmp-page', ['title' => '管理者：申請一覧画面']);
    });

