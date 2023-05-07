<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IngredientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $initialIngredientsData = [
            [
                'id' => 1,
                'name' => 'Beef',
                'original_stock' => 20 * 1000,
                'current_stock' => 20 * 1000,
                'merchant_id' => 1,
                'is_merchant_notified' => false,
            ],
            [
                'id' => 2,
                'name' => 'Cheese',
                'original_stock' => 5 * 1000,
                'current_stock' => 5 * 1000,
                'merchant_id' => 2,
                'is_merchant_notified' => false,
            ],
            [
                'id' => 3,
                'name' => 'Onion',
                'original_stock' => 1 * 1000,
                'current_stock' => 1 * 1000,
                'merchant_id' => 3,
                'is_merchant_notified' => false,
            ]
        ];

        if(DB::table('ingredients')->count() == 0){
            DB::table('ingredients')->insert($initialIngredientsData);
        }
    }
}
