<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceRecord;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    private function loginAsStaff(): User
    {
        return User::factory()->create(['role' => User::ROLE_STAFF]);
    }

    public function test_勤務外の場合ステータスが正しく表示される()
    {
        $user = $this->loginAsStaff();
        // レコードを作らない = not_working がデフォルト

        $response = $this->actingAs($user)->get(route('attendance.index'));

        $response->assertSee('勤務外');
    }

    public function test_出勤中の場合ステータスが正しく表示される()
    {
        $user = $this->loginAsStaff();
        AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now(),
            'status' => 'working',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.index'));

        $response->assertSee('出勤中');
    }

    public function test_休憩中の場合ステータスが正しく表示される()
    {
        $user = $this->loginAsStaff();
        AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now(),
            'status' => 'on_break',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.index'));

        $response->assertSee('休憩中');
    }

    public function test_退勤済の場合ステータスが正しく表示される()
    {
        $user = $this->loginAsStaff();
        AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now()->subHours(8),
            'clock_out' => now(),
            'status' => 'finished',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.index'));

        $response->assertSee('退勤済');
    }
}
