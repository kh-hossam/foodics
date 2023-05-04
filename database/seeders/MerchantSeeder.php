<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MerchantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $initialIngredientsData = [
            [
                'id' => 1,
                'name' => 'Beef merchant',
                'email' => 'beef@merchant.com',
            ],
            [
                'id' => 2,
                'name' => 'Cheese merchant',
                'email' => 'cheese@merchant.com',
            ],
            [
                'id' => 3,
                'name' => 'Onion merchant',
                'email' => 'onion@merchant.com',
            ]
        ];

        if(DB::table('merchants')->count() == 0){
            DB::table('merchants')->insert($initialIngredientsData);
        }
    }
}
