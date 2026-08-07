<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceRecord;

class AttendanceListUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_自分の勤怠情報が全て表示されている()
    {
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);
        $otherUser = User::factory()->create(['role' => User::ROLE_STAFF]);

        AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => today()->setDay(1),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'finished',
        ]);
        AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => today()->setDay(2),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'finished',
        ]);

        AttendanceRecord::create([
            'user_id' => $otherUser->id,
            'date' => today()->setDay(3),
            'clock_in' => '10:30:00',
            'clock_out' => '19:30:00',
            'status' => 'finished',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.list'));

        $response->assertStatus(200);
        $response->assertSee(today()->setDay(1)->format('m/d'));
        $response->assertSee(today()->setDay(2)->format('m/d'));
    // 他人の打刻時刻(10:30, 19:30)は絶対に表示されないことを確認
        $response->assertDontSee('10:30');
        $response->assertDontSee('19:30');
    }

    public function test_勤怠一覧画面に遷移した際に現在の月が表示される()
    {
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);

        $response = $this->actingAs($user)->get(route('attendance.list'));

        $response->assertStatus(200);
        $response->assertSee(now()->format('Y/m'));
    }

    public function test_前月を押下した時に前月の情報が表示される()
    {
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);

        AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => today()->subMonth()->setDay(15),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'finished',
        ]);

        $prevMonth = now()->subMonth()->format('Y-m');
        $response = $this->actingAs($user)->get(route('attendance.list', ['month' => $prevMonth]));

        $response->assertStatus(200);
        $response->assertSee(today()->subMonth()->format('Y/m'));
        $response->assertSee(today()->subMonth()->setDay(15)->format('m/d'));
    }

    public function test_翌月を押下した時に翌月の情報が表示される()
    {
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);

        AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => today()->addMonth()->setDay(10),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'finished',
        ]);

        $nextMonth = now()->addMonth()->format('Y-m');
        $response = $this->actingAs($user)->get(route('attendance.list', ['month' => $nextMonth]));

        $response->assertStatus(200);
        $response->assertSee(today()->addMonth()->format('Y/m'));
        $response->assertSee(today()->addMonth()->setDay(10)->format('m/d'));
    }
}
