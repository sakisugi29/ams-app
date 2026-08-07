<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceRecord;

class AttendanceClockInTest extends TestCase
{
    use RefreshDatabase;

    public function test_出勤ボタンを押すとステータスが出勤中になる()
    {
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);

        $response = $this->actingAs($user)->post(route('attendance.clockIn'));

        $response->assertRedirect(route('attendance.index'));

        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $user->id,
            'date' => today()->toDateString(),
            'status' => 'working',
        ]);

        $indexResponse = $this->actingAs($user)->get(route('attendance.index'));
        $indexResponse->assertSee('出勤中');
        $indexResponse->assertDontSee('>出勤<', false);
    }

    public function test_退勤済の場合出勤ボタンが表示されない()
    {
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);

        AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now()->subHours(8),
            'clock_out' => now(),
            'status' => 'finished',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.index'));

        $response->assertDontSee('>出勤<', false);
    }

    public function test_出勤時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);

        $this->travelTo(today()->setTime(9, 0, 0));

        $this->actingAs($user)->post(route('attendance.clockIn'));

        $response = $this->actingAs($user)->get(route('attendance.list'));

        $response->assertStatus(200);
        $response->assertSee(today()->format('m/d'));
        $response->assertSee('09:00');
    }
}
