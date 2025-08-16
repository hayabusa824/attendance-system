<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 勤怠詳細画面に表示されるデータが選択したものになっている()
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $user = User::create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => bcrypt('password')
        ]);

        $date = Carbon::today();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $date,
            'time' => $date->copy()->setHour(9)->setMinute(0),
            'type' => 'clock_in',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/attendance/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee('09:00');
    }

    /** @test */
    public function 出勤時間が退勤時間より後の場合はエラーが表示される()
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $user = User::create(['name' => 'User', 'email' => 'user@example.com', 'password' => bcrypt('password')]);
        $date = Carbon::today();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $date,
            'time' => $date->copy()->setHour(9),
            'type' => 'clock_in',
        ]);

        $postUrl = "/admin/admin/attendance/update/{$attendance->id}";

        $response = $this->actingAs($admin, 'admin')
            ->from("/admin/attendance/{$attendance->id}")
            ->post($postUrl, [
                'target_date' => $date->toDateString(),
                'clock_in' => '19:00',
                'clock_out' => '18:00',
                'break_in' => '10:00',
                'break_out' => '11:00',
                'reason' => 'Test note',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['clock_in']);
    }

    /** @test */
    public function 休憩開始時間が退勤時間より後の場合はエラーが表示される()
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $user = User::create(['name' => 'User', 'email' => 'user@example.com', 'password' => bcrypt('password')]);
        $date = Carbon::today();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $date,
            'time' => $date->copy()->setHour(9),
            'type' => 'clock_in',
        ]);

        $postUrl = "/admin/admin/attendance/update/{$attendance->id}";

        $response = $this->actingAs($admin, 'admin')
            ->from("/admin/attendance/{$attendance->id}")
            ->post($postUrl, [
                'target_date' => $date->toDateString(),
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'break_in' => '19:00', // 退勤より後
                'break_out' => '19:30', // 退勤より後
                'reason' => 'Test note',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['break_in','break_out']);
    }

    /** @test */
    public function 休憩終了時間が退勤時間より後の場合はエラーが表示される()
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $user = User::create(['name' => 'User', 'email' => 'user@example.com', 'password' => bcrypt('password')]);
        $date = Carbon::today();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $date,
            'time' => $date->copy()->setHour(9),
            'type' => 'clock_in',
        ]);

        $postUrl = "/admin/admin/attendance/update/{$attendance->id}";

        $response = $this->actingAs($admin, 'admin')
            ->from("/admin/attendance/{$attendance->id}")
            ->post($postUrl, [
                'target_date' => $date->toDateString(),
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'break_in' => '19:00',
                'break_out' => '19:30',
                'reason' => 'Test note',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['break_in','break_out']);
    }

    /** @test */
    public function 備考欄が未入力の場合はエラーが表示される()
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $user = User::create(['name' => 'User', 'email' => 'user@example.com', 'password' => bcrypt('password')]);
        $date = Carbon::today();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $date,
            'time' => $date->copy()->setHour(9),
            'type' => 'clock_in',
        ]);

        $postUrl = "/admin/admin/attendance/update/{$attendance->id}";

        $response = $this->actingAs($admin, 'admin')
            ->from("/admin/attendance/{$attendance->id}")
            ->post($postUrl, [
                'target_date' => $date->toDateString(),
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'break_in' => '09:00',
                'break_out' => '18:00',
                'reason' => '', // 未入力
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['reason']);
    }
}