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
                'name' => 'Beef',
                'quantity' => 20 * 1000,
                'merchant_id' => 1,
            ],
            [
                'name' => 'Cheese',
                'quantity' => 5 * 1000,
                'merchant_id' => 2,
            ],
            [
                'name' => 'Onion',
                'quantity' => 1 * 1000,
                'merchant_id' => 3,
            ]
        ];

        if(DB::table('ingredients')->count() == 0){
            DB::table('ingredients')->insert($initialIngredientsData);
        }
    }
}
