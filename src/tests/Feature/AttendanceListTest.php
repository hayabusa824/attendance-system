<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 勤怠一覧に自分の勤怠情報が全て表示される()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        // 自分の勤怠情報
        Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-08-01',
            'time' => '2025-08-01 09:00:00',
            'type' => 'clock_in'
        ]);
        Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-08-01',
            'time' => '2025-08-01 18:00:00',
            'type' => 'clock_out'
        ]);

        // 他人の勤怠情報（表示されないはず）
        Attendance::create([
            'user_id' => $otherUser->id,
            'date' => '2025-08-01',
            'time' => '2025-08-01 09:00:00',
            'type' => 'clock_in'
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertDontSee($otherUser->name);
    }

    /** @test */
    public function 勤怠一覧は現在の月が表示される()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/attendance/list')
            ->assertSee(now()->format('Y/m'));
    }

    /** @test */
    public function 前月ボタンで前月が表示される()
    {
        $user = User::factory()->create();

        $prevMonth = now()->subMonth();

        $this->actingAs($user)
            ->get('/attendance/list?year=' . $prevMonth->year . '&month=' . $prevMonth->month)
            ->assertSee($prevMonth->format('Y/m'));
    }

    /** @test */
    public function 翌月ボタンで翌月が表示される()
    {
        $user = User::factory()->create();

        $nextMonth = now()->addMonth();

        $this->actingAs($user)
            ->get('/attendance/list?year=' . $nextMonth->year . '&month=' . $nextMonth->month)
            ->assertSee($nextMonth->format('Y/m'));
    }

    /** @test */
    public function 詳細リンクで勤怠詳細に遷移できる()
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
}
