<?php

namespace App\Services;

use App\Mail\LowIngredientStock;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class OrderService
{
    public function create(Collection $productsInOrder)
    {
        $products = Product::query()
            ->whereIn('id', $productsInOrder->pluck('product_id'))
            ->with('ingredients')
            ->get();

        $createdOrder = DB::transaction(function () use ($productsInOrder, $products) {
            $order = Order::create();

            $productsInOrderFormatted = $productsInOrder->mapWithKeys(fn ($product) => [$product['product_id']  => ['quantity' => $product['quantity']]]);

            $order->products()->attach($productsInOrderFormatted->toArray());

            $this->updateStock($productsInOrder, $products);

            return $order;
        });

        return $createdOrder->load('products');
    }

    private function updateStock($productsInOrder, $products)
    {
        foreach ($productsInOrder as $orderProduct) {
            // here we getting the product from the already existing products collection that we fetched before, not from database
            $product = $products->where('id', $orderProduct['product_id'])->first();

            // loop through the product ingredients to update the stock
            foreach ($product->ingredients as $ingredient) {
                $ingredientRequiredQuantity = $orderProduct['quantity'] * $ingredient->pivot->quantity;

                if ($ingredient->current_stock < $ingredientRequiredQuantity) {
                    throw new HttpResponseException(
                        response()->json(['message' => "Not enough stock for ingredient {$ingredient->name} left to make your order"], Response::HTTP_BAD_REQUEST)
                    );
                }

                $ingredient->decrement('current_stock', $ingredientRequiredQuantity);

                $this->checkIfIngredientBelow50Percent($ingredient);
            }
        }
    }


    private function checkIfIngredientBelow50Percent(Ingredient $ingredient){
        if(
            $ingredient->current_stock < ($ingredient->original_stock * .5) &&
            !($ingredient->is_merchant_notified)
        ) {
            $ingredient->load('merchant');
            Mail::to($ingredient->merchant->email)->send(new LowIngredientStock($ingredient));

            $ingredient->updateQuietly(['is_merchant_notified' => true]);
        }
    }
}
