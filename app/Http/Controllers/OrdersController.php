<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Order;
use App\Models\OrderProduct;
use Illuminate\Http\Request;
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
                    'commission_pct' => (float) $order->commission_pct,
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
                'id'                 => $order->id,
                'price'              => (float) $order->price,
                'size'               => $order->size,
                'approved'           => (bool) $order->approved,
                'date'               => $order->date,
                'packaging_material' => (float) $order->packaging_material,
                'production'         => (float) $order->production,
                'packaging'          => (float) $order->packaging,
                'transportation'     => (float) $order->transportation,
                'multi_delivery'     => (float) $order->multi_delivery,
                'sell_percent'       => (float) $order->sell_percent,

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

    public function updateCommission(Request $request, Order $order)
    {
        $data = $request->validate([
            'commission_pct' => 'required|numeric|min:0|max:100',
        ]);

        $order->update(['commission_pct' => $data['commission_pct']]);

        return back()->with('success', 'Commission updated');
    }

    public function approve(Order $order)
    {
        if ($order->approved) {
            return back()->with('error', 'Order is already approved.');
        }

        $order->update(['approved' => true]);

        return back()->with('success', "Order #{$order->id} approved.");
    }

    public function destroy(Order $order)
    {
        if ($order->approved) {
            return back()->with('error', 'Approved orders cannot be deleted.');
        }

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

    public function pricingPdf(Order $order)
    {
        $data = $this->buildPricingPdfData($order);

        return Pdf::loadView('pdf.pricing', $data)
            ->setPaper('a4')
            ->download("pricing-{$order->id}.pdf");
    }

    public function pricingPdfPreview(Order $order)
    {
        $data = $this->buildPricingPdfData($order);

        return Pdf::loadView('pdf.pricing', $data)
            ->setPaper('a4')
            ->stream("pricing-{$order->id}.pdf");
    }

    public function pricingInternal(Order $order)
    {
        $order->load(['client', 'location']);

        $orderProducts = OrderProduct::with(['product:id,name_en,type', 'components'])
            ->where('order_id', $order->id)
            ->get();

        // Pass raw food cost per kg so the frontend can recompute when costs change
        $items = $orderProducts->map(function (OrderProduct $op) {
            $portionGrams = (int) ($op->portion_grams ?? 320);

            $foodCostPerKg = $op->components->sum(fn ($c) =>
                (float) $c->price_per_kg * (float) $c->grams / 1000
            );

            $rawName   = $op->product?->name_en ?? ('#' . $op->product_id);
            $cleanName = trim(preg_replace('/\s*\((vegan|vegetarian)\)/i', '', $rawName));

            return [
                'name'             => $cleanName,
                'type'             => $op->product?->type ?? 'base',
                'portion_grams'    => $portionGrams,
                'food_cost_per_kg' => round($foodCostPerKg, 2),
            ];
        })->values();

        return Inertia::render('orders/PricingInternal', [
            'order' => [
                'id'                 => $order->id,
                'date'               => $order->date,
                'client_name'        => $order->client?->name ?? '—',
                'commission_pct'     => (float) $order->commission_pct,
                'packaging_material' => (float) $order->packaging_material,
                'production'         => (float) $order->production,
                'packaging'          => (float) $order->packaging,
                'transportation'     => (float) $order->transportation,
                'multi_delivery'     => (float) $order->multi_delivery,
                'sell_percent'       => (float) $order->sell_percent,
            ],
            'items' => $items,
        ]);
    }

    public function updateSettings(Request $request, Order $order)
    {
        $data = $request->validate([
            'commission_pct'     => 'required|numeric|min:0|max:100',
            'packaging_material' => 'required|numeric|min:0',
            'production'         => 'required|numeric|min:0',
            'packaging'          => 'required|numeric|min:0',
            'transportation'     => 'required|numeric|min:0',
            'multi_delivery'     => 'required|numeric|min:0',
            'sell_percent'       => 'required|numeric|min:0',
        ]);

        $order->update($data);

        return back()->with('success', 'Settings updated');
    }

    private function buildPricingPdfData(Order $order): array
    {
        $order->load(['client', 'location']);

        $orderProducts = OrderProduct::with(['product:id,name_en,type'])
            ->where('order_id', $order->id)
            ->get();

        $dateFormatted = \Carbon\Carbon::parse($order->date)->format('M-y');

        $logoPath = public_path('images/logo.png');
        $logo = null;
        if (file_exists($logoPath)) {
            $logo = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $items = $orderProducts->map(function (OrderProduct $op) {
            $pricePerKg   = (float) $op->price;
            $portionGrams = (int) ($op->portion_grams ?? 320);
            $unitsPerBox  = (int) ($op->units_per_box ?? 8);

            $pricePerPiece = round($pricePerKg * $portionGrams / 1000, 2);
            $pricePerBox   = round($pricePerPiece * $unitsPerBox, 2);

            $rawName = $op->product?->name_en ?? ('#' . $op->product_id);
            // Strip any existing inline tags so we don't duplicate the badge
            $cleanName = trim(preg_replace('/\s*\((vegan|vegetarian)\)/i', '', $rawName));

            $type = $op->product?->type ?? 'base'; // 'base' | 'vegan' | 'vegetarian'

            return [
                'name'          => $cleanName,
                'type'          => $type,
                'portion_grams' => $portionGrams,
                'units_per_box' => $unitsPerBox,
                'price_per_piece' => $pricePerPiece,
                'price_per_box'   => $pricePerBox,
            ];
        })->values();

        // Determine title suffix: show grams only when all products share the same portion size
        $allGrams    = $items->pluck('portion_grams')->unique();
        $gramsSuffix = $allGrams->count() === 1 ? ' ' . $allGrams->first() . 'g' : '';

        $allBoxSizes = $items->pluck('units_per_box')->unique();
        $boxSize     = $allBoxSizes->count() === 1 ? $allBoxSizes->first() : 8;

        return [
            'order'          => $order,
            'client_name'    => $order->client?->name ?? '—',
            'grams_suffix'   => $gramsSuffix,
            'box_size'       => $boxSize,
            'date_formatted' => $dateFormatted,
            'logo'           => $logo,
            'items'          => $items,
        ];
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
