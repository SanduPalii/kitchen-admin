<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Component;
use App\Models\ComponentItem;
use App\Models\Ingredient;
use App\Models\Location;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderProductComponent;
use App\Models\Product;
use App\Models\ProductComponent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class FakeDataSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure we have a user
        $user = User::first() ?? User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // ── INGREDIENTS ──────────────────────────────────────────────
        $ingredients = collect([
            ['name' => 'Chicken breast',     'price' => 8.50,  'size' => 1,    'unit' => 'kg', 'kg_price' => 8.50],
            ['name' => 'Beef',               'price' => 14.90, 'size' => 1,    'unit' => 'kg', 'kg_price' => 14.90],
            ['name' => 'Pork',               'price' => 7.80,  'size' => 1,    'unit' => 'kg', 'kg_price' => 7.80],
            ['name' => 'Salmon',             'price' => 18.50, 'size' => 1,    'unit' => 'kg', 'kg_price' => 18.50],
            ['name' => 'Cod',                'price' => 12.00, 'size' => 1,    'unit' => 'kg', 'kg_price' => 12.00],
            ['name' => 'Basmati rice',       'price' => 3.20,  'size' => 1,    'unit' => 'kg', 'kg_price' => 3.20],
            ['name' => 'Potato',             'price' => 1.20,  'size' => 1,    'unit' => 'kg', 'kg_price' => 1.20],
            ['name' => 'Penne pasta',        'price' => 2.50,  'size' => 0.5,  'unit' => 'kg', 'kg_price' => 5.00],
            ['name' => 'Buckwheat',          'price' => 2.80,  'size' => 1,    'unit' => 'kg', 'kg_price' => 2.80],
            ['name' => 'Bulgur',             'price' => 3.50,  'size' => 1,    'unit' => 'kg', 'kg_price' => 3.50],
            ['name' => 'Onion',              'price' => 1.10,  'size' => 1,    'unit' => 'kg', 'kg_price' => 1.10],
            ['name' => 'Carrot',             'price' => 1.30,  'size' => 1,    'unit' => 'kg', 'kg_price' => 1.30],
            ['name' => 'Tomato',             'price' => 3.50,  'size' => 1,    'unit' => 'kg', 'kg_price' => 3.50],
            ['name' => 'Bell pepper',        'price' => 5.20,  'size' => 1,    'unit' => 'kg', 'kg_price' => 5.20],
            ['name' => 'Broccoli',           'price' => 4.80,  'size' => 1,    'unit' => 'kg', 'kg_price' => 4.80],
            ['name' => 'Spinach',            'price' => 6.50,  'size' => 0.5,  'unit' => 'kg', 'kg_price' => 13.00],
            ['name' => 'Cream 20%',          'price' => 2.30,  'size' => 1,    'unit' => 'l',  'kg_price' => 2.30],
            ['name' => 'Milk',               'price' => 1.20,  'size' => 1,    'unit' => 'l',  'kg_price' => 1.20],
            ['name' => 'Butter',             'price' => 3.80,  'size' => 0.2,  'unit' => 'kg', 'kg_price' => 19.00],
            ['name' => 'Olive oil',          'price' => 8.90,  'size' => 1,    'unit' => 'l',  'kg_price' => 8.90],
            ['name' => 'Sunflower oil',      'price' => 2.50,  'size' => 1,    'unit' => 'l',  'kg_price' => 2.50],
            ['name' => 'Sour cream',         'price' => 1.80,  'size' => 0.5,  'unit' => 'kg', 'kg_price' => 3.60],
            ['name' => 'Parmesan cheese',    'price' => 12.50, 'size' => 0.3,  'unit' => 'kg', 'kg_price' => 41.67],
            ['name' => 'Mozzarella cheese',  'price' => 4.50,  'size' => 0.25, 'unit' => 'kg', 'kg_price' => 18.00],
            ['name' => 'Garlic',             'price' => 8.00,  'size' => 1,    'unit' => 'kg', 'kg_price' => 8.00],
            ['name' => 'Tomato paste',       'price' => 2.20,  'size' => 0.5,  'unit' => 'kg', 'kg_price' => 4.40],
            ['name' => 'Soy sauce',          'price' => 3.80,  'size' => 0.5,  'unit' => 'l',  'kg_price' => 7.60],
            ['name' => 'Honey',              'price' => 6.50,  'size' => 0.5,  'unit' => 'kg', 'kg_price' => 13.00],
            ['name' => 'Chickpeas',          'price' => 3.20,  'size' => 1,    'unit' => 'kg', 'kg_price' => 3.20],
            ['name' => 'Red lentils',        'price' => 4.00,  'size' => 1,    'unit' => 'kg', 'kg_price' => 4.00],
            ['name' => 'Coconut milk',       'price' => 2.80,  'size' => 0.4,  'unit' => 'l',  'kg_price' => 7.00],
            ['name' => 'Salt',               'price' => 0.80,  'size' => 1,    'unit' => 'kg', 'kg_price' => 0.80],
            ['name' => 'Black pepper',       'price' => 12.00, 'size' => 0.1,  'unit' => 'kg', 'kg_price' => 120.00],
            ['name' => 'Paprika',            'price' => 5.00,  'size' => 0.1,  'unit' => 'kg', 'kg_price' => 50.00],
            ['name' => 'Turmeric',           'price' => 6.00,  'size' => 0.1,  'unit' => 'kg', 'kg_price' => 60.00],
            ['name' => 'Tofu',               'price' => 3.50,  'size' => 0.4,  'unit' => 'kg', 'kg_price' => 8.75],
            ['name' => 'Eggs',               'price' => 2.80,  'size' => 10,   'unit' => 'pcs','kg_price' => 0.28],
            ['name' => 'Wheat flour',        'price' => 1.20,  'size' => 1,    'unit' => 'kg', 'kg_price' => 1.20],
            ['name' => 'Zucchini',           'price' => 3.00,  'size' => 1,    'unit' => 'kg', 'kg_price' => 3.00],
            ['name' => 'Eggplant',           'price' => 4.50,  'size' => 1,    'unit' => 'kg', 'kg_price' => 4.50],
        ])->map(fn ($data) => Ingredient::create($data));

        // ── COMPONENTS (semi-finished products) ──────────────────────
        $componentsDef = [
            // Side dishes
            ['name' => 'Boiled rice',              'type' => 'side_dish', 'quantity' => 3000, 'ingredients' => [[5, 1000], [31, 5], [18, 20]]],
            ['name' => 'Mashed potatoes',          'type' => 'side_dish', 'quantity' => 3000, 'ingredients' => [[6, 800], [17, 100], [18, 50], [31, 8]]],
            ['name' => 'Boiled buckwheat',         'type' => 'side_dish', 'quantity' => 2500, 'ingredients' => [[8, 900], [31, 5], [18, 15]]],
            ['name' => 'Penne pasta',              'type' => 'side_dish', 'quantity' => 2500, 'ingredients' => [[7, 800], [31, 10], [19, 20]]],
            ['name' => 'Bulgur with vegetables',   'type' => 'side_dish', 'quantity' => 3000, 'ingredients' => [[9, 600], [10, 100], [12, 80], [13, 80], [19, 30], [31, 5]]],
            ['name' => 'Turmeric rice',            'type' => 'side_dish', 'quantity' => 3000, 'ingredients' => [[5, 900], [34, 5], [18, 20], [31, 5]]],

            // Proteins
            ['name' => 'Grilled chicken breast',   'type' => 'protein', 'quantity' => 2000, 'ingredients' => [[0, 1000], [19, 30], [31, 8], [32, 3], [33, 3]]],
            ['name' => 'Braised beef',             'type' => 'protein', 'quantity' => 2500, 'ingredients' => [[1, 800], [10, 100], [11, 80], [25, 40], [20, 30], [31, 8], [32, 3]]],
            ['name' => 'Roasted pork',             'type' => 'protein', 'quantity' => 2000, 'ingredients' => [[2, 900], [24, 20], [19, 20], [31, 8], [32, 3], [33, 5]]],
            ['name' => 'Steamed salmon',           'type' => 'protein', 'quantity' => 1500, 'ingredients' => [[3, 800], [19, 20], [31, 5], [32, 2]]],
            ['name' => 'Cod in cream',             'type' => 'protein', 'quantity' => 2000, 'ingredients' => [[4, 700], [16, 200], [10, 50], [31, 5], [32, 2]]],
            ['name' => 'Teriyaki tofu',            'type' => 'protein', 'quantity' => 1500, 'ingredients' => [[35, 600], [26, 80], [27, 40], [24, 15], [20, 20]]],
            ['name' => 'Chicken cutlets',          'type' => 'protein', 'quantity' => 2000, 'ingredients' => [[0, 800], [36, 30], [37, 50], [10, 60], [31, 8], [32, 3]]],
            ['name' => 'Beef meatballs',           'type' => 'protein', 'quantity' => 2000, 'ingredients' => [[1, 700], [36, 20], [37, 40], [10, 80], [31, 8], [32, 3]]],

            // Gravies / sauces
            ['name' => 'Cream sauce',              'type' => 'gravy', 'quantity' => 1500, 'ingredients' => [[16, 500], [18, 40], [37, 20], [31, 5], [32, 2]]],
            ['name' => 'Tomato sauce',             'type' => 'gravy', 'quantity' => 2000, 'ingredients' => [[12, 400], [25, 100], [10, 80], [24, 20], [19, 30], [31, 5], [32, 3], [33, 3]]],
            ['name' => 'Mushroom sauce',           'type' => 'gravy', 'quantity' => 1500, 'ingredients' => [[16, 400], [10, 100], [18, 30], [37, 15], [31, 5], [32, 2]]],
            ['name' => 'Teriyaki sauce',           'type' => 'gravy', 'quantity' => 1000, 'ingredients' => [[26, 300], [27, 100], [24, 30], [20, 20]]],
            ['name' => 'Curry sauce',              'type' => 'gravy', 'quantity' => 1500, 'ingredients' => [[30, 400], [34, 8], [10, 80], [24, 15], [20, 20], [31, 5]]],
            ['name' => 'Sour cream sauce',         'type' => 'gravy', 'quantity' => 1500, 'ingredients' => [[21, 500], [10, 60], [24, 15], [31, 5], [32, 2]]],
        ];

        $components = collect();
        foreach ($componentsDef as $def) {
            $component = Component::create([
                'name' => $def['name'],
                'type' => $def['type'],
                'quantity' => $def['quantity'],
            ]);

            foreach ($def['ingredients'] as [$ingredientIdx, $qty]) {
                ComponentItem::create([
                    'component_id' => $component->id,
                    'ingredient_id' => $ingredients[$ingredientIdx]->id,
                    'quantity' => $qty,
                ]);
            }

            $components->push($component);
        }

        // ── PRODUCTS (dishes) ────────────────────────────────────────
        $productsDef = [
            // [name_en, name_fi, name_ee, type, [[componentIdx, quantity, sort]...]]
            ['Chicken with rice',          'Kanaa ja riisiä',         'Kana riisiga',           'base',       [[6, 200, 1], [0, 150, 2], [14, 50, 3]]],
            ['Beef stew with buckwheat',   'Nautapata tattarilla',    'Veisehautis tatrapudruga','base',      [[7, 200, 1], [2, 150, 2], [15, 50, 3]]],
            ['Pork with mashed potatoes',  'Possua ja perunamuusia',  'Siga kartulipudruga',    'base',       [[8, 180, 1], [1, 160, 2], [14, 50, 3]]],
            ['Salmon with bulgur',         'Lohta ja bulguria',       'Lõhe bulguuriga',        'base',       [[9, 180, 1], [4, 150, 2]]],
            ['Cod in cream sauce',         'Turskaa kermakastikkeessa','Tursk koorekastmes',     'base',       [[10, 180, 1], [0, 150, 2], [14, 50, 3]]],
            ['Chicken pasta',              'Kanapasta',               'Kanapasta',              'base',       [[6, 170, 1], [3, 160, 2], [14, 50, 3]]],
            ['Meatballs with rice',        'Lihapullia ja riisiä',    'Lihapallid riisiga',     'base',       [[13, 200, 1], [0, 150, 2], [15, 50, 3]]],
            ['Chicken cutlets with bulgur', 'Kanakyljyksiä ja bulguria','Kanakotletid bulguuriga','base',      [[12, 180, 1], [4, 150, 2], [19, 40, 3]]],
            ['Teriyaki tofu with rice',    'Teriyaki-tofua ja riisiä','Teriyaki tofu riisiga',  'vegan',      [[11, 180, 1], [5, 150, 2], [17, 40, 3]]],
            ['Curry tofu with bulgur',     'Curry-tofua ja bulguria', 'Karri tofu bulguuriga',  'vegan',      [[11, 170, 1], [4, 140, 2], [18, 50, 3]]],
            ['Chicken teriyaki',           'Teriyaki-kanaa',          'Teriyaki kana',          'base',       [[6, 200, 1], [5, 150, 2], [17, 50, 3]]],
            ['Beef with pasta',            'Nautaa ja pastaa',        'Veiseliha pastaga',      'base',       [[7, 180, 1], [3, 160, 2], [15, 50, 3]]],
        ];

        $products = collect();
        foreach ($productsDef as $def) {
            $product = Product::create([
                'name_en' => $def[0],
                'name_fi' => $def[1],
                'name_ee' => $def[2],
                'type' => $def[3],
            ]);

            foreach ($def[4] as [$compIdx, $qty, $sort]) {
                ProductComponent::create([
                    'product_id' => $product->id,
                    'component_id' => $components[$compIdx]->id,
                    'quantity' => $qty,
                    'sort_order' => $sort,
                ]);
            }

            $products->push($product);
        }

        // ── LOCATIONS ────────────────────────────────────────────────
        $locations = collect([
            ['name' => 'Tallinn Keskus',  'price' => 2.50],
            ['name' => 'Tallinn Lasnamäe','price' => 3.00],
            ['name' => 'Tallinn Mustamäe','price' => 3.00],
            ['name' => 'Tartu',           'price' => 5.00],
            ['name' => 'Pärnu',           'price' => 6.50],
            ['name' => 'Narva',           'price' => 7.00],
            ['name' => 'Viljandi',        'price' => 6.00],
        ])->map(fn ($data) => Location::create($data));

        // ── CLIENTS ──────────────────────────────────────────────────
        $clientsDef = [
            ['name' => 'Lasteaed Päikene',      'phone' => '+372 5551 2001', 'location' => 0, 'approved' => true,  'registered_at' => '2025-09-15'],
            ['name' => 'Tallinna Kool nr. 21',   'phone' => '+372 5551 2002', 'location' => 0, 'approved' => true,  'registered_at' => '2025-09-20'],
            ['name' => 'IT Solutions OÜ',        'phone' => '+372 5551 2003', 'location' => 1, 'approved' => true,  'registered_at' => '2025-10-01'],
            ['name' => 'Kohvik Mamma',           'phone' => '+372 5551 2004', 'location' => 0, 'approved' => true,  'registered_at' => '2025-10-10'],
            ['name' => 'Spordiklubi Energia',    'phone' => '+372 5551 2005', 'location' => 2, 'approved' => true,  'registered_at' => '2025-10-20'],
            ['name' => 'Tartu Ülikool söökla',   'phone' => '+372 5551 2006', 'location' => 3, 'approved' => true,  'registered_at' => '2025-11-01'],
            ['name' => 'Pärnu Hotell Strand',    'phone' => '+372 5551 2007', 'location' => 4, 'approved' => true,  'registered_at' => '2025-11-15'],
            ['name' => 'Narva Haigla',           'phone' => '+372 5551 2008', 'location' => 5, 'approved' => true,  'registered_at' => '2025-12-01'],
            ['name' => 'Lasteaed Sipsik',        'phone' => '+372 5551 2009', 'location' => 2, 'approved' => true,  'registered_at' => '2025-12-10'],
            ['name' => 'Rimi Logistics',         'phone' => '+372 5551 2010', 'location' => 0, 'approved' => true,  'registered_at' => '2026-01-05'],
            ['name' => 'Viljandi Gümnaasium',    'phone' => '+372 5551 2011', 'location' => 6, 'approved' => true,  'registered_at' => '2026-01-15'],
            ['name' => 'Selveri kontor',         'phone' => '+372 5551 2012', 'location' => 1, 'approved' => true,  'registered_at' => '2026-01-20'],
            ['name' => 'Nordic Catering OÜ',     'phone' => '+372 5551 2013', 'location' => 0, 'approved' => false, 'registered_at' => '2026-03-25'],
            ['name' => 'Tallinna Lasteaed Mesi', 'phone' => '+372 5551 2014', 'location' => 1, 'approved' => true,  'registered_at' => '2026-02-01'],
            ['name' => 'Bolt Food kontor',       'phone' => '+372 5551 2015', 'location' => 0, 'approved' => true,  'registered_at' => '2026-02-15'],
        ];

        $clients = collect();
        foreach ($clientsDef as $def) {
            $clients->push(Client::create([
                'name' => $def['name'],
                'phone' => $def['phone'],
                'location_id' => $locations[$def['location']]->id,
                'approved' => $def['approved'],
                'registered_at' => Carbon::parse($def['registered_at']),
            ]));
        }

        // ── ORDERS ───────────────────────────────────────────────────
        // Generate orders from October 2025 to April 2026
        $orderTemplates = [];

        // Regular clients ordering weekly
        $regularClients = [0, 1, 2, 4, 5, 7, 8, 10, 11];
        $startDate = Carbon::parse('2025-10-06');
        $endDate = Carbon::parse('2026-04-07');

        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            // Each regular client orders ~2-3 times per month
            foreach ($regularClients as $clientIdx) {
                // Skip if client wasn't registered yet
                $client = $clients[$clientIdx];
                if ($currentDate->lt(Carbon::parse($client->registered_at))) {
                    continue;
                }

                // ~60% chance of ordering this week
                if (fake()->boolean(60)) {
                    $numProducts = fake()->numberBetween(2, 5);
                    $selectedProducts = $products->random($numProducts);
                    $size = fake()->randomElement([10, 15, 20, 25, 30, 40, 50]);

                    $orderTemplates[] = [
                        'client' => $client,
                        'date' => $currentDate->copy()->addDays(fake()->numberBetween(0, 4)),
                        'products' => $selectedProducts,
                        'size' => $size,
                        'approved' => $currentDate->lt(Carbon::now()->subDays(3)),
                    ];
                }
            }

            // Occasional clients (cafes, hotels, one-off)
            $occasionalClients = [3, 6, 9, 13, 14];
            foreach ($occasionalClients as $clientIdx) {
                $client = $clients[$clientIdx];
                if ($currentDate->lt(Carbon::parse($client->registered_at))) {
                    continue;
                }

                if (fake()->boolean(25)) {
                    $numProducts = fake()->numberBetween(3, 6);
                    $selectedProducts = $products->random(min($numProducts, $products->count()));
                    $size = fake()->randomElement([20, 30, 40, 50, 60, 80, 100]);

                    $orderTemplates[] = [
                        'client' => $client,
                        'date' => $currentDate->copy()->addDays(fake()->numberBetween(0, 4)),
                        'products' => $selectedProducts,
                        'size' => $size,
                        'approved' => $currentDate->lt(Carbon::now()->subDays(3)),
                    ];
                }
            }

            $currentDate->addWeek();
        }

        // Create orders
        foreach ($orderTemplates as $tmpl) {
            $order = Order::create([
                'client_id' => $tmpl['client']->id,
                'location_id' => $tmpl['client']->location_id,
                'user_id' => $user->id,
                'price' => 0,
                'size' => $tmpl['size'],
                'date' => $tmpl['date'],
                'approved' => $tmpl['approved'],
                'commission_pct' => fake()->randomElement([3.00, 5.00, 5.00, 5.00, 7.00]),
                'packaging_material' => fake()->randomFloat(4, 0.05, 0.20),
                'production' => fake()->randomFloat(4, 0.30, 0.80),
                'packaging' => fake()->randomFloat(4, 0.10, 0.30),
                'transportation' => fake()->randomFloat(4, 0.15, 0.50),
                'multi_delivery' => fake()->randomFloat(4, 0.00, 0.15),
                'sell_percent' => fake()->randomElement([25.00, 30.00, 30.00, 35.00]),
            ]);

            $totalPrice = 0;

            foreach ($tmpl['products'] as $product) {
                $portionGrams = fake()->randomElement([300, 350, 400, 450, 500]);
                $unitsPerBox = fake()->randomElement([1, 2, 4, 6, 8, 10]);

                $orderProduct = OrderProduct::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'portion_grams' => $portionGrams,
                    'units_per_box' => $unitsPerBox,
                    'sell_percent' => fake()->randomElement([0, 25, 30, 35]),
                ]);

                // Add order_product_components (snapshot of product's components at order time)
                $productComponents = ProductComponent::where('product_id', $product->id)->get();
                $productCost = 0;

                foreach ($productComponents as $pc) {
                    $component = Component::with('ingredients')->find($pc->component_id);

                    // Calculate price_per_kg from component cost
                    $componentCost = 0;
                    foreach ($component->ingredients as $ing) {
                        $componentCost += ($ing->pivot->quantity / $component->quantity) * $ing->kg_price;
                    }

                    // Add some historical price variation (±15%)
                    $pricePerKg = round($componentCost * fake()->randomFloat(2, 0.85, 1.15), 4);
                    $grams = $pc->quantity;

                    OrderProductComponent::create([
                        'order_product_id' => $orderProduct->id,
                        'component_id' => $component->id,
                        'grams' => $grams,
                        'price_per_kg' => $pricePerKg,
                    ]);

                    $productCost += ($grams / 1000) * $pricePerKg;
                }

                $totalPrice += $productCost * $tmpl['size'];
            }

            // Update order total price
            $order->update(['price' => round($totalPrice, 2)]);
        }

        $this->command->info('Fake data seeded successfully!');
        $this->command->info('Created: ' . $ingredients->count() . ' ingredients');
        $this->command->info('Created: ' . $components->count() . ' components');
        $this->command->info('Created: ' . $products->count() . ' products');
        $this->command->info('Created: ' . $locations->count() . ' locations');
        $this->command->info('Created: ' . $clients->count() . ' clients');
        $this->command->info('Created: ' . count($orderTemplates) . ' orders');
    }
}
