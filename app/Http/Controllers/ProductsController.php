<?php

namespace App\Http\Controllers;
use App\Models\Component;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductsController extends Controller
{
    public function index()
    {
        $products = Product::with('components')->get();

        $columns = [
            ['field' => 'name_fi', 'header' => 'Name (FI)'],
            ['field' => 'name_ee', 'header' => 'Name (EE)'],
            ['field' => 'name_en', 'header' => 'Name (EN)'],
            ['field' => 'type', 'header' => 'Type'],
        ];

        return Inertia::render('products/Index', [
            'products' => $products,
            'columns' => $columns,
        ]);
    }

    public function create()
    {
        return Inertia::render('products/Create', [
            'components' => Component::select('id', 'name', 'type')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_fi' => 'nullable|string|max:255',
            'name_ee' => 'nullable|string|max:255',
            'type' => 'required|in:base,vegan,vegetarian',
            'items' => 'required|array|min:1',
            'items.*.component_id' => 'required|exists:components,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
        ]);

        $product = Product::create($request->only(['name_fi', 'name_ee', 'name_en', 'type']));

        $product->components()->sync(
            collect($data['items'])->values()->mapWithKeys(fn ($item, $index) => [
                $item['component_id'] => [
                    'quantity'   => $item['quantity'],
                    'sort_order' => $index,
                ],
            ])->toArray()
        );

        return redirect()->route('products')->with('success', 'Product created');
    }

    public function edit(Product $product)
    {
        return Inertia::render('products/Edit', [
            'product' => $product->load('components'),
            'components' => Component::select('id', 'name', 'type')->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_fi' => 'nullable|string|max:255',
            'name_ee' => 'nullable|string|max:255',
            'type' => 'required|in:base,vegan,vegetarian',
            'items' => 'required|array|min:1',
            'items.*.component_id' => 'required|exists:components,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
        ]);

        $product->update($request->only(['name_fi', 'name_ee', 'name_en', 'type']));

        $product->components()->sync(
            collect($data['items'])->values()->mapWithKeys(fn ($item, $index) => [
                $item['component_id'] => [
                    'quantity'   => $item['quantity'],
                    'sort_order' => $index,
                ],
            ])->toArray()
        );

        return redirect()->route('products')->with('success', 'Product updated');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products')->with('success', 'Product deleted');
    }
}
