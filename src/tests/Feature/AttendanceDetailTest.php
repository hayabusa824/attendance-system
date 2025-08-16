<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 勤怠詳細画面に名前が表示される()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-08-05',
            'time' => '2025-08-05 09:00:00',
            'type' => 'clock_in'
        ]);

        $this->actingAs($user)
            ->get(route('attendance.detail', ['id' => $attendance->id]))
            ->assertStatus(200)
            ->assertSee($user->name);
    }

    /** @test */
    public function 勤怠詳細画面に日付が表示される()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-08-05',
            'time' => '2025-08-05 09:00:00',
            'type' => 'clock_in'
        ]);

        $this->actingAs($user)
            ->get(route('attendance.detail', ['id' => $attendance->id]))
            ->assertStatus(200)
            ->assertSee('2025-08-05');
    }

    /** @test */
    public function 勤怠詳細画面に出勤退勤時間が表示される()
    {
        $user = User::factory()->create();

        $clockIn = Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-08-05',
            'time' => '2025-08-05 09:00:00',
            'type' => 'clock_in'
        ]);
        Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-08-05',
            'time' => '2025-08-05 18:00:00',
            'type' => 'clock_out'
        ]);

        $this->actingAs($user)
            ->get(route('attendance.detail', ['id' => $clockIn->id]))
            ->assertStatus(200)
            ->assertSee('09:00')
            ->assertSee('18:00');
    }

    /** @test */
    public function 勤怠詳細画面に休憩時間が表示される()
    {
        $user = User::factory()->create();

        $breakIn = Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-08-05',
            'time' => '2025-08-05 12:00:00',
            'type' => 'break_in'
        ]);
        Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-08-05',
            'time' => '2025-08-05 13:00:00',
            'type' => 'break_out'
        ]);

        $this->actingAs($user)
            ->get(route('attendance.detail', ['id' => $breakIn->id]))
            ->assertStatus(200)
            ->assertSee('12:00')
            ->assertSee('13:00');
    }

    /** @test */
    public function 出勤時間が退勤時間より後ならエラー()
    {
        $user = User::factory()->create();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-08-05',
            'time' => '2025-08-05 09:00:00',
            'type' => 'clock_in'
        ]);

        $this->actingAs($user)
            ->post(route('correction.store'), [
                'attendance_id' => $attendance->id,
                'clock_in' => '20:00',
                'clock_out' => '18:00',
                'note' => 'テスト'
            ])
            ->assertSessionHasErrors();
    }

    /** @test */
    public function 休憩開始時間が退勤時間より後ならエラー()
    {
        $user = User::factory()->create();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-08-05',
            'time' => '2025-08-05 09:00:00',
            'type' => 'clock_in'
        ]);

        $this->actingAs($user)
            ->post(route('correction.store'), [
                'attendance_id' => $attendance->id,
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'break_in' => '19:00',
                'note' => 'テスト'
            ])
            ->assertSessionHasErrors();
    }

    /** @test */
    public function 休憩終了時間が退勤時間より後ならエラー()
    {
        $user = User::factory()->create();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-08-05',
            'time' => '2025-08-05 09:00:00',
            'type' => 'clock_in'
        ]);

        $this->actingAs($user)
            ->post(route('correction.store'), [
                'attendance_id' => $attendance->id,
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'break_out' => '19:00',
                'note' => 'テスト'
            ])
            ->assertSessionHasErrors();
    }

    /** @test */
    public function 備考未入力ならエラー()
    {
        $user = User::factory()->create();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-08-05',
            'time' => '2025-08-05 09:00:00',
            'type' => 'clock_in'
        ]);

        $this->actingAs($user)
            ->post(route('correction.store'), [
                'attendance_id' => $attendance->id,
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'note' => ''
            ])
            ->assertSessionHasErrors();
    }

    /** @test */
    public function 修正申請が正常に保存される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-08-05',
            'time' => '2025-08-05 09:00:00',
            'type' => 'clock_in'
        ]);

        $this->post(route('correction.store'), [
            'record_id' => $attendance->id,
            'target_date' => '2025-08-05',
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'reason' => '修正申請テスト',
            'break_in' => ['12:00'],
            'break_out' => ['13:00']
        ])->assertSessionHasNoErrors();
    }

    /** @test */
    public function 承認待ち一覧に自分の申請が全て表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        \App\Models\Request::create([
            'user_id' => $user->id,
            'target_date' => '2025-08-05', // 必須
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'reason' => '承認待ちテスト',
            'applied_date' => now(),
            'status' => 'pending',
        ]);

        $this->get('/stamp_correction_request/list?status=pending')
            ->assertStatus(200)
            ->assertSee('承認待ちテスト');
    }

    /** @test */
    public function 承認済み一覧に管理者が承認した申請が全て表示される()
    {
        $admin = User::factory()->create();
        $this->actingAs($admin, 'admin');

        \App\Models\Request::create([
            'user_id' => $admin->id,
            'target_date' => '2025-08-05',
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'reason' => '承認済みテスト',
            'applied_date' => now(),
            'status' => 'approved',
        ]);

        $this->get('/stamp_correction_request/list?status=approved')
            ->assertStatus(200)
            ->assertSee('承認済みテスト');
    }

    /** @test */
    public function 申請詳細画面に遷移できる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-08-05',
            'time' => '2025-08-05 09:00:00',
            'type' => 'clock_in'
        ]);

        $requestModel = \App\Models\Request::create([
            'user_id' => $user->id,
            'target_date' => '2025-08-05',
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'reason' => '詳細画面テスト',
            'applied_date' => now(),
            'status' => 'pending',
        ]);

        $this->get(route('attendance.detail.fromRequest', ['request_id' => $requestModel->id]))
            ->assertStatus(200)
            ->assertSee('詳細画面テスト');
    }
}