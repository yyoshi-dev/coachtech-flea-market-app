<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('payment_methods')->insert([
            ['id' => 1, 'name' => 'コンビニ支払い', 'stripe_type' => 'konbini'],
            ['id' => 2, 'name' => 'カード支払い', 'stripe_type' => 'card'],
        ]);
    }
}
