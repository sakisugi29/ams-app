<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\AttendanceRecord;
use App\Models\CorrectionRequest;

class AttendanceCorrectionUserTest extends TestCase
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

    public function test_出勤時間が退勤時間より後の場合エラーメッセージが表示される()
    {
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);
        $attendance = $this->createFinishedAttendance($user);

        $response = $this->actingAs($user)->put(route('attendance.update', $attendance->date->format('Y-m-d')), [
            'clock_in' => '19:00',
            'clock_out' => '18:00',
            'comment' => 'テスト備考',
        ]);

        $response->assertSessionHasErrors([
            'clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_休憩開始時間が退勤時間より後の場合エラーメッセージが表示される()
    {
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);
        $attendance = $this->createFinishedAttendance($user);

        $response = $this->actingAs($user)->put(route('attendance.update', $attendance->date->format('Y-m-d')), [
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
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);
        $attendance = $this->createFinishedAttendance($user);

        $response = $this->actingAs($user)->put(route('attendance.update', $attendance->date->format('Y-m-d')), [
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
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);
        $attendance = $this->createFinishedAttendance($user);

        $response = $this->actingAs($user)->put(route('attendance.update', $attendance->date->format('Y-m-d')), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'comment' => '',
        ]);

        $response->assertSessionHasErrors([
            'comment' => '備考を記入してください',
        ]);
    }

    public function test_修正申請処理が実行される()
    {
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);
        $attendance = $this->createFinishedAttendance($user);

        $response = $this->actingAs($user)->put(route('attendance.update', $attendance->date->format('Y-m-d')), [
            'clock_in' => '09:30',
            'clock_out' => '18:00',
            'comment' => '電車遅延のため',
        ]);

        $response->assertRedirect(route('attendance.show', $attendance->date->format('Y-m-d')));

        $this->assertDatabaseHas('correction_requests', [
            'attendance_record_id' => $attendance->id,
            'status' => '承認待ち',
        ]);
    }

    public function test_承認待ちにログインユーザーが行った申請が全て表示されている()
    {
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);
        $attendance = $this->createFinishedAttendance($user);

        $this->actingAs($user)->put(route('attendance.update', $attendance->date->format('Y-m-d')), [
            'clock_in' => '09:30',
            'clock_out' => '18:00',
            'comment' => '電車遅延のため',
        ]);

        $response = $this->actingAs($user)->get(route('application.index', ['tab' => 'pending']));

        $response->assertStatus(200);
        $response->assertSee('電車遅延のため');
    }

    public function test_承認済みに管理者が承認した修正申請が全て表示されている()
    {
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);
        $attendance = $this->createFinishedAttendance($user);

        $this->actingAs($user)->put(route('attendance.update', $attendance->date->format('Y-m-d')), [
            'clock_in' => '09:30',
            'clock_out' => '18:00',
            'comment' => '承認済みテスト',
        ]);

        $correctionRequest = CorrectionRequest::where('attendance_record_id', $attendance->id)->first();
        $correctionRequest->update(['status' => '承認済み']);

        $response = $this->actingAs($user)->get(route('application.index', ['tab' => 'approved']));

        $response->assertStatus(200);
        $response->assertSee('承認済みテスト');
    }

    public function test_各申請の詳細を押下すると勤怠詳細画面に遷移する()
    {
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);
        $attendance = $this->createFinishedAttendance($user);

        $this->actingAs($user)->put(route('attendance.update', $attendance->date->format('Y-m-d')), [
            'clock_in' => '09:30',
            'clock_out' => '18:00',
            'comment' => 'テスト備考',
        ]);

        $response = $this->actingAs($user)->get(route('application.index'));

        $response->assertStatus(200);
        // 一覧内に詳細へのリンク(勤怠詳細画面のURL)が含まれているか確認
        $response->assertSee(route('attendance.show', \Carbon\Carbon::parse($attendance->date)->format('Y-m-d')));
    }
}