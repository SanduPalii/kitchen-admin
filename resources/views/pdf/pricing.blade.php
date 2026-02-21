<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 12mm 14mm; }

        * { box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10pt;
            color: #111;
            margin: 0;
            padding: 0;
        }

        /* ── Page frame ────────────────────────── */
        .frame {
            border: 1.5px solid #777;
            border-radius: 7px;
            padding: 18px 20px 24px;
        }

        /* ── Header ────────────────────────────── */
        .header { width: 100%; }

        .logo-large {
            font-family: DejaVu Serif, serif;
            font-size: 24pt;
            font-weight: bold;
            line-height: 1;
        }
        .logo-small {
            font-size: 6.5pt;
            letter-spacing: 3px;
            margin-top: 2px;
        }
        .title {
            font-size: 17pt;
            font-weight: bold;
            line-height: 1.25;
        }
        .date-label {
            font-size: 9pt;
            color: #333;
            white-space: nowrap;
        }

        .divider {
            border: none;
            border-top: 1.5px solid #888;
            margin: 12px 0 8px;
        }

        /* ── Pricing table ──────────────────────── */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #999;
        }

        .main-table thead th {
            padding: 9px 10px;
            font-weight: bold;
            border-bottom: 1.5px solid #999;
            text-align: left;
        }

        .th-price {
            text-align: center !important;
            border-left: 1.5px solid #999;
            width: 108px;
        }

        /* info row (delivery / use-by) */
        .info-cell { padding: 9px 10px 2px; font-size: 9pt; }
        .info-inner { width: 100%; }

        /* spacer between info and product rows */
        .spacer-row td { height: 7px; }

        /* product rows */
        .product-row td {
            padding: 5px 10px 3px;
            border-top: 0.5px solid #ddd;
            vertical-align: middle;
        }

        /* product name */
        .product-name { font-size: 10pt; line-height: 1.3; }

        /* packaging sub-line */
        .pkg-info {
            font-size: 7.5pt;
            color: #666;
            margin-top: 1px;
            line-height: 1;
        }

        /* type badge */
        .badge-vegan, .badge-vegetarian {
            color: #16a34a;
            font-weight: bold;
            font-size: 9pt;
        }

        /* price cells */
        .price-col {
            border-left: 1.5px solid #999;
            text-align: right;
            white-space: nowrap;
            width: 108px;
            vertical-align: middle;
        }

        /* box-info sub-line in price-col */
        .box-label {
            font-size: 7.5pt;
            color: #888;
            text-align: right;
            margin-top: 1px;
        }
    </style>
</head>
<body>

<div class="frame">

    {{-- ── Header ── --}}
    <table class="header">
        <tr>
            {{-- Logo --}}
            <td style="width:82px; vertical-align:middle;">
                @if($logo)
                    <img src="{{ $logo }}" alt="Dal's Kitchen" style="height:62px; width:auto;">
                @else
                    <div class="logo-large">Dal's</div>
                    <div class="logo-small">KITCHEN</div>
                @endif
            </td>

            {{-- Title --}}
            <td style="vertical-align:middle; padding-left:14px;">
                <div class="title">
                    Pricing Model _ Ready Meals{{ $grams_suffix }} _ {{ $client_name }}
                </div>
            </td>

            {{-- Date --}}
            <td style="text-align:right; vertical-align:top; width:50px;">
                <div class="date-label">{{ $date_formatted }}</div>
            </td>
        </tr>
    </table>

    <hr class="divider">

    {{-- ── Pricing table ── --}}
    <table class="main-table">
        <thead>
            <tr>
                <th><strong>Product</strong></th>
                <th class="th-price"><strong>price per<br>piece</strong></th>
                <th class="th-price"><strong>price per<br>box</strong></th>
            </tr>
        </thead>

        <tbody>

            {{-- Delivery / use-by info row --}}
            <tr>
                <td class="info-cell" colspan="3">
                    <table class="info-inner">
                        <tr>
                            <td>Delivery and Storage Temperature: +2 °C to +6</td>
                            <td style="text-align:right; padding-right:8px;">Use by date: 10 days from order</td>
                        </tr>
                    </table>
                </td>
            </tr>

            {{-- Spacer --}}
            <tr class="spacer-row"><td colspan="3"></td></tr>

            {{-- ── Product rows ── --}}
            @foreach($items as $item)
            <tr class="product-row">

                {{-- Name + type badge + packaging info --}}
                <td>
                    <div class="product-name">
                        {{ $item['name'] }}
                        @if($item['type'] === 'vegan')
                            <span class="badge-vegan">(vegan)</span>
                        @elseif($item['type'] === 'vegetarian')
                            <span class="badge-vegetarian">(vegetarian)</span>
                        @endif
                    </div>
                    <div class="pkg-info">
                        {{ $item['portion_grams'] }} g &nbsp;·&nbsp; {{ $item['units_per_box'] }} pcs/box
                    </div>
                </td>

                {{-- Price per piece --}}
                <td class="price-col">
                    € {{ number_format($item['price_per_piece'], 2, '.', ' ') }}
                </td>

                {{-- Price per box (with "× N pcs" hint) --}}
                <td class="price-col">
                    € {{ number_format($item['price_per_box'], 2, '.', ' ') }}
                    <div class="box-label">× {{ $item['units_per_box'] }} pcs</div>
                </td>


            </tr>
            @endforeach

        </tbody>
    </table>

</div>

</body>
</html>
