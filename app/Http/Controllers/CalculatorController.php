<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Order;
use App\Models\Product;
use App\Models\Client;
use App\Models\Location;

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
                        return $sum + ((float)$ingredient->kg_price * (float)$ingredient->pivot->quantity);
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

            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.final_price' => 'required|numeric|min:0',

            'items.*.packaging_material' => 'required|numeric|min:0',
            'items.*.production' => 'required|numeric|min:0',
            'items.*.packaging' => 'required|numeric|min:0',
            'items.*.transportation' => 'required|numeric|min:0',
            'items.*.multi_delivery' => 'required|numeric|min:0',
            'items.*.sell_percent' => 'required|numeric|min:0',

            'items.*.components' => 'required|array|min:1',
            'items.*.components.*.component_id' => 'required|exists:components,id',
            'items.*.components.*.grams' => 'required|integer|min:0',
            'items.*.components.*.price_per_kg' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($data) {

            $order = Order::create([
                'client_id' => $data['client_id'],
                'location_id' => $data['location_id'],
                'size' => $data['size'],
                'user_id' => auth()->id(),
                'price' => 0,
                'approved' => false,
                'date' => now(),
            ]);

            $total = 0;

            foreach ($data['items'] as $item) {

                $order->products()->attach($item['product_id'], [
                    'price' => $item['final_price'],
                    'packaging_material_price' => $item['packaging_material'],
                    'production_price' => $item['production'],
                    'packaging_price' => $item['packaging'],
                    'transportation_price' => $item['transportation'],
                    'multi_delivery_price' => $item['multi_delivery'],
                    'sell_percent' => $item['sell_percent'],
                ]);

                $orderProduct = DB::table('order_products')
                    ->where('order_id', $order->id)
                    ->where('product_id', $item['product_id'])
                    ->first();

                foreach ($item['components'] as $component) {
                    \App\Models\OrderProductComponent::create([
                        'order_product_id' => $orderProduct->id,
                        'component_id' => $component['component_id'],
                        'grams' => $component['grams'],
                        'price_per_kg' => $component['price_per_kg'],
                    ]);
                }

                $total += $item['final_price'];
            }

            $order->update(['price' => $total]);

            return redirect()->route('orders')->with('success', 'Order saved');
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
        $order->load([
            'products',
        ]);

        $orderProducts = \App\Models\OrderProduct::with(['components.component'])
            ->where('order_id', $order->id)
            ->get()
            ->keyBy('product_id');

        return Inertia::render('calculator/Edit', [
            'order' => [
                'id' => $order->id,
                'client_id' => $order->client_id,
                'location_id' => $order->location_id,
                'size' => $order->size,
                'items' => $order->products->map(function ($product) use ($orderProducts) {
                    $orderProduct = $orderProducts[$product->id];

                    return [
                        'product_id' => $product->id,
                        'final_price' => (float) $product->pivot->price,
                        'packaging_material' => (float) $product->pivot->packaging_material_price,
                        'production' => (float) $product->pivot->production_price,
                        'packaging' => (float) $product->pivot->packaging_price,
                        'transportation' => (float) $product->pivot->transportation_price,
                        'multi_delivery' => (float) $product->pivot->multi_delivery_price,
                        'sell_percent' => (float) $product->pivot->sell_percent,
                        'components' => $orderProduct->components->map(fn ($c) => [
                            'component_id' => $c->component_id,
                            'name' => $c->component->name,
                            'grams' => (int) $c->grams,
                            'price_per_kg' => (float) $c->price_per_kg,
                        ])->values(),
                    ];
                })->values(),
            ],

            'products' => Product::with('components')->get(),
            'clients' => Client::select('id', 'name')->get(),
            'locations' => Location::select('id', 'name', 'price')->get(),
        ]);
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'location_id' => 'required|exists:locations,id',
            'size' => 'required|integer|min:1',

            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.final_price' => 'required|numeric|min:0',

            'items.*.packaging_material' => 'required|numeric|min:0',
            'items.*.production' => 'required|numeric|min:0',
            'items.*.packaging' => 'required|numeric|min:0',
            'items.*.transportation' => 'required|numeric|min:0',
            'items.*.multi_delivery' => 'required|numeric|min:0',
            'items.*.sell_percent' => 'required|numeric|min:0',

            'items.*.components' => 'required|array|min:1',
            'items.*.components.*.component_id' => 'required|exists:components,id',
            'items.*.components.*.grams' => 'required|integer|min:0',
            'items.*.components.*.price_per_kg' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($order, $data) {

            $order->update([
                'client_id' => $data['client_id'],
                'location_id' => $data['location_id'],
                'size' => $data['size'],
            ]);

            DB::table('order_product_components')
                ->whereIn('order_product_id', function ($q) use ($order) {
                    $q->select('id')->from('order_products')->where('order_id', $order->id);
                })->delete();

            $order->products()->detach();

            $total = 0;

            foreach ($request->items as $item) {
                $orderProduct = OrderProduct::where('order_id', $order->id)
                    ->where('product_id', $item['product_id'])
                    ->firstOrFail();

                $orderProduct->update([
                    'price' => $item['final_price'],
                    'packaging_material_price' => $item['packaging_material'],
                    'production_price' => $item['production'],
                    'packaging_price' => $item['packaging'],
                    'transportation_price' => $item['transportation'],
                    'multi_delivery_price' => $item['multi_delivery'],
                    'sell_percent' => $item['sell_percent'],
                ]);

                $orderProduct->components()->delete();

                foreach ($item['components'] as $c) {
                    $orderProduct->components()->create([
                        'component_id' => $c['component_id'],
                        'grams' => $c['grams'],
                        'price_per_kg' => $c['price_per_kg'],
                    ]);
                }
            }


            $order->update(['price' => $total]);

            return redirect()->route('orders')->with('success', 'Order updated');
        });
    }

}

