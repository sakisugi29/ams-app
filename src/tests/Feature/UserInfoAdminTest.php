<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\AttendanceRecord;


class UserInfoAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_管理者ユーザーが全一般ユーザーの氏名メールアドレスを確認できる()
    {
        $admin = User::factory()->create(['role'=>User::ROLE_ADMIN]);
        $user1 = User::factory()->create(['role' => User::ROLE_STAFF, 'name' => 'User One', 'email' => 'user1@example.com']);
        $user2 = User::factory()->create(['role' => User::ROLE_STAFF, 'name' => 'User Two', 'email' => 'user2@example.com']);

        $response = $this->actingAs($admin)->get(route('admin.staff-list'));

        $response->assertStatus(200);
        $response->assertSee($user1->name);
        $response->assertSee($user1->email);
        $response->assertSee($user2->name);
        $response->assertSee($user2->email);
    }

    public function test_ユーザーの勤怠情報が正しく表示される()
    {
        $admin = User::factory()->create(['role'=>User::ROLE_ADMIN]);
        $user = User::factory()->create(['role' => User::ROLE_STAFF, 'name' => 'User One']);

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => now()->subHours(8),
            'clock_out' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.staff-attendance-list', ['id' => $user->id]));

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee(\Carbon\Carbon::parse($attendanceRecord->clock_in)->format('H:i'));
        $response->assertSee(\Carbon\Carbon::parse($attendanceRecord->clock_out)->format('H:i'));
    }

    public function test_前月を押下した時に表示月の前月の情報が表示される()
    {
        $admin = User::factory()->create(['role'=>User::ROLE_ADMIN]);
        $user = User::factory()->create(['role' => User::ROLE_STAFF, 'name' => 'User One']);

        AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => today()->subMonth()->setDay(15),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'finished',
        ]);

        $prevMonth = now()->subMonth()->format('Y-m');
        $response = $this->actingAs($admin)->get(route('admin.staff-attendance-list', ['id' => $user->id, 'month' => $prevMonth]));

        $response->assertStatus(200);
        $response->assertSee(today()->subMonth()->format('Y-m'));
        $response->assertSee(today()->subMonth()->setDay(15)->format('m/d'));
    }

    public function test_翌月を押下した時に翌月の情報が表示される()
    {
        $admin = User::factory()->create(['role'=>User::ROLE_ADMIN]);
        $user = User::factory()->create(['role' => User::ROLE_STAFF, 'name' => 'User One']);

        AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => today()->addMonth()->setDay(10),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'finished',
        ]);

        $nextMonth = now()->addMonth()->format('Y-m');
        $response = $this->actingAs($admin)->get(route('admin.staff-attendance-list', ['id' => $user->id,'month' => $nextMonth]));

        $response->assertStatus(200);
        $response->assertSee(today()->addMonth()->format('Y-m'));
        $response->assertSee(today()->addMonth()->setDay(10)->format('m/d'));
    }

    public function test_詳細を押下するとその日の勤怠詳細画面に遷移する()
    {
        $admin = User::factory()->create(['role'=>User::ROLE_ADMIN]);
        $user = User::factory()->create(['role' => User::ROLE_STAFF, 'name' => 'User One']);

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => today()->setDay(5),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'finished',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.staff-attendance-list', ['id' => $user->id]));

        $response->assertStatus(200);
        $response->assertSee(route('admin.attendance-show', ['id' => $user->id . '_' . today()->setDay(5)->format('Y-m-d')]));
    }
}
