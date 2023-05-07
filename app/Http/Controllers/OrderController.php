<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Services\OrderService;

class OrderController extends Controller
{
    public function __construct(public OrderService $orderService) {
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrderRequest $request)
    {
        $productsInOrder = $request->collect('products');

        $createdOrder = $this->orderService->create($productsInOrder);

        // we could make a general helper to unify the response structure but here we just return json for simplicity
        return response()->json([
            'message' => 'order created!',
            'data' => [
                'order' => $createdOrder,
            ],
        ]);
    }
}
