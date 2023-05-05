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
                'stock' => 20 * 1000,
                'merchant_id' => 1,
            ],
            [
                'id' => 2,
                'name' => 'Cheese',
                'stock' => 5 * 1000,
                'merchant_id' => 2,
            ],
            [
                'id' => 3,
                'name' => 'Onion',
                'stock' => 1 * 1000,
                'merchant_id' => 3,
            ]
        ];

        if(DB::table('ingredients')->count() == 0){
            DB::table('ingredients')->insert($initialIngredientsData);
        }
    }
}
