<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Inertia\Inertia;

class OrdersController extends Controller
{
    public function index()
    {
        $orders = Order::with(['client', 'location', 'user'])
            ->latest()
            ->get();

        return Inertia::render('orders/Index', [
            'orders' => $orders,
        ]);
    }

    public function show(Order $order)
    {
        $order->load([
            'client',
            'location',
            'user',
            'products.components.ingredients', // для детализации калькуляции
        ]);

        return Inertia::render('orders/Show', [
            'order' => $order,
        ]);
    }
}
