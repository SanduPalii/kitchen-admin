<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ingredient;
use Inertia\Inertia;
class IngredientsController extends Controller
{

    public function getData( Request $request ){
        $ingredients = Ingredient::when(
            $request->unit,
            fn ($q) => $q->where('unit', $request->unit)
        )->get();

        $columns = [
          ['field'=> 'name', 'header' => 'Name'],
          ['field' => 'price', 'header' => 'Price'],
          ['field' => 'size', 'header' => 'Size'],
          ['field' => 'unit', 'header' => 'Unit'],
          ['field' => 'kg_price', 'header' => 'Price per kg']
        ];

        return Inertia::render('Ingredients', [
            'columns' => $columns,
            'ingredients' => $ingredients,
            'filters' => $request->only('unit'),
        ]);
    }

    public function create(){
        return Inertia::render('crud/IngredientsCreate');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'size' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
        ]);

        $data['kg_price'] = $data['price'] / $data['size'];

        Ingredient::create($data);

        return redirect()->route('ingredients')
            ->with('success', 'Ingredient created');
    }

    public function destroy(Ingredient $ingredient)
    {
        $ingredient->delete();

        return redirect()->route('ingredients')
            ->with('success', 'Ingredient deleted');
    }


}
