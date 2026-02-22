<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import ConfirmDialog from 'primevue/confirmdialog'
import { useConfirm } from 'primevue/useconfirm'
import Select from 'primevue/select'
import { computed, ref, watch } from 'vue'

type Component = {
    id: number
    name: string
    grams: number
    price_per_kg: number
}

type Product = {
    id: number
    name: string
    components: Component[]
}

type Client = {
    id: number
    name: string
    phone: string
    location_id: number
    location_name: string
    location_price: number
}

type Location = {
    id: number
    name: string
    price: number
}

type OrderItem = {
    product_id: number
    product_name: string
    components: Component[]
    final_price: number
    portion_grams: number
    units_per_box: number
}

const confirm = useConfirm()

const props = defineProps<{
    products: Product[]
    clients: Client[]
    locations: Location[]
}>()

const TOTAL_WEIGHT = 1000
const step = 5

const products = ref<Product[]>(props.products ?? [])

const selectedProductId = ref<number | null>(products.value.length ? products.value[0].id : null)
const selectedProduct = ref<Product | null>(cloneProduct(products.value[0] ?? null))

const selectedClientId = ref<number | null>(null)
const selectedLocationId = ref<number | null>(props.locations?.[0]?.id ?? null)

const orderItems = ref<OrderItem[]>([])
const editingItemIndex = ref<number | null>(null)

const portionGrams = ref<number>(100)
const unitsPerBox = ref<number>(4)

const costs = ref({
    packaging_material: 0.45,
    production: 0.12,
    packaging: 0.08,
    transportation: 0.45,
    multi_delivery: 0.12,
    sell_percent: 30,
})

const commissionPct = ref(5)
const costsOpen = ref(true)

const nf = (v: number | string, d = 2) => Number(v).toFixed(d).replace('.', ',')

function cloneProduct(p?: Product | null): Product | null {
    if (!p) return null
    return { ...p, components: (p.components ?? []).map(c => ({ ...c })) }
}

// Flag to skip product watcher when editing an existing order item
let skipProductWatch = false

watch(selectedProductId, (id) => {
    if (skipProductWatch) { skipProductWatch = false; return }
    const p = products.value.find(p => p.id === id) ?? null
    selectedProduct.value = cloneProduct(p)
})

// When client changes: auto-set location and transportation from client's location
watch(selectedClientId, (id) => {
    if (!id) return
    const client = props.clients.find(c => c.id === id)
    if (client && client.location_id) {
        selectedLocationId.value = client.location_id
        // transportation will be updated by location watcher
    }
})

// When location changes: update transportation cost from location price
watch(selectedLocationId, (id) => {
    if (!id) return
    const location = props.locations.find(l => l.id === id)
    if (location) {
        costs.value.transportation = Number(location.price)
    }
})

const freeGrams = computed(() => {
    if (!selectedProduct.value) return 0
    const used = selectedProduct.value.components.reduce((s, c) => s + (Number(c.grams) || 0), 0)
    return Math.max(0, TOTAL_WEIGHT - used)
})

const decreaseComponent = (i: number, delta: number) => {
    if (!selectedProduct.value) return
    const c = selectedProduct.value.components[i]
    c.grams = Math.max(0, (Number(c.grams) || 0) - delta)
}

const increaseComponent = (i: number, delta: number) => {
    if (!selectedProduct.value) return
    if (freeGrams.value <= 0) return
    const c = selectedProduct.value.components[i]
    const add = Math.min(delta, freeGrams.value)
    c.grams = (Number(c.grams) || 0) + add
}

const takeAllFree = (i: number) => {
    if (!selectedProduct.value) return
    if (freeGrams.value <= 0) return
    selectedProduct.value.components[i].grams = (Number(selectedProduct.value.components[i].grams) || 0) + freeGrams.value
}

const setGrams = (i: number, ev: Event) => {
    if (!selectedProduct.value) return
    const raw = parseInt((ev.target as HTMLInputElement).value, 10)
    if (isNaN(raw)) return
    const othersTotal = selectedProduct.value.components.reduce((s, c, idx) =>
        idx !== i ? s + (Number(c.grams) || 0) : s, 0)
    const clamped = Math.max(0, Math.min(raw, TOTAL_WEIGHT - othersTotal))
    selectedProduct.value.components[i].grams = clamped
    ;(ev.target as HTMLInputElement).value = String(clamped)
}

const pricePerKg = computed(() => {
    if (!selectedProduct.value) return 0
    const raw = selectedProduct.value.components.reduce((sum, c) => {
        return sum + ((Number(c.price_per_kg) || 0) * (Number(c.grams) || 0)) / 1000
    }, 0)
    return Math.round(raw * 100) / 100
})

const finalPrice = computed(() => {
    const base =
        pricePerKg.value +
        Number(costs.value.packaging_material) +
        Number(costs.value.production) +
        Number(costs.value.packaging) +
        Number(costs.value.transportation) +
        Number(costs.value.multi_delivery)

    const percent = base * (Number(costs.value.sell_percent) / 100)
    return +(base + percent).toFixed(2)
})

// Load an existing order item into the editor
const startEditItem = (i: number) => {
    const item = orderItems.value[i]
    editingItemIndex.value = i
    skipProductWatch = true
    selectedProductId.value = item.product_id
    selectedProduct.value = {
        id: item.product_id,
        name: item.product_name,
        components: item.components.map(c => ({ ...c })),
    }
    portionGrams.value = item.portion_grams
    unitsPerBox.value = item.units_per_box
}

const cancelEdit = () => {
    editingItemIndex.value = null
}

const addToOrder = () => {
    if (!selectedClientId.value) {
        confirm.require({
            header: 'Client not selected',
            message: 'Please select a client before adding a product to the order.',
            icon: 'pi pi-info-circle',
            acceptLabel: 'OK',
            rejectLabel: '',
            rejectClass: 'hidden_btn',
        })
        return
    }

    if (!selectedProduct.value) {
        confirm.require({
            header: 'No products',
            message: 'Create at least one product first.',
            icon: 'pi pi-info-circle',
            acceptLabel: 'OK',
            rejectLabel: '',
            rejectClass: 'hidden_btn',
        })
        return
    }

    const newItem: OrderItem = {
        product_id: selectedProduct.value.id,
        product_name: selectedProduct.value.name,
        components: selectedProduct.value.components.map(c => ({ ...c })),
        final_price: finalPrice.value,
        portion_grams: portionGrams.value,
        units_per_box: unitsPerBox.value,
    }

    if (editingItemIndex.value !== null) {
        orderItems.value[editingItemIndex.value] = newItem
        editingItemIndex.value = null
    } else {
        orderItems.value.push(newItem)
    }
}

const saveOrder = () => {
    if (!selectedClientId.value) {
        confirm.require({
            header: 'Client not selected',
            message: 'Please select a client before saving the order.',
            icon: 'pi pi-info-circle',
            acceptLabel: 'OK',
            rejectLabel: '',
            rejectClass: 'hidden_btn',
        })
        return
    }

    if (!selectedLocationId.value) {
        confirm.require({
            header: 'Location not selected',
            message: 'Please select a location before saving the order.',
            icon: 'pi pi-info-circle',
            acceptLabel: 'OK',
            rejectLabel: '',
            rejectClass: 'hidden_btn',
        })
        return
    }

    if (!orderItems.value.length) {
        confirm.require({
            header: 'Empty order',
            message: 'Add at least one product to the order.',
            icon: 'pi pi-info-circle',
            acceptLabel: 'OK',
            rejectLabel: '',
            rejectClass: 'hidden_btn',
        })
        return
    }

    router.post('/calculator/store', {
        client_id: selectedClientId.value,
        location_id: selectedLocationId.value,
        size: orderItems.value.length,
        commission_pct: commissionPct.value,

        packaging_material: costs.value.packaging_material,
        production: costs.value.production,
        packaging: costs.value.packaging,
        transportation: costs.value.transportation,
        multi_delivery: costs.value.multi_delivery,
        sell_percent: costs.value.sell_percent,

        items: orderItems.value.map(i => ({
            product_id: i.product_id,
            final_price: i.final_price,
            portion_grams: i.portion_grams,
            units_per_box: i.units_per_box,
            components: i.components.map(c => ({
                component_id: c.id,
                grams: c.grams,
                price_per_kg: c.price_per_kg,
            })),
        })),
    }, {
        preserveScroll: true,
        onSuccess: () => {
            orderItems.value = []
            confirm.require({
                header: 'Success',
                message: 'Order saved successfully!',
                icon: 'pi pi-check-circle',
                acceptLabel: 'OK',
                rejectLabel: '',
                rejectClass: 'hidden_btn',
            })
        },
        onError: (e) => {
            console.error('SAVE ORDER ERROR', e)
        }
    })
}

</script>

<template>
    <Head title="Calculator" />
    <AppLayout>
        <ConfirmDialog />

        <div class="p-4 space-y-3">

            <!-- TOP BAR: Client · Location · Commission -->
            <div class="rounded-xl bg-white p-3 shadow">
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <div class="text-xs font-medium text-gray-500 mb-1">Client</div>
                        <Select v-model="selectedClientId" :options="clients" optionLabel="name" optionValue="id"
                                filter filterPlaceholder="Search..." placeholder="Select client" class="w-full"
                                autoFilterFocus>
                            <template #option="{ option }">
                                <div>
                                    <div class="font-medium">{{ option.name }}</div>
                                    <div class="text-xs text-gray-400 mt-0.5">{{ option.phone }} · {{ option.location_name }}</div>
                                </div>
                            </template>
                            <template #value="slotProps">
                                <template v-if="slotProps.value">
                                    <span>{{ clients.find(c => c.id === slotProps.value)?.name }}</span>
                                    <span class="text-xs text-gray-400 ml-1">· {{ clients.find(c => c.id === slotProps.value)?.phone }}</span>
                                </template>
                                <span v-else class="text-gray-400">Select client</span>
                            </template>
                        </Select>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-gray-500 mb-1">Location</div>
                        <Select v-model="selectedLocationId" :options="locations" optionLabel="name" optionValue="id"
                                filter filterPlaceholder="Search..." placeholder="Select location" class="w-full"
                                autoFilterFocus />
                    </div>
                    <div>
                        <div class="text-xs font-medium text-gray-500 mb-1">Commission (%)</div>
                        <input type="number" step="0.5" min="0" max="100" v-model.number="commissionPct"
                               class="w-full rounded border px-2 py-2 text-sm border-purple-300 focus:border-purple-500 focus:outline-none" />
                    </div>
                </div>
            </div>

            <!-- Empty state -->
            <div v-if="!products.length" class="rounded-xl bg-white p-4 shadow">
                <div class="font-semibold">No products</div>
                <div class="text-sm text-gray-500 mt-1">
                    Create ingredients → components → products, then calculator will work.
                </div>
                <button class="mt-3 rounded bg-blue-600 px-4 py-2 text-white" @click="router.visit('/products/create')">
                    + Create product
                </button>
            </div>

            <!-- MAIN GRID -->
            <div v-else class="grid grid-cols-12 gap-3">

                <!-- LEFT: Product + Components -->
                <div class="col-span-7 space-y-3">

                    <!-- Product -->
                    <div class="rounded-xl bg-white p-3 shadow">
                        <div class="text-xs font-medium text-gray-500 mb-1">Product</div>
                        <Select v-model="selectedProductId" :options="products" optionLabel="name" optionValue="id"
                                filter filterPlaceholder="Search..." placeholder="Select product" class="w-full"
                                autoFilterFocus />
                    </div>

                    <!-- Components -->
                    <div v-if="selectedProduct" class="rounded-xl bg-white p-3 shadow space-y-1">
                        <div v-for="(c, i) in selectedProduct.components" :key="c.id"
                             class="flex items-center justify-between border-b py-2 last:border-0">
                            <div class="text-xs uppercase text-gray-400 min-w-0 flex-1 pr-2 truncate">{{ c.name }}</div>
                            <div class="flex items-center gap-1 shrink-0">
                                <button class="h-7 w-7 rounded-full border text-base leading-none" @click="decreaseComponent(i, step)">−</button>
                                <div class="flex items-center">
                                    <input
                                        type="number" min="0" step="1"
                                        :value="c.grams"
                                        @input="setGrams(i, $event)"
                                        class="w-16 rounded border p-1 text-center text-sm"
                                    />
                                    <span class="text-xs text-gray-400 ml-0.5">g</span>
                                </div>
                                <button class="h-7 w-7 rounded-full border text-base leading-none" @click="increaseComponent(i, step)">+</button>
                                <button
                                        class="h-7 w-7 rounded-full border text-xs transition"
                                        :class="freeGrams > 0 ? 'text-yellow-500' : 'text-gray-300 cursor-not-allowed'"
                                        :disabled="freeGrams <= 0"
                                        title="Take all free grams" @click="takeAllFree(i)">⚡</button>
                            </div>
                        </div>
                        <div class="pt-1 text-center">
                            <span class="rounded-full bg-gray-100 px-3 py-0.5 text-xs text-gray-500">
                                Free: {{ freeGrams }} g &nbsp;·&nbsp; Price/kg: {{ nf(pricePerKg) }} €
                            </span>
                        </div>
                    </div>

                    <!-- Portion & Box + Final -->
                    <div class="space-y-2">
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <label>Portion (g)
                                <input type="number" step="1" min="1" v-model.number="portionGrams"
                                       class="mt-0.5 w-full rounded border p-1.5" />
                            </label>
                            <label>Units/box
                                <input type="number" step="1" min="1" v-model.number="unitsPerBox"
                                       class="mt-0.5 w-full rounded border p-1.5" />
                            </label>
                        </div>
                        <div class="text-xs text-gray-400 text-center">
                            box net weight = {{ portionGrams > 0 ? nf(portionGrams * unitsPerBox / 1000, 3) : '—' }} kg
                        </div>
                        <div class="text-center">
                            <span class="text-2xl font-bold text-blue-600">{{ nf(finalPrice) }} €</span>
                            <span class="text-xs text-gray-400 ml-1">/kg</span>
                            <div v-if="portionGrams > 0" class="text-xs text-gray-500 mt-0.5">
                                {{ nf(+(finalPrice * portionGrams / 1000).toFixed(2)) }} €/portion ·
                                {{ nf(+(finalPrice * portionGrams / 1000).toFixed(2) * unitsPerBox) }} €/box
                            </div>
                        </div>
                    </div>

                    <!-- BOTTOM ROW: Costs accordion -->
                    <div v-if="products.length" class="flex gap-3 items-stretch">
                        <!-- Costs accordion -->
                        <div class="flex-1 rounded-xl bg-white p-3 shadow">
                            <button class="flex w-full items-center justify-between text-sm font-semibold"
                                    @click="costsOpen = !costsOpen">
                                <span>Additional costs — per order</span>
                                <span class="text-xs text-gray-400">{{ costsOpen ? '▲ hide' : '▼ show' }}</span>
                            </button>
                            <div v-show="costsOpen" class="mt-2 grid grid-cols-6 gap-2 text-xs">
                                <label>Pkg. mat.
                                    <input type="number" step="0.01" v-model.number="costs.packaging_material"
                                           class="mt-0.5 w-full rounded border p-1.5" />
                                </label>
                                <label>Production
                                    <input type="number" step="0.01" v-model.number="costs.production"
                                           class="mt-0.5 w-full rounded border p-1.5" />
                                </label>
                                <label>Packaging
                                    <input type="number" step="0.01" v-model.number="costs.packaging"
                                           class="mt-0.5 w-full rounded border p-1.5" />
                                </label>
                                <label>Transport
                                    <input type="number" step="0.01" v-model.number="costs.transportation"
                                           class="mt-0.5 w-full rounded border p-1.5" />
                                </label>
                                <label>Multi del.
                                    <input type="number" step="0.01" v-model.number="costs.multi_delivery"
                                           class="mt-0.5 w-full rounded border p-1.5" />
                                </label>
                                <label>Sell %
                                    <input type="number" step="1" v-model.number="costs.sell_percent"
                                           class="mt-0.5 w-full rounded border p-1.5" />
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-2 justify-end">
                        <button v-if="editingItemIndex !== null"
                                class="rounded border border-gray-300 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 transition"
                                @click="cancelEdit">
                            Cancel
                        </button>
                        <button class="rounded px-5 py-2 text-sm text-white transition whitespace-nowrap"
                                :class="editingItemIndex !== null ? 'bg-orange-500 hover:bg-orange-600' : 'bg-green-600 hover:bg-green-700'"
                                @click="addToOrder">
                            {{ editingItemIndex !== null ? '✏️ Update item' : '➕ Add to order' }}
                        </button>
                    </div>
                </div>

                <!-- RIGHT: Portion + Price + Items -->
                <div class="col-span-5">
                    <div class="rounded-xl bg-white p-3 shadow space-y-3">

                        <!-- Order items -->
                        <div class="border-t pt-2 space-y-1">
                            <div class="text-sm font-semibold">Order items</div>
                            <div v-if="!orderItems.length" class="text-xs text-gray-400">No items yet.</div>
                            <div class="space-y-1 max-h-[45vh] overflow-y-auto pr-1">
                                <div v-for="(item, i) in orderItems" :key="i"
                                     class="relative rounded border p-2 text-xs cursor-pointer transition"
                                     :class="editingItemIndex === i
                                         ? 'border-orange-400 bg-orange-50'
                                         : 'hover:border-blue-300 hover:bg-blue-50'"
                                     @click="startEditItem(i)">
                                    <button class="absolute top-1 right-1 text-red-400 hover:text-red-600 px-1"
                                            title="Remove" @click.stop="orderItems.splice(i, 1); if (editingItemIndex === i) editingItemIndex = null">✕</button>
                                    <div class="font-semibold pr-4">{{ item.product_name }}</div>
                                    <div class="text-gray-500">
                                        {{ nf(item.final_price) }} €/kg · {{ item.portion_grams }}g × {{ item.units_per_box }}
                                    </div>
                                    <ul class="mt-0.5 text-gray-400">
                                        <li v-for="c in item.components" :key="c.id">{{ c.name }} – {{ c.grams }}g</li>
                                    </ul>
                                    <div v-if="editingItemIndex === i" class="mt-1 text-orange-500 font-medium">editing...</div>
                                </div>
                            </div>
                        </div>

                        <button style="width: 100%;" class="rounded bg-blue-600 px-5 py-2 text-sm text-white hover:bg-blue-700 transition whitespace-nowrap"
                                @click="saveOrder">
                            Save order
                        </button>

                    </div>
                </div>
            </div>


        </div>
    </AppLayout>
</template>
