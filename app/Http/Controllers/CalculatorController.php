<?php

namespace App\Http\Controllers;

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
                    $pricePerKg = $component->ingredients->reduce(function ($sum, $ingredient) {
                        return $sum + ($ingredient->kg_price * $ingredient->pivot->quantity);
                    }, 0);
                    return [
                        'id' => $component->id,
                        'name' => $component->name,
                        'grams' => (int) ($component->pivot->quantity ?? 500), // дефолт для калькулятора
                        'price_per_kg' => round($pricePerKg, 2),
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
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.sell_percent' => 'required|numeric|min:0',
        ]);

        $order = Order::create([
            'client_id' => $data['client_id'],
            'location_id' => $data['location_id'],
            'user_id' => auth()->id(),
            'size' => $data['size'],
            'price' => 0,
            'approved' => false,
            'date' => now(),
        ]);

        $total = 0;

        foreach ($data['items'] as $item) {
            $product = Product::with('components.ingredients')->find($item['product_id']);

            $baseCost = $this->calculateProductCost($product);

            $sellPrice = $baseCost + ($baseCost * $item['sell_percent'] / 100);

            $order->products()->attach($product->id, [
                'price' => $sellPrice,
                'packaging_material_price' => 0,
                'production_price' => 0,
                'packaging_price' => 0,
                'transportation_price' => 0,
                'multi_delivery_price' => 0,
                'selling_percent' => $item['sell_percent'],
            ]);

            $total += $sellPrice;
        }

        $order->update(['price' => $total]);

        return redirect()->route('orders')->with('success', 'Order calculated');
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
}

