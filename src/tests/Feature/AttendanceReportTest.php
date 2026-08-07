<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceRecord;
use App\Models\BreakTime;

class AttendanceReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_ゲストはレポートページにアクセスできない()
    {
        $response = $this->get(route('attendance.report'));

        $response->assertRedirect(route('login'));
    }

    public function test_認証ユーザーの統計情報が正しく計算される()
    {
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);

        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'finished',
        ]);

        BreakTime::create([
            'attendance_record_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.report'));

        $response->assertStatus(200);
        // 9:00-18:00 - 1時間休憩 = 8時間労働 = "8h0m"
        $response->assertSee('8h0m');
    }

    public function test_勤怠記録がないユーザーで安全に処理される()
    {
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);

        $response = $this->actingAs($user)->get(route('attendance.report'));

        $response->assertStatus(200);
        $response->assertSee('0h0m');
    }
}
