<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceRecord;

class AttendanceClockOutTest extends TestCase
{
    use RefreshDatabase;

    public function test_退勤ボタンを押すとステータスが退勤済になる()
    {
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);

        AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now(),
            'status' => 'working',
        ]);

        $response = $this->actingAs($user)->post(route('attendance.clockOut'));

        $response->assertRedirect(route('attendance.index'));

        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $user->id,
            'status' => 'finished',
        ]);

        $indexResponse = $this->actingAs($user)->get(route('attendance.index'));
        $indexResponse->assertSee('お疲れ様でした。');
    }

    public function test_退勤時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);

        $this->travelTo(today()->setTime(9, 0, 0));
        AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now(),
            'status' => 'working',
        ]);

        $this->travelTo(today()->setTime(18, 0, 0));
        $this->actingAs($user)->post(route('attendance.clockOut'));

        $response = $this->actingAs($user)->get(route('attendance.list'));

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
}
