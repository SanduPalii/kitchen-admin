<?php
namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Location;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientsController extends Controller
{
    public function index()
    {
        $clients = Client::with('location')->get();

        $columns = [
            ['field' => 'name', 'header' => 'Name'],
            ['field' => 'phone', 'header' => 'Phone'],
            ['field' => 'location.name', 'header' => 'Location'],
            ['field' => 'approved', 'header' => 'Approved'],
        ];

        return Inertia::render('clients/Index', [
            'clients' => $clients,
            'columns' => $columns,
        ]);
    }

    public function create()
    {
        return Inertia::render('clients/Create', [
            'locations' => Location::select('id', 'name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'location_id' => 'required|exists:locations,id',
            'approved' => 'boolean',
        ]);
        $data['registered_at'] = now();
        Client::create($data);

        return redirect()->route('clients')->with('success', 'Client created');
    }

    public function edit(Client $client)
    {
        return Inertia::render('clients/Edit', [
            'client' => $client,
            'locations' => Location::select('id', 'name')->get(),
        ]);
    }

    public function update(Request $request, Client $client)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'location_id' => 'required|exists:locations,id',
            'approved' => 'boolean',
        ]);

        $client->update($data);

        return redirect()->route('clients')->with('success', 'Client updated');
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()->route('clients')->with('success', 'Client deleted');
    }
}
