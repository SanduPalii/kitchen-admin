<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 10mm 12mm; }

        * { box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9pt;
            color: #111;
            margin: 0;
            padding: 0;
        }

        /* ── Page frame ─────────────────────────── */
        .frame {
            border: 1.5px solid #222;
            border-radius: 5px;
            padding: 14px 16px 16px;
        }

        /* ── Header ─────────────────────────────── */
        .header-table {
            width: 100%;
            margin-bottom: 10px;
            border-collapse: collapse;
        }

        .title {
            font-size: 15pt;
            font-weight: bold;
            line-height: 1.2;
            padding-left: 12px;
        }

        .date-label {
            font-size: 8pt;
            color: #333;
            white-space: nowrap;
            text-align: right;
        }

        /* ── Main table ──────────────────────────── */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #999;
        }

        .main-table thead tr th {
            padding: 5px 10px;
            font-weight: bold;
            text-align: left;
            border-bottom: 1.5px solid #999;
            font-size: 8.5pt;
            line-height: 1.15;
        }

        .th-price {
            text-align: center !important;
            border-left: 1.5px solid #999;
            width: 110px;
            font-size: 7.5pt;
            padding: 5px 14px;
        }

        /* Info row */
        .info-row td {
            padding: 6px 10px 4px;
            font-size: 8.5pt;
            color: #333;
        }

        .info-inner { width: 100%; border-collapse: collapse; }

        .spacer-row td { height: 4px; }

        /* Product rows */
        .product-row td {
            padding: 2px 10px;
            border-top: 0.5px solid #ddd;
            vertical-align: middle;
            font-size: 8pt;
            line-height: 1.2;
        }

        .product-sub {
            font-size: 7pt;
            color: #888;
            line-height: 1.1;
        }

        .badge-vegan { color: #16a34a; }
        .badge-vegetarian { color: #16a34a; }

        .price-col {
            border-left: 1.5px solid #999;
            text-align: right;
            white-space: nowrap;
            width: 110px;
            vertical-align: middle;
            padding: 2px 14px;
            font-size: 8pt;
        }
    </style>
</head>
<body>

<div class="frame">

    {{-- ── Header ── --}}
    <table class="header-table">
        <tr>
            <td style="width:72px; vertical-align:middle;">
                @if($logo)
                    <img src="{{ $logo }}" alt="Dal's Kitchen" style="height:54px; width:auto;">
                @else
                    <div style="font-size:20pt;font-weight:bold;line-height:1;">Dal's</div>
                    <div style="font-size:6pt;letter-spacing:3px;">KITCHEN</div>
                @endif
            </td>

            <td style="vertical-align:middle;">
                <div class="title">Pricing Model _ Ready Meals{{ $grams_suffix }} _ {{ $client_name }}</div>
            </td>

            <td style="width:46px; vertical-align:top; text-align:right;">
                <div class="date-label">{{ $date_formatted }}</div>
            </td>
        </tr>
    </table>

    {{-- ── Pricing table ── --}}
    <table class="main-table">
        <thead>
            <tr>
                <th>Product</th>
                <th class="th-price">price per<br>piece</th>
                <th class="th-price">price of a box of<br>{{ $box_size }} pieces</th>
            </tr>
        </thead>

        <tbody>
            <tr class="info-row">
                <td colspan="3">
                    <table class="info-inner">
                        <tr>
                            <td>Delivery and Storage Temperature: +2 °C to +6</td>
                            <td style="text-align:right; padding-right:6px;">Use by date: 10 days from order</td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="spacer-row"><td colspan="3"></td></tr>

            @foreach($items as $item)
            <tr class="product-row">
                <td>
                    {{ $item['name'] }}@if($item['type'] === 'vegan') <span class="badge-vegan">(vegan)</span>@elseif($item['type'] === 'vegetarian') <span class="badge-vegetarian">(vegetarian)</span>@endif
                    <div class="product-sub">{{ $item['portion_grams'] }} g &nbsp;·&nbsp; {{ $item['units_per_box'] }} pcs / box</div>
                </td>
                <td class="price-col">€ {{ number_format($item['price_per_piece'], 2, ',', '') }}</td>
                <td class="price-col">€ {{ number_format($item['price_per_box'], 2, ',', '') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>

</body>
</html>
