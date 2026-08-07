<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AttendanceRecordController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::prefix('v1')->group(function () {
    // 認証不要のグループ(GET系)
    Route::get('/attendance-records', [AttendanceRecordController::class, 'index'])->name('attendance-records.index');
    Route::get('/attendance-records/{attendanceRecord}', [AttendanceRecordController::class, 'show'])->name('attendance-records.show');

    // 認証必要のグループ(POST/PUT/DELETE系)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/attendance-records', [AttendanceRecordController::class, 'store'])->name('attendance-records.store');
        Route::match(['put', 'patch'], '/attendance-records/{attendanceRecord}', [AttendanceRecordController::class, 'update'])->name('attendance-records.update');
        Route::delete('/attendance-records/{attendanceRecord}', [AttendanceRecordController::class, 'destroy'])->name('attendance-records.destroy');
    });
});
