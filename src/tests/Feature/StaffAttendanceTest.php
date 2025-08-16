<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class StaffAttendanceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 管理者は全一般ユーザーの氏名とメールアドレスを確認できる()
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $user1 = User::create(['name' => 'User1', 'email' => 'user1@example.com', 'password' => bcrypt('password')]);
        $user2 = User::create(['name' => 'User2', 'email' => 'user2@example.com', 'password' => bcrypt('password')]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/staff/list');

        $response->assertStatus(200)
            ->assertSee($user1->name)
            ->assertSee($user1->email)
            ->assertSee($user2->name)
            ->assertSee($user2->email);
    }

    /** @test */
    public function 選択したユーザーの勤怠情報が正しく表示される()
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $user = User::create(['name' => 'User1', 'email' => 'user1@example.com', 'password' => bcrypt('password')]);

        $today = Carbon::today();

        // 勤怠データ作成
        Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'time' => $today->copy()->setHour(9)->setMinute(0),
            'type' => 'clock_in'
        ]);
        Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'time' => $today->copy()->setHour(18)->setMinute(0),
            'type' => 'clock_out'
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/attendance/staff/{$user->id}?year={$today->year}&month={$today->month}");

        $response->assertStatus(200)
            ->assertSee($user->name)
            ->assertSee('09:00')
            ->assertSee('18:00');
    }

    /** @test */
    public function 前月ボタンを押すと前月の勤怠情報が表示される()
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $user = User::create(['name' => 'User1', 'email' => 'user1@example.com', 'password' => bcrypt('password')]);

        $currentMonth = Carbon::now();
        $previousMonth = $currentMonth->copy()->subMonth();

        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/attendance/staff/{$user->id}?year={$previousMonth->year}&month={$previousMonth->month}");

        $response->assertStatus(200)
            ->assertSee($previousMonth->format('Y'));
    }

    /** @test */
    public function 翌月ボタンを押すと翌月の勤怠情報が表示される()
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $user = User::create(['name' => 'User1', 'email' => 'user1@example.com', 'password' => bcrypt('password')]);

        $currentMonth = Carbon::now();
        $nextMonth = $currentMonth->copy()->addMonth();

        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/attendance/staff/{$user->id}?year={$nextMonth->year}&month={$nextMonth->month}");

        $response->assertStatus(200)
            ->assertSee($nextMonth->format('Y'));
    }

    /** @test */
    public function 勤怠詳細ボタンを押すとその日の詳細画面に遷移する()
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $user = User::create(['name' => 'User1', 'email' => 'user1@example.com', 'password' => bcrypt('password')]);

        $today = Carbon::today();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'time' => $today->copy()->setHour(9),
            'type' => 'clock_in'
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/attendance/{$attendance->id}");

        $response->assertStatus(200)
            ->assertSee($user->name)
            ->assertSee('09:00');
    }
}
