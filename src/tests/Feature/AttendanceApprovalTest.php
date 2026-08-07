<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceRecord;

class AttendanceApprovalTest extends TestCase
{
    use RefreshDatabase;

    private function createFinishedAttendance(User $user): AttendanceRecord
    {
        return AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'finished',
        ]);
    }

    public function test_詳細画面に表示されるデータが選択したものになっている()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);

        $attendance = $this->createFinishedAttendance($user);

        $response = $this->actingAs($admin)->get(route('admin.attendance-update', ['id' => $user->id . '_' . \Carbon\Carbon::parse($attendance->date)->format('Y-m-d')]));
        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee(\Carbon\Carbon::parse($attendance->date)->format('Y年'));
        $response->assertSee(\Carbon\Carbon::parse($attendance->date)->format('m月d日'));
        $response->assertSee(\Carbon\Carbon::parse($attendance->clock_in)->format('H:i'));
        $response->assertSee(\Carbon\Carbon::parse($attendance->clock_out)->format('H:i'));
    }

    public function test_出勤時間が退勤時間より後になっている場合エラーメッセージが表示される()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);

        $attendance = $this->createFinishedAttendance($user);

        $response = $this->actingAs($admin)->put(route('admin.attendance-update', ['id' => $user->id . '_' . \Carbon\Carbon::parse($attendance->date)->format('Y-m-d')]),[
            'clock_in' => '19:00',
            'clock_out' => '18:00',
            'comment' => 'テスト備考',
        ]);


        $response->assertSessionHasErrors([ 'clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_休憩開始時間が退勤時間より後の場合エラーメッセージが表示される()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);
        $attendance = $this->createFinishedAttendance($user);

        $response = $this->actingAs($admin)->put(route('admin.attendance-update', ['id' => $user->id . '_' . \Carbon\Carbon::parse($attendance->date)->format('Y-m-d')]), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_start1' => '19:00',
            'comment' => 'テスト備考',
        ]);

        $response->assertSessionHasErrors([
            'break_start1' => '休憩時間が不適切な値です',
        ]);
    }

    public function test_休憩終了時間が退勤時間より後の場合エラーメッセージが表示される()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);
        $attendance = $this->createFinishedAttendance($user);

        $response = $this->actingAs($admin)->put(route('admin.attendance-update', ['id' => $user->id . '_' . \Carbon\Carbon::parse($attendance->date)->format('Y-m-d')]), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_start1' => '12:00',
            'break_end1' => '19:00',
            'comment' => 'テスト備考',
        ]);

        $response->assertSessionHasErrors([
            'break_end1' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_備考欄が未入力の場合エラーメッセージが表示される()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);
        $attendance = $this->createFinishedAttendance($user);

        $response = $this->actingAs($admin)->put(route('admin.attendance-update', ['id' => $user->id . '_' . \Carbon\Carbon::parse($attendance->date)->format('Y-m-d')]), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'comment' => '',
        ]);

        $response->assertSessionHasErrors([
            'comment' => '備考を記入してください',
        ]);
    }
}
