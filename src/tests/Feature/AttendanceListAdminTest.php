<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceRecord;

class AttendanceListAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_その日になされた全ユーザーの勤怠情報が正確に確認できる()
    {
        $admin = User::factory()->create(['role'=> User::ROLE_ADMIN]);
        $user1 = User::factory()->create(['role'=> User::ROLE_STAFF]);
        $user2 = User::factory()->create(['role'=> User::ROLE_STAFF]);

        AttendanceRecord::create([
            'user_id' => $user1->id,
            'date' => today(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'finished',
        ]);

        AttendanceRecord::create([
            'user_id' => $user2->id,
            'date' => today(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'finished',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance-list'));

        $response->assertStatus(200);
        $response->assertSee($user1->name);
        $response->assertSee($user2->name);
    }

    public function test_遷移した際に現在の日付が表示される()
    {
        $admin = User::factory()->create(['role'=> User::ROLE_ADMIN]);

        $response = $this->actingAs($admin)->get(route('admin.attendance-list'));

        $response->assertStatus(200);
        $response->assertSee(today()->format('Y/m/d'));
    }

    public function test_前日を押下した時に前の日の勤怠情報が表示される()
    {
        $admin = User::factory()->create(['role'=> User::ROLE_ADMIN]);
        $user1 = User::factory()->create(['role'=> User::ROLE_STAFF]);

        AttendanceRecord::create([
            'user_id' => $user1->id,
            'date' => today()->subDay(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'finished',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance-list', ['date' => today()->subDay()->format('Y-m-d')]));

        $response->assertStatus(200);
        $response->assertSee(today()->subDay()->format('Y-m-d'));
        $response->assertSee($user1->name);
    }

    public function test_翌日を押下した時に次の日の勤怠情報が表示される()
    {
        $admin = User::factory()->create(['role'=> User::ROLE_ADMIN]);
        $user1 = User::factory()->create(['role'=> User::ROLE_STAFF]);

        AttendanceRecord::create([
            'user_id' => $user1->id,
            'date' => today()->addDay(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'finished',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance-list', ['date' => today()->addDay()->format('Y-m-d')]));

        $response->assertStatus(200);
        $response->assertSee(today()->addDay()->format('Y-m-d'));
        $response->assertSee($user1->name);
    }
}
