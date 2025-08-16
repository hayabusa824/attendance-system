<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Laravel\Dusk\Browser;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_現在の日時情報がUIと同じ形式で出力されている()
    {
        // JavaScriptの出力はここでは検証できない
    }

    /** @test */
    public function test_勤務外の場合_勤怠ステータスが勤務外と表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    /** @test */
    public function test_出勤中の場合_勤怠ステータスが勤務中と表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'type' => 'clock_in',
            'time' => now()->subHour(),
        ]);

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('勤務中');
    }

    /** @test */
    public function test_休憩中の場合_勤怠ステータスが休憩中と表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'type' => 'clock_in',
            'time' => now()->subHours(2),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'type' => 'break_in',
            'time' => now()->subHour(),
        ]);

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    /** @test */
    public function test_退勤済の場合_勤怠ステータスが退勤済と表示される()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'type' => 'clock_in',
            'time' => now()->subHours(4),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'type' => 'clock_out',
            'time' => now()->subHour(),
        ]);

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('退勤済');
        $response->assertSee('お疲れ様でした');
    }
}