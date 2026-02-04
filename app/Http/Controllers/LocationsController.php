<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LocationsController extends Controller
{
    public function index()
    {
        $locations = Location::all();

        $columns = [
            ['field' => 'name', 'header' => 'Name'],
            ['field' => 'price', 'header' => 'Delivery price'],
        ];

        return Inertia::render('locations/Index', [
            'locations' => $locations,
            'columns' => $columns,
        ]);
    }

    public function create()
    {
        return Inertia::render('locations/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        Location::create($data);

        return redirect()->route('locations')->with('success', 'Location created');
    }

    public function edit(Location $location)
    {
        return Inertia::render('locations/Edit', [
            'location' => $location,
        ]);
    }

    public function update(Request $request, Location $location)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        $location->update($data);

        return redirect()->route('locations')->with('success', 'Location updated');
    }

    public function destroy(Location $location)
    {
        $location->delete();

        return redirect()->route('locations')->with('success', 'Location deleted');
    }
}
