<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // 一般ユーザー
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // 管理者ユーザー
        $this->admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('adminpass123'),
            'is_admin' => true,
        ]);
    }

    /** @test */
    public function メールアドレスが未入力の場合_バリデーションエラーが表示される()
    {
        $response = $this->from('/login')->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function パスワードが未入力の場合_バリデーションエラーが表示される()
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function 登録内容と一致しない場合_バリデーションエラーが表示される()
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors([
            'email' => '認証情報と一致するレコードがありません。',
        ]);
    }

    /** @test */
    public function 管理者_メールアドレスが未入力の場合_バリデーションメッセージが表示される()
    {
        $response = $this->from('/admin/login')->post('/admin/attendance/list', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest('admin');
    }

    /** @test */
    public function 管理者_パスワードが未入力の場合_バリデーションメッセージが表示される()
    {
        $response = $this->from('/admin/login')->post('/admin/attendance/list', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors(['password']);
        $this->assertGuest('admin');
    }

    /** @test */
    public function 管理者_誤ったメールアドレスの場合_エラーメッセージが表示される()
    {
        // 正しい管理者ユーザー（ただしテストでは使わない）
        User::factory()->create([
            'email' => 'admin' . uniqid() . '@example.com',
            'password' => Hash::make('password123'),
            'is_admin' => true,
        ]);

        // 間違ったメールアドレスでログイン試行
        $response = $this->from('/admin/login')->post('/admin/attendance/list', [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest('admin');
    }
}

