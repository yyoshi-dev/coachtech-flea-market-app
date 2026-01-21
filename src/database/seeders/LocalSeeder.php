<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LocalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => '検証ユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('test1234'),
            'postal_code' => '123-4567',
            'address' => 'テスト県テスト市 1-2-3',
            'building' => 'テストマンション101',
            'email_verified_at' => now(),
        ]);
    }
}
