<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceRecord;
use App\Models\BreakTime;

class AttendanceDetailUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_勤怠詳細画面の名前がログインユーザーの氏名になっている()
    {
        $user = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'name' => 'テスト太郎',
        ]);

        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'finished',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.show', $attendance->date->format('Y-m-d')));

        $response->assertStatus(200);
        $response->assertSee('テスト太郎');
    }

    public function test_勤怠詳細画面の日付が選択した日付になっている()
    {
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);

        $targetDate = today()->subDays(3);
        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => $targetDate,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'finished',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.show', $attendance->date->format('Y-m-d')));

        $response->assertStatus(200);
        $response->assertSee($targetDate->format('Y年'));
        $response->assertSee($targetDate->format('m月d日'));
    }

    public function test_出勤退勤の時間がログインユーザーの打刻と一致している()
    {
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);

        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => '09:15:00',
            'clock_out' => '18:30:00',
            'status' => 'finished',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.show', $attendance->date->format('Y-m-d')));

        $response->assertStatus(200);
        $response->assertSee('value="09:15"', false);
        $response->assertSee('value="18:30"', false);
    }

    public function test_休憩の時間がログインユーザーの打刻と一致している()
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

        $response = $this->actingAs($user)->get(route('attendance.show', $attendance->date->format('Y-m-d')));

        $response->assertStatus(200);
        $response->assertSee('value="12:00"', false);
        $response->assertSee('value="13:00"', false);
    }
}

