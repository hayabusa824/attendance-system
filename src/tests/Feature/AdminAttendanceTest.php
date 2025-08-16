<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AdminAttendanceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 管理者はその日の全ユーザーの勤怠情報を確認できる()
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $user1 = User::create(['name' => 'User1', 'email' => 'user1@example.com', 'password' => bcrypt('password')]);
        $user2 = User::create(['name' => 'User2', 'email' => 'user2@example.com', 'password' => bcrypt('password')]);

        $today = Carbon::today();

        Attendance::create([
            'user_id' => $user1->id,
            'date' => $today,
            'time' => $today->copy()->setHour(9)->setMinute(0),
            'type' => 'clock_in',
        ]);
        Attendance::create([
            'user_id' => $user1->id,
            'date' => $today,
            'time' => $today->copy()->setHour(18)->setMinute(0),
            'type' => 'clock_out',
        ]);
        Attendance::create([
            'user_id' => $user2->id,
            'date' => $today,
            'time' => $today->copy()->setHour(10)->setMinute(0),
            'type' => 'clock_in',
        ]);
        Attendance::create([
            'user_id' => $user2->id,
            'date' => $today,
            'time' => $today->copy()->setHour(19)->setMinute(0),
            'type' => 'clock_out',
        ]);

        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSee($user1->name)->assertSee('09:00')->assertSee('18:00');
        $response->assertSee($user2->name)->assertSee('10:00')->assertSee('19:00');
        $response->assertSee($today->format('Y年n月j日'));
    }

    /** @test */
    public function 前日ボタンで前日の勤怠情報が表示される()
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $user = User::create(['name' => 'User', 'email' => 'user@example.com', 'password' => bcrypt('password')]);

        $yesterday = Carbon::yesterday();

        Attendance::create([
            'user_id' => $user->id,
            'date' => $yesterday,
            'time' => $yesterday->copy()->setHour(9)->setMinute(0),
            'type' => 'clock_in',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/attendance/list?date={$yesterday->format('Y-m-d')}");

        $response->assertStatus(200);
        $response->assertSee($yesterday->format('Y年n月j日'));
        $response->assertSee('09:00');
    }

    /** @test */
    public function 翌日ボタンで翌日の勤怠情報が表示される()
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $user = User::create(['name' => 'User', 'email' => 'user@example.com', 'password' => bcrypt('password')]);

        $tomorrow = Carbon::tomorrow();

        Attendance::create([
            'user_id' => $user->id,
            'date' => $tomorrow,
            'time' => $tomorrow->copy()->setHour(10)->setMinute(0),
            'type' => 'clock_in',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/attendance/list?date={$tomorrow->format('Y-m-d')}");

        $response->assertStatus(200);
        $response->assertSee($tomorrow->format('Y年n月j日'));
        $response->assertSee('10:00');
    }
}
