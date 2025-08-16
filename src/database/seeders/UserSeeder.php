<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('adminpass'),
            'is_admin' => true,
            'email_verified_at' => Carbon::now(),
        ]);

        User::create([
            'name' => '一般ユーザー',
            'email' => 'user@example.com',
            'password' => bcrypt('userpass'),
            'email_verified_at' => Carbon::now(),
        ]);

        User::create([
            'name' => 'テストユーザー1',
            'email' => 'test1@example.com',
            'password' => bcrypt('password1'),
            'email_verified_at' => Carbon::now(),
        ]);

        User::create([
            'name' => 'テストユーザー2',
            'email' => 'test2@example.com',
            'password' => bcrypt('password2'),
            'email_verified_at' => Carbon::now(),
        ]);

        User::create([
            'name' => 'テストユーザー3',
            'email' => 'test3@example.com',
            'password' => bcrypt('password3'),
            'email_verified_at' => Carbon::now(),
        ]);
    }
}
