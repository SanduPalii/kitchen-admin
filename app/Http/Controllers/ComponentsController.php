<?php

namespace App\Http\Controllers;

use App\Models\Component;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ComponentsController extends Controller
{
    public function index()
    {

        $components = Component::with('ingredients')->get()->map(function ($component) {

            $cost = $component->ingredients->sum(function ($ing) {
                return round((float) $ing->kg_price * (float) $ing->pivot->quantity, 2);
            });

            $costPerKg = $component->quantity > 0
                ? $cost / (float) $component->quantity
                : 0;

            return [
                'id'          => $component->id,
                'name'        => $component->name,
                'type'        => $component->type,
                'quantity'    => $component->quantity,
                'cost'        => number_format($cost, 2, ',', ''),
                'cost_per_kg' => number_format($costPerKg, 2, ',', ''),
            ];
        });

        $columns = [
            ['field' => 'name', 'header' => 'Name'],
            ['field' => 'type', 'header' => 'Type'],
            ['field' => 'quantity', 'header' => 'Quantity'],
            ['field' => 'cost', 'header' => 'Cost'],
            ['field' => 'cost_per_kg', 'header' => 'Cost per kg'],
        ];

        return Inertia::render('сomponents/Index', [
            'components' => $components,
            'columns' => $columns,
        ]);
    }



    public function create()
    {
        return Inertia::render('сomponents/Create', [
            'ingredients' => Ingredient::select('id', 'name', 'unit', 'kg_price')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:gravy,protein,side_dish',
            'quantity' => 'required|integer|min:1',
            'items' => 'required|array|min:1',
            'items.*.ingredient_id' => 'required|exists:ingredients,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
        ]);

        $component = Component::create([
            'name' => $data['name'],
            'type' => $data['type'],
            'quantity' => $data['quantity'],
        ]);

        // sync ингредиенты с quantity
        $component->ingredients()->sync(
            collect($data['items'])->mapWithKeys(fn ($item) => [
                $item['ingredient_id'] => ['quantity' => $item['quantity']],
            ])->toArray()
        );

        return redirect()->route('components')
            ->with('success', 'Component created');
    }

    public function edit(Component $component)
    {
        return Inertia::render('сomponents/Edit', [
            'component' => $component->load('ingredients'),
            'ingredients' => Ingredient::select('id', 'name', 'unit', 'kg_price')->get(),
        ]);
    }

    public function update(Request $request, Component $component)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:gravy,protein,side_dish',
            'quantity' => 'required|integer|min:1',
            'items' => 'required|array|min:1',
            'items.*.ingredient_id' => 'required|exists:ingredients,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
        ]);

        $component->update([
            'name' => $data['name'],
            'type' => $data['type'],
            'quantity' => $data['quantity'],
        ]);

        $component->ingredients()->sync(
            collect($data['items'])->mapWithKeys(fn ($item) => [
                $item['ingredient_id'] => ['quantity' => $item['quantity']],
            ])->toArray()
        );

        return redirect()->route('components')
            ->with('success', 'Component updated');
    }

    public function destroy(Component $component)
    {
        $component->delete();

        return redirect()->route('components')
            ->with('success', 'Component deleted');
    }
}
