<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceRecord;

class AttendanceBreakTest extends TestCase
{
    use RefreshDatabase;

    private function createWorkingAttendance(User $user): AttendanceRecord
    {
        return AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now(),
            'status' => 'working',
        ]);
    }

    public function test_休憩ボタンを押すとステータスが休憩中になる()
    {
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);
        $this->createWorkingAttendance($user);

        $response = $this->actingAs($user)->post(route('attendance.startBreak'));

        $response->assertRedirect(route('attendance.index'));

        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $user->id,
            'status' => 'on_break',
        ]);

        $indexResponse = $this->actingAs($user)->get(route('attendance.index'));
        $indexResponse->assertSee('休憩中');
    }

    public function test_休憩は一日に何回でもできる()
    {
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);
        $this->createWorkingAttendance($user);

        // 1回目：休憩入→休憩戻
        $this->actingAs($user)->post(route('attendance.startBreak'));
        $this->actingAs($user)->post(route('attendance.endBreak'));

        // 2回目の休憩入ボタンが表示されているか確認
        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertSee('休憩入</button>', false);
    }

    public function test_休憩戻ボタンを押すとステータスが出勤中に戻る()
    {
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);
        $this->createWorkingAttendance($user);

        $this->actingAs($user)->post(route('attendance.startBreak'));
        $response = $this->actingAs($user)->post(route('attendance.endBreak'));

        $response->assertRedirect(route('attendance.index'));

        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $user->id,
            'status' => 'working',
        ]);

        $indexResponse = $this->actingAs($user)->get(route('attendance.index'));
        $indexResponse->assertSee('出勤中');
    }

    public function test_休憩戻は一日に何回でもできる()
    {
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);
        $this->createWorkingAttendance($user);

        // 1回目：休憩入→休憩戻
        $this->actingAs($user)->post(route('attendance.startBreak'));
        $this->actingAs($user)->post(route('attendance.endBreak'));

        // 2回目の休憩入
        $this->actingAs($user)->post(route('attendance.startBreak'));

        // 休憩戻ボタンが表示されているか確認
        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertSee('休憩戻</button>', false);
    }

    public function test_休憩時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);
        $this->createWorkingAttendance($user);

        $this->travelTo(today()->setTime(12, 0, 0));
        $this->actingAs($user)->post(route('attendance.startBreak'));

        $this->travelTo(today()->setTime(13, 0, 0));
        $this->actingAs($user)->post(route('attendance.endBreak'));

        $response = $this->actingAs($user)->get(route('attendance.list'));

        $response->assertStatus(200);
        // 休憩合計1時間が表示されているか(break_totalの計算結果)
        $response->assertSee('1:00');
    }
}
