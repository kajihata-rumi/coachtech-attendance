<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/home', function () {
    return redirect('/attendance');
})->middleware('auth');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', function () {
        return view('tmp-page', ['title' => '勤怠登録画面']);
    });

    Route::get('/attendance/list', function () {
        return view('tmp-page', ['title' => '勤怠一覧画面']);
    });

    Route::get('/stamp_correction_request/list', function () {
        return view('tmp-page', ['title' => '申請一覧画面']);
    });
});

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/attendance/list', function () {
        return view('tmp-page', ['title' => '管理者：勤怠一覧画面']);
    });

    Route::get('/admin/staff/list', function () {
        return view('tmp-page', ['title' => '管理者：スタッフ一覧画面']);
    });

    Route::get('/admin/stamp_correction_request/list', function () {
        return view('tmp-page', ['title' => '管理者：申請一覧画面']);
    });
});