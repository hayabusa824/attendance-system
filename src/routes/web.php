<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\AttendanceListController;
use App\Http\Controllers\AttendanceDetailController;
use App\Http\Controllers\AdminAttendanceDetailController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StaffAttendanceController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\AdminRequestController;
use App\Http\Controllers\ApprovalController;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;




/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/register', [RegisterController::class, 'index'])->name('register');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/attendance', [LoginController::class, 'store']);

Route::get('/admin/login', [AdminLoginController::class, 'index'])->name('admin.login');
Route::post('/admin/attendance/list', [AdminLoginController::class, 'store']);



Route::middleware(['auth:web','verified.custom'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/attendance', [AttendanceController::class, 'index']);
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn']);
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut']);
    Route::post('/attendance/break-in', [AttendanceController::class, 'breakIn']);
    Route::post('/attendance/break-out', [AttendanceController::class, 'breakOut']);

    Route::get('/attendance/list', [AttendanceListController::class, 'index']);

    Route::get('/attendance/{id}', [AttendanceDetailController::class, 'show'])->name('attendance.detail');
    Route::post('/attendance/correction', [AttendanceDetailController::class, 'store'])->name('correction.store');
    Route::get('/attendance/request/{request_id}', [AttendanceDetailController::class, 'showFromRequest'])->name('attendance.detail.fromRequest');

});


Route::prefix('admin')->middleware(['auth:admin'])->group(function () {
    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');

    Route::get('/attendance/list', [AdminAttendanceController::class, 'index']);

    Route::get('/attendance/{id}', [AdminAttendanceDetailController::class, 'show'])->name('admin.attendance.detail');

    Route::get('/staff/list', [StaffController::class, 'index'])->name('admin.staff.list');

    Route::get('attendance/staff/{id}',[StaffAttendanceController::class, 'show'])->name('admin.attendance.staff');
    Route::get('/admin/attendance/csv', [StaffAttendanceController::class, 'exportCsv'])
    ->name('admin.attendance.csv');

    Route::get('/stamp_correction_request/approve/{attendance_correct_request}', [ApprovalController::class, 'show'])
    ->name('stamp_correction_request.approve');
    Route::post('/stamp_correction_request/approve/{id}', [ApprovalController::class, 'approve'])
    ->name('stamp_correction_request.approve.action');

    Route::post('/admin/attendance/update/{id}', [AdminAttendanceDetailController::class, 'update'])
    ->name('admin.attendance.update');
});

Route::get('/stamp_correction_request/list', [RequestController::class, 'index'])
    ->middleware('request.auth');



Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect('/attendance');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('message', '認証リンクを再送信しました');
})->middleware(['auth', 'throttle:6,1'])->name('verification.resend');