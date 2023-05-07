<?php

use App\Mail\LowIngredientStock;
use Tests\TestCase;
use Illuminate\Http\Response;
use App\Models\Product;
use App\Models\Ingredient;
use App\Models\Merchant;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Mail;

class OrderTest extends TestCase
{
    use LazilyRefreshDatabase;

    /** @test */
    public function it_can_create_an_order_for_multiple_products_and_ingredients()
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();
        $ingredient1 = Ingredient::factory()->create(['current_stock' => 100]);
        $ingredient2 = Ingredient::factory()->create(['current_stock' => 200]);
        $ingredient3 = Ingredient::factory()->create(['current_stock' => 50]);
        $product1->ingredients()->attach([
            $ingredient1->id => ['quantity' => 20],
            $ingredient2->id => ['quantity' => 10],
        ]);
        $product2->ingredients()->attach([
            $ingredient2->id => ['quantity' => 100],
            $ingredient3->id => ['quantity' => 30],
        ]);

        $orderData = [
            'products' => [
                ['product_id' => $product1->id, 'quantity' => 2],
                ['product_id' => $product2->id, 'quantity' => 1],
            ],
        ];

        $response = $this->json('POST', '/api/orders', $orderData);

        $response->assertJson([
            'message' => 'order created!',
        ])->assertStatus(Response::HTTP_OK);
        $order = $response->json('data.order');

        $this->assertNotNull($order);
        $this->assertEquals(2, $order['products'][0]['pivot']['quantity']);
        $this->assertEquals(1, $order['products'][1]['pivot']['quantity']);
        $this->assertEquals($ingredient1->current_stock - 2 * $product1->ingredients->find($ingredient1->id)->pivot->quantity, $ingredient1->fresh()->current_stock);
        $this->assertEquals($ingredient2->current_stock - (2 * $product1->ingredients->find($ingredient2->id)->pivot->quantity) - $product2->ingredients->find($ingredient2->id)->pivot->quantity, $ingredient2->fresh()->current_stock);
        $this->assertEquals($ingredient3->current_stock - $product2->ingredients->find($ingredient3->id)->pivot->quantity, $ingredient3->fresh()->current_stock);
    }


    /** @test */
    public function it_returns_an_error_when_product_id_is_missing()
    {
        $orderData = [
            'products' => [
                ['quantity' => 2],
            ],
        ];
        $response = $this->json('POST', '/api/orders', $orderData);
        $response->assertJsonValidationErrors('products.0.product_id');
    }

    /** @test */
    public function it_returns_an_error_when_product_id_is_invalid()
    {
        $orderData = [
            'products' => [
                ['product_id' => 999, 'quantity' => 2],
            ],
        ];
        $response = $this->json('POST', '/api/orders', $orderData);
        $response->assertJsonValidationErrors('products.0.product_id');
    }

    /** @test */
    public function it_returns_an_error_when_quantity_is_missing()
    {
        $product1 = Product::factory()->create();
        $orderData = [
            'products' => [
                ['product_id' => $product1->id],
            ],
        ];
        $response = $this->json('POST', '/api/orders', $orderData);
        $response->assertJsonValidationErrors('products.0.quantity');
    }

    /** @test */
    public function it_returns_an_error_when_quantity_is_negative()
    {
        $product1 = Product::factory()->create();
        $orderData = [
            'products' => [
                ['product_id' => $product1->id, 'quantity' => -1],
            ],
        ];
        $response = $this->json('POST', '/api/orders', $orderData);
        $response->assertJsonValidationErrors('products.0.quantity');
    }

    /** @test */
    public function it_returns_an_error_when_quantity_is_zero()
    {
        $product1 = Product::factory()->create();
        $orderData = [
            'products' => [
                ['product_id' => $product1->id, 'quantity' => 0],
            ],
        ];
        $response = $this->json('POST', '/api/orders', $orderData);
        $response->assertJsonValidationErrors('products.0.quantity');
    }

    /** @test */
    public function it_returns_an_error_when_quantity_is_greater_than_maximum()
    {
        $product1 = Product::factory()->create();
        $orderData = [
            'products' => [
                ['product_id' => $product1->id, 'quantity' => 1000001],
            ],
        ];
        $response = $this->json('POST', '/api/orders', $orderData);
        $response->assertJsonValidationErrors('products.0.quantity');
    }

    /** @test */
    public function it_returns_an_error_when_ingredient_stock_is_insufficient()
    {
        $product1 = Product::factory()->create();
        $ingredient1 = Ingredient::factory()->create(['current_stock' => 10]);
        $product1->ingredients()->attach([$ingredient1->id => ['quantity' => 10]]);
        $orderData = [
            'products' => [
                ['product_id' => $product1->id, 'quantity' => 2],
            ],
        ];
        $response = $this->json('POST', '/api/orders', $orderData);
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJsonFragment(["Not enough stock for ingredient $ingredient1->name left to make your order"]);
    }


    /** @test */
    public function it_sends_email_when_product_ingredient_falls_below_50_percent_and_only_send_once()
    {
        Mail::fake();

        $merchant = Merchant::factory()->create([
            'email' => 'test@merchant.com'
        ]);

        // Create a test product with a low-quantity ingredient
        $lowStockIngredient = Ingredient::factory()->create([
            'current_stock' => 20,
            'original_stock' => 50,
            'merchant_id' => $merchant->id,
            'is_merchant_notified' => false,
        ]);
        $product = Product::factory()->create();
        $product->ingredients()->attach($lowStockIngredient->id, ['quantity' => 5]);

        $response = $this->json('POST', '/api/orders', [
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1
                ]
            ],
        ]);

        $response2 = $this->json('POST', '/api/orders', [
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1
                ]
            ],
        ]);

        $response->assertSuccessful();
        $response2->assertSuccessful();

        Mail::assertQueued(LowIngredientStock::class, 1);
        Mail::assertQueued(LowIngredientStock::class, function ($mail) use ($lowStockIngredient) {
            return $mail->ingredient->id === $lowStockIngredient->id;
        });
    }


    /** @test */
    public function it_does_not_send_email_when_product_ingredient_is_above_50_percent()
    {
        Mail::fake();

        $merchant = Merchant::factory()->create([
            'email' => 'test@example.com'
        ]);

        // Create a test product with a high-quantity ingredient
        $highStockIngredient = Ingredient::factory()->create([
            'current_stock' => 40,
            'original_stock' => 50,
            'merchant_id' => $merchant->id
        ]);
        $product = Product::factory()->create();
        $product->ingredients()->attach($highStockIngredient->id, ['quantity' => 5]);

        // Make a POST request to the create order API endpoint
        $response = $this->json('POST', '/api/orders', [
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1
                ]
            ],
        ]);

        $response->assertSuccessful();

        // Assert that no email was sent
        Mail::assertNothingQueued();
    }
}
