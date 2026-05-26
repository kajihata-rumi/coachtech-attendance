<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\AdminStaffController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/admin/login', [LoginController::class, 'showLoginForm']);
Route::post('/admin/login', [LoginController::class, 'login']);

Route::middleware('admin.auth')->group(function () {
    Route::post('/admin/logout', [LoginController::class, 'logout'])
    ->name('admin.logout');
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index'])
    ->name('admin.attendance.list');

    Route::get('/admin/attendance/{attendance}', [AdminAttendanceController::class, 'show'])
    ->name('admin.attendance.show');

    Route::patch('/admin/attendance/{attendance}', [AdminAttendanceController::class, 'update'])
    ->name('admin.attendance.update');

    Route::get('/admin/staff/list', [AdminStaffController::class, 'index'])
    ->name('admin.staff.list');

    Route::get('/admin/attendance/staff/{id}', [AdminStaffController::class, 'attendance'])
    ->name('admin.attendance.staff');

    Route::get('/admin/attendance/staff/{id}/csv', [AdminStaffController::class, 'exportCsv'])
    ->name('admin.attendance.staff.csv');
});

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

    Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [AttendanceController::class, 'approve'])
    ->name('stamp_correction_request.approve');
    });

    Route::patch('/stamp_correction_request/approve/{attendance_correction_request_id}', [AttendanceController::class, 'approveUpdate'])
    ->name('stamp_correction_request.approve.update');
