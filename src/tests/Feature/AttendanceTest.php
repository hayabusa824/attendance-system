<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 出勤ボタンが表示されて出勤処理ができる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertSee('出勤');

        $this->post('/attendance/clock-in');
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'type' => 'clock_in',
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('勤務中');
    }

    /** @test */
    public function 出勤は一日一回のみ()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'type' => 'clock_out',
            'time' => now(),
        ]);

        $response = $this->get('/attendance');
        $response->assertDontSee('出勤');
    }

    /** @test */
    public function 出勤時刻が勤怠一覧画面に表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('/attendance/clock-in');
        $response = $this->get('/attendance/list'); // 勤怠一覧ルート

        $response->assertSee(now()->format('H:i'));
    }

    /** @test */
    public function 休憩入処理でステータスが休憩中になる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'type' => 'clock_in',
            'time' => now()->subHours(1),
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('休憩入');

        $this->post('/attendance/break-in');

        $response = $this->get('/attendance');
        $response->assertSee('休憩中');
    }

    /** @test */
    public function 休憩は何回でもできる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::create(['user_id' => $user->id, 'date' => today(), 'type' => 'clock_in', 'time' => now()->subHours(2)]);
        Attendance::create(['user_id' => $user->id, 'date' => today(), 'type' => 'break_in', 'time' => now()->subHour()]);
        Attendance::create(['user_id' => $user->id, 'date' => today(), 'type' => 'break_out', 'time' => now()->subMinutes(30)]);

        $response = $this->get('/attendance');
        $response->assertSee('休憩入');
    }

    /** @test */
    public function 休憩戻処理後ステータスが勤務中になる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::create(['user_id' => $user->id, 'date' => today(), 'type' => 'clock_in', 'time' => now()->subHours(2)]);
        Attendance::create(['user_id' => $user->id, 'date' => today(), 'type' => 'break_in', 'time' => now()->subHour()]);

        $this->post('/attendance/break-out');

        $response = $this->get('/attendance');
        $response->assertSee('勤務中');
    }

    /** @test */
    public function 休憩時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $date = now()->toDateString();

        Attendance::create([
            'user_id' => $user->id,
            'date' => $date,
            'type' => 'clock_in',
            'time' => now()->subHours(3),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'date' => $date,
            'type' => 'break_in',
            'time' => now()->subHours(2),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'date' => $date,
            'type' => 'break_out',
            'time' => now()->subHour(),
        ]);

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('1:00');
    }

    /** @test */
    public function 退勤処理でステータスが退勤済になる()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::create(['user_id' => $user->id, 'date' => today(), 'type' => 'clock_in', 'time' => now()->subHours(8)]);

        $response = $this->get('/attendance');
        $response->assertSee('退勤');

        $this->post('/attendance/clock-out');

        $response = $this->get('/attendance');
        $response->assertSee('退勤済');
    }

    /** @test */
    public function 退勤時刻が勤怠一覧画面に表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::create(['user_id' => $user->id, 'date' => today(), 'type' => 'clock_in', 'time' => now()->subHours(9)]);
        Attendance::create(['user_id' => $user->id, 'date' => today(), 'type' => 'clock_out', 'time' => now()]);

        $response = $this->get('/attendance/list');
        $response->assertSee(now()->format('H:i'));
    }
}
