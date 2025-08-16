<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Request as RequestModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AdminStampCorrectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 承認待ちの修正申請が一覧に表示される()
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

        $pendingRequest = RequestModel::create([
            'user_id' => $user->id,
            'target_date' => Carbon::today()->toDateString(),
            'reason' => '出勤時間修正',
            'status' => 'pending',
            'applied_date' => Carbon::now(),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/stamp_correction_request/list');

        $response->assertStatus(200)
            ->assertSee($user->name)
            ->assertSee('出勤時間修正');
    }

    /** @test */
    public function 承認済みの修正申請が一覧に表示される()
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

        $approvedRequest = RequestModel::create([
            'user_id' => $user->id,
            'target_date' => Carbon::today()->toDateString(),
            'reason' => '退勤時間修正',
            'status' => 'approved',
            'applied_date' => Carbon::now(),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/stamp_correction_request/list?status=approved');

        $response->assertStatus(200)
            ->assertSee($user->name)
            ->assertSee('退勤時間修正');
    }

    /** @test */
    public function 修正申請の詳細内容が正しく表示される()
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

        $request = RequestModel::create([
            'user_id' => $user->id,
            'target_date' => Carbon::today()->toDateString(),
            'reason' => '出勤時間修正',
            'status' => 'pending',
            'applied_date' => Carbon::now(),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('stamp_correction_request.approve', ['attendance_correct_request' => $request->id]));

        $response->assertStatus(200)
            ->assertSee($user->name)
            ->assertSee('出勤時間修正');
    }

    /** @test */
    public function 修正申請の承認処理が正しく行われる()
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

        $request = RequestModel::create([
            'user_id' => $user->id,
            'target_date' => Carbon::today()->toDateString(),
            'reason' => '出勤時間修正',
            'status' => 'pending',
            'applied_date' => Carbon::now(),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->post(route('stamp_correction_request.approve.action', ['id' => $request->id]));

        $response->assertStatus(302);

        $this->assertDatabaseHas('requests', [
            'id' => $request->id,
            'status' => 'approved',
        ]);
    }
}
