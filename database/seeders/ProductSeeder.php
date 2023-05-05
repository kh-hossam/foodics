<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $initialProductsData = [
            [
                'name' => 'Burger',
            ],
        ];

        if(DB::table('products')->count() == 0){
            foreach ($initialProductsData as $product) {
                $product = Product::create($product);
                $product->ingredients()->attach([
                    1 => ['quantity' => 150],
                    2 => ['quantity' => 30],
                    3 => ['quantity' => 20],
                ]);
            }
        }
    }
}
