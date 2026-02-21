<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Order;
use App\Models\Product;
use App\Models\Client;
use App\Models\Location;
use App\Models\OrderProduct;

class CalculatorController extends Controller
{
    public function index()
    {
        $products = Product::with('components.ingredients')->get()->map(function ($product) {

            return [
                'id' => $product->id,
                'name' => $product->name_en,
                'price_per_kg' => $product->price_per_kg ?? 0, // если есть поле
                'components' => $product->components->map(function ($component) {

                    // Себестоимость компонента (на 1 кг)
                    $batchCost = $component->ingredients->reduce(function ($sum, $ingredient) {
                        return $sum + round((float)$ingredient->kg_price * (float)$ingredient->pivot->quantity, 2);
                    }, 0);

                    $costPerKg = $component->quantity > 0
                        ? $batchCost / (float)$component->quantity
                        : 0;

                    return [
                        'id' => $component->id,
                        'name' => $component->name,
                        'grams' => (int) ($component->pivot->quantity ?? 500), // дефолт для калькулятора
                        'price_per_kg' => round($costPerKg, 2),
                    ];
                })->values(),
            ];
        });

        return Inertia::render('Calculator', [
            'products' => $products,
            'clients' => Client::select('id', 'name')->get(),
            'locations' => Location::select('id', 'name', 'price')->get(),
        ]);
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'location_id' => 'required|exists:locations,id',
            'size' => 'required|integer|min:1',
            'commission_pct' => 'required|numeric|min:0|max:100',

            'packaging_material' => 'required|numeric|min:0',
            'production' => 'required|numeric|min:0',
            'packaging' => 'required|numeric|min:0',
            'transportation' => 'required|numeric|min:0',
            'multi_delivery' => 'required|numeric|min:0',
            'sell_percent' => 'required|numeric|min:0',

            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.final_price' => 'required|numeric|min:0',
            'items.*.portion_grams' => 'required|integer|min:1',
            'items.*.units_per_box' => 'required|integer|min:1',

            'items.*.components' => 'required|array|min:1',
            'items.*.components.*.component_id' => 'required|exists:components,id',
            'items.*.components.*.grams' => 'required|integer|min:0',
            'items.*.components.*.price_per_kg' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($data) {

            $order = Order::create([
                'client_id'          => $data['client_id'],
                'location_id'        => $data['location_id'],
                'size'               => $data['size'],
                'user_id'            => auth()->id(),
                'price'              => 0,
                'approved'           => false,
                'date'               => now(),
                'commission_pct'     => $data['commission_pct'],
                'packaging_material' => $data['packaging_material'],
                'production'         => $data['production'],
                'packaging'          => $data['packaging'],
                'transportation'     => $data['transportation'],
                'multi_delivery'     => $data['multi_delivery'],
                'sell_percent'       => $data['sell_percent'],
            ]);

            $total = 0;

            foreach ($data['items'] as $item) {

                $orderProduct = OrderProduct::create([
                    'order_id'    => $order->id,
                    'product_id'  => $item['product_id'],
                    'price'       => $item['final_price'],
                    'portion_grams' => $item['portion_grams'],
                    'units_per_box' => $item['units_per_box'],
                ]);

                foreach ($item['components'] as $component) {
                    \App\Models\OrderProductComponent::create([
                        'order_product_id' => $orderProduct->id,
                        'component_id'     => $component['component_id'],
                        'grams'            => $component['grams'],
                        'price_per_kg'     => $component['price_per_kg'],
                    ]);
                }

                $total += $item['final_price'];
            }

            $order->update(['price' => $total]);

            return redirect()->route('orders')->with('success', 'Order saved');
        });
    }


    public function duplicate(Order $order)
    {
        return DB::transaction(function () use ($order) {

            $orderProducts = OrderProduct::with(['components'])
                ->where('order_id', $order->id)
                ->get();

            $newOrder = Order::create([
                'client_id'          => $order->client_id,
                'location_id'        => $order->location_id,
                'size'               => $order->size,
                'user_id'            => auth()->id(),
                'price'              => $order->price,
                'approved'           => false,
                'date'               => now(),
                'commission_pct'     => $order->commission_pct,
                'packaging_material' => $order->packaging_material,
                'production'         => $order->production,
                'packaging'          => $order->packaging,
                'transportation'     => $order->transportation,
                'multi_delivery'     => $order->multi_delivery,
                'sell_percent'       => $order->sell_percent,
            ]);

            foreach ($orderProducts as $op) {
                $newOp = OrderProduct::create([
                    'order_id'      => $newOrder->id,
                    'product_id'    => $op->product_id,
                    'price'         => $op->price,
                    'portion_grams' => $op->portion_grams,
                    'units_per_box' => $op->units_per_box,
                ]);

                foreach ($op->components as $c) {
                    \App\Models\OrderProductComponent::create([
                        'order_product_id' => $newOp->id,
                        'component_id'     => $c->component_id,
                        'grams'            => $c->grams,
                        'price_per_kg'     => $c->price_per_kg,
                    ]);
                }
            }

            return redirect()->route('orders')->with('success', "Order #{$order->id} duplicated as #{$newOrder->id}");
        });
    }

    private function calculateProductCost(Product $product): float
    {
        $sum = 0;

        foreach ($product->components as $component) {
            foreach ($component->ingredients as $ingredient) {
                $sum += $ingredient->kg_price * $ingredient->pivot->quantity;
            }
        }

        return $sum;
    }

    public function edit(Order $order)
    {
        if ($order->approved) {
            return redirect()->route('orders')->with('error', 'Approved orders cannot be edited.');
        }

        $orderProducts = \App\Models\OrderProduct::with(['components.component'])
            ->where('order_id', $order->id)
            ->get();

        return Inertia::render('calculator/Edit', [
            'order' => [
                'id'                 => $order->id,
                'client_id'          => $order->client_id,
                'location_id'        => $order->location_id,
                'size'               => $order->size,
                'commission_pct'     => (float) $order->commission_pct,
                'packaging_material' => (float) $order->packaging_material,
                'production'         => (float) $order->production,
                'packaging'          => (float) $order->packaging,
                'transportation'     => (float) $order->transportation,
                'multi_delivery'     => (float) $order->multi_delivery,
                'sell_percent'       => (float) $order->sell_percent,
                'items' => $orderProducts->map(function ($op) {
                    return [
                        'product_id'    => $op->product_id,
                        'final_price'   => (float) $op->price,
                        'portion_grams' => (int) ($op->portion_grams ?? 100),
                        'units_per_box' => (int) ($op->units_per_box ?? 1),
                        'components'    => $op->components->map(fn ($c) => [
                            'component_id' => $c->component_id,
                            'name'         => $c->component->name,
                            'grams'        => (int) $c->grams,
                            'price_per_kg' => (float) $c->price_per_kg,
                        ])->values(),
                    ];
                })->values(),
            ],

            'products' => Product::with('components')->get()->map(fn ($p) => [
                'id'   => $p->id,
                'name' => $p->name_en,
                'components' => $p->components->map(fn ($c) => [
                    'id'           => $c->id,
                    'name'         => $c->name,
                    'grams'        => (int) ($c->pivot->quantity ?? 500),
                    'price_per_kg' => 0,
                ])->values(),
            ]),
            'clients' => Client::select('id', 'name')->get(),
            'locations' => Location::select('id', 'name', 'price')->get(),
        ]);
    }

    public function update(Request $request, Order $order)
    {
        if ($order->approved) {
            return redirect()->route('orders')->with('error', 'Approved orders cannot be edited.');
        }

        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'location_id' => 'required|exists:locations,id',
            'size' => 'required|integer|min:1',
            'commission_pct' => 'required|numeric|min:0|max:100',

            'packaging_material' => 'required|numeric|min:0',
            'production' => 'required|numeric|min:0',
            'packaging' => 'required|numeric|min:0',
            'transportation' => 'required|numeric|min:0',
            'multi_delivery' => 'required|numeric|min:0',
            'sell_percent' => 'required|numeric|min:0',

            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.final_price' => 'required|numeric|min:0',
            'items.*.portion_grams' => 'required|integer|min:1',
            'items.*.units_per_box' => 'required|integer|min:1',

            'items.*.components' => 'required|array|min:1',
            'items.*.components.*.component_id' => 'required|exists:components,id',
            'items.*.components.*.grams' => 'required|integer|min:0',
            'items.*.components.*.price_per_kg' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($order, $data) {

            $order->update([
                'client_id'          => $data['client_id'],
                'location_id'        => $data['location_id'],
                'size'               => $data['size'],
                'commission_pct'     => $data['commission_pct'],
                'packaging_material' => $data['packaging_material'],
                'production'         => $data['production'],
                'packaging'          => $data['packaging'],
                'transportation'     => $data['transportation'],
                'multi_delivery'     => $data['multi_delivery'],
                'sell_percent'       => $data['sell_percent'],
            ]);

            // Delete old order products (cascade deletes components)
            $order->products()->detach();

            $total = 0;

            foreach ($data['items'] as $item) {

                $orderProduct = OrderProduct::create([
                    'order_id'      => $order->id,
                    'product_id'    => $item['product_id'],
                    'price'         => $item['final_price'],
                    'portion_grams' => $item['portion_grams'],
                    'units_per_box' => $item['units_per_box'],
                ]);

                foreach ($item['components'] as $c) {
                    $orderProduct->components()->create([
                        'component_id' => $c['component_id'],
                        'grams'        => $c['grams'],
                        'price_per_kg' => $c['price_per_kg'],
                    ]);
                }

                $total += $item['final_price'];
            }

            $order->update(['price' => $total]);

            return redirect()->route('orders')->with('success', 'Order updated');
        });

    }

}

