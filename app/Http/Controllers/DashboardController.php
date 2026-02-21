<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Location;
use App\Models\Order;
use Carbon\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $orders = Order::with(['client:id,name', 'location:id,name', 'products:id,type'])
            ->orderBy('date')
            ->get()
            ->map(fn ($o) => [
                'id'            => $o->id,
                'date'          => Carbon::parse($o->date)->format('Y-m-d'),
                'price'         => (float) $o->price,
                'size'          => (int) $o->size,
                'approved'      => (bool) $o->approved,
                'client_id'     => $o->client_id,
                'client_name'   => $o->client?->name ?? '—',
                'location_id'   => $o->location_id,
                'location_name' => $o->location?->name ?? '—',
                'types'         => $o->products->countBy('type')->toArray(),
            ]);

        $clients   = Client::orderBy('name')->get(['id', 'name']);
        $locations = Location::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Dashboard', compact('orders', 'clients', 'locations'));
    }
}
