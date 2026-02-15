<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Order;
use App\Models\OrderProduct;
use Inertia\Inertia;

class OrdersController extends Controller
{
    public function index()
    {
        $orders = Order::with(['client:id,name', 'location:id,name'])
            ->latest()
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'price' => (float) $order->price,
                    'size' => $order->size,
                    'approved' => (bool) $order->approved,
                    'date' => $order->date,
                    'client' => $order->client ? [
                        'id' => $order->client->id,
                        'name' => $order->client->name,
                    ] : null,
                    'location' => $order->location ? [
                        'id' => $order->location->id,
                        'name' => $order->location->name,
                    ] : null,
                ];
            });

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
            'products',
        ]);

        $orderProducts = \App\Models\OrderProduct::with(['components.component'])
            ->where('order_id', $order->id)
            ->get()
            ->keyBy('product_id');

        return Inertia::render('orders/Show', [
            'order' => [
                'id' => $order->id,
                'price' => (float) $order->price,
                'size' => $order->size,
                'approved' => (bool) $order->approved,
                'date' => $order->date,

                'client' => $order->client ? [
                    'id' => $order->client->id,
                    'name' => $order->client->name,
                ] : null,

                'location' => $order->location ? [
                    'id' => $order->location->id,
                    'name' => $order->location->name,
                    'price' => (float) $order->location->price,
                ] : null,

                'products' => $order->products->map(function ($product) use ($orderProducts) {

                    $orderProduct = $orderProducts[$product->id] ?? null;

                    return [
                        'id' => $product->id,
                        'name_en' => $product->name_en,

                        'pivot' => [
                            'price' => (float) $product->pivot->price,
                            'packaging_material_price' => (float) $product->pivot->packaging_material_price,
                            'production_price' => (float) $product->pivot->production_price,
                            'packaging_price' => (float) $product->pivot->packaging_price,
                            'transportation_price' => (float) $product->pivot->transportation_price,
                            'multi_delivery_price' => (float) $product->pivot->multi_delivery_price,
                            'sell_percent' => (float) $product->pivot->sell_percent,
                        ],

                        'components' => $orderProduct
                            ? $orderProduct->components->map(function ($c) {
                                return [
                                    'id' => $c->component_id,
                                    'name' => $c->component->name,
                                    'grams' => (int) $c->grams,
                                    'price_per_kg' => (float) $c->price_per_kg,
                                ];
                            })->values()
                            : [],
                    ];
                })->values(),
            ],
        ]);
    }

    public function destroy(Order $order)
    {
        $order->products()->detach();
        $order->delete();

        return redirect()->route('orders')->with('success', 'Order deleted');
    }

    public function pdf(Order $order)
    {
        $data = $this->buildOrderPdfData($order);

        return Pdf::loadView('pdf.order', $data)
            ->setPaper('a4')
            ->download("order-{$order->id}.pdf");
    }

    public function pdfPreview(Order $order)
    {
        $data = $this->buildOrderPdfData($order);

        return Pdf::loadView('pdf.order', $data)
            ->setPaper('a4')
            ->stream("order-{$order->id}.pdf");
    }

    /**
     * Собираем данные ТОЛЬКО из заказа:
     * order_products + order_product_components
     */
    private function buildOrderPdfData(Order $order): array
    {
        $order->load(['client', 'location']);

        $orderProducts = OrderProduct::query()
            ->with([
                'product:id,name_en',
                'components.component:id,name',
            ])
            ->where('order_id', $order->id)
            ->get();

        return [
            'order' => $order,
            'items' => $orderProducts->map(function (OrderProduct $op) {
                return [
                    'product_name' => $op->product?->name_en ?? ('#' . $op->product_id),
                    'price' => (float)$op->price,

                    'packaging_material_price' => (float)$op->packaging_material_price,
                    'production_price' => (float)$op->production_price,
                    'packaging_price' => (float)$op->packaging_price,
                    'transportation_price' => (float)$op->transportation_price,
                    'multi_delivery_price' => (float)$op->multi_delivery_price,
                    'sell_percent' => (float)$op->sell_percent,

                    'components' => $op->components->map(function ($c) {
                        return [
                            'name' => $c->component?->name ?? ('#' . $c->component_id),
                            'grams' => (float)$c->grams,
                            'price_per_kg' => (float)$c->price_per_kg,
                        ];
                    })->values(),
                ];
            })->values(),
        ];

    }

}
