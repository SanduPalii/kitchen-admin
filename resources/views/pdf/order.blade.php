<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 20px; margin: 0 0 8px; }
        h3 { font-size: 14px; margin: 18px 0 6px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        td, th { border: 1px solid #d1d5db; padding: 6px; }
        th { background: #f3f4f6; text-align: left; }
        .muted { color: #4b5563; }
        .right { text-align: right; }
        .center { text-align: center; }
    </style>
</head>
<body>

<h1>Order #{{ $order->id }}</h1>

<p class="muted" style="margin-top:0;">
    Client: {{ $order->client?->name ?? '-' }} <br>
    Location: {{ $order->location?->name ?? '-' }} <br>
    Date: {{ $order->date }}
</p>

@foreach($items as $item)
    <h3>{{ $item['product_name'] }} — {{ number_format($item['price'], 2, ',', '') }} €</h3>

    <p class="muted" style="margin:0 0 6px;">
        Packaging material: {{ number_format($order->packaging_material, 2, ',', '') }} €
        · Production: {{ number_format($order->production, 2, ',', '') }} €
        · Packaging: {{ number_format($order->packaging, 2, ',', '') }} €
        · Transport: {{ number_format($order->transportation, 2, ',', '') }} €
        · Multi delivery: {{ number_format($order->multi_delivery, 2, ',', '') }} €
        · Margin: {{ number_format($order->sell_percent, 2, ',', '') }} %
    </p>

    <table>
        <thead>
        <tr>
            <th>Component</th>
            <th class="center">Grams</th>
            <th class="right">€/kg</th>
        </tr>
        </thead>
        <tbody>
        @forelse($item['components'] as $c)
            <tr>
                <td>{{ $c['name'] }}</td>
                <td class="center">{{ number_format($c['grams'], 0) }} g</td>
                <td class="right">{{ number_format($c['price_per_kg'], 2, ',', '') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="muted">No components</td>
            </tr>
        @endforelse
        </tbody>
    </table>
@endforeach

</body>
</html>
