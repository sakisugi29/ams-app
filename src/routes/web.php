<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\Auth\AdminLoginController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\StaffAttendanceController;
use App\Http\Controllers\AttendanceReportController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/register', function () {
        return view('auth.register');
    })->name('register');

    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');
});

Route::post('/logout', function () {
    Auth::logout();
    return redirect('/login');
})->name('logout');

Route::middleware('auth')->group(function () {

    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (\Illuminate\Foundation\Auth\EmailVerificationRequest $request) {
        $request->fulfill();

        if ($request->user()->isAdmin()) {
            return redirect()->route('admin.attendance.list');
        }

        return redirect()->route('attendance.index');
    })->middleware('signed')->name('verification.verify');

    Route::post('/email/verification-notification', function (\Illuminate\Http\Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('status', 'verification-link-sent');
    })->middleware('throttle:6,1')->name('verification.send');

    Route::middleware('verified')->group(function () {
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clockIn');
        Route::post('/attendance/start-break', [AttendanceController::class, 'startBreak'])->name('attendance.startBreak');
        Route::post('/attendance/end-break', [AttendanceController::class, 'endBreak'])->name('attendance.endBreak');
        Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clockOut');
        Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
        Route::get('/attendance/detail/{id}', [AttendanceController::class, 'show'])->name('attendance.show');
        Route::put('/attendance/detail/{id}', [AttendanceController::class, 'update'])->name('attendance.update');
        Route::get('/attendance/detail/{id}/edit', [AttendanceController::class, 'edit'])->name('attendance.edit');
        Route::get('/attendance/report',[AttendanceReportController::class,'index'])->name('attendance.report');
    });
});
//*申請一覧画面(一般・管理者共通)*//
        Route::get('/stamp_correction_request/list', [ApplicationController::class, 'index'])->name('application.index')
            ->middleware(['verified','auth']);

//*管理者*//
        Route::get('/admin/login', [AdminLoginController::class, 'create'])->name('admin.login');
        Route::post('/admin/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('guest');

        Route::post('/admin/logout', [AdminLoginController::class, 'destroy'])->name('admin.logout');

    Route::middleware(['auth', 'verified', 'admin'])->group(function () {
        Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance-list');
        Route::get('/admin/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('admin.attendance-show');
        Route::put('/admin/attendance/{id}', [AdminAttendanceController::class, 'update'])->name('admin.attendance-update');
        Route::get('/admin/staff/list', [StaffAttendanceController::class, 'index'])->name('admin.staff-list');
        Route::get('/admin/attendance/staff/{id}', [StaffAttendanceController::class, 'list'])->name('admin.staff-attendance-list');
        Route::get('/admin/attendance/staff/{id}/csv', [StaffAttendanceController::class, 'exportCsv'])->name('admin.staff-attendance-csv');
        Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}',[ApplicationController::class,'show'])->name('admin.application-detail');
        Route::patch('/stamp_correction_request/approve/{attendance_correct_request_id}',[ApplicationController::class,'update'])->name('admin.application-update');
});