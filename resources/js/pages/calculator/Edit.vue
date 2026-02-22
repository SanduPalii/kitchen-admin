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
    order: {
        id: number
        client_id: number
        location_id: number
        commission_pct: number
        packaging_material: number
        production: number
        packaging: number
        transportation: number
        multi_delivery: number
        sell_percent: number
        items: {
            product_id: number
            final_price: number
            portion_grams: number
            units_per_box: number
            components: {
                component_id: number
                name: string
                grams: number
                price_per_kg: number
            }[]
        }[]
    }
    products: Product[]
    clients: { id: number; name: string; phone: string; location_id: number; location_name: string; location_price: number }[]
    locations: { id: number; name: string; price: number }[]
}>()

const TOTAL_WEIGHT = 1000
const STEP = 10

const selectedClientId = ref<number | null>(props.order.client_id)
const selectedLocationId = ref<number | null>(props.order.location_id)

const costs = ref({
    packaging_material: props.order.packaging_material ?? 0.45,
    production: props.order.production ?? 0.12,
    packaging: props.order.packaging ?? 0.08,
    transportation: props.order.transportation ?? 0.45,
    multi_delivery: props.order.multi_delivery ?? 0.12,
    sell_percent: props.order.sell_percent ?? 30,
})

const commissionPct = ref<number>(props.order.commission_pct ?? 5)
const costsOpen = ref(true)

// When location changes: update transportation cost from location price
watch(selectedLocationId, (id) => {
    if (!id) return
    const location = props.locations.find(l => l.id === id)
    if (location) {
        costs.value.transportation = Number(location.price)
    }
})

const nf = (v: number | string, d = 2) => Number(v).toFixed(d).replace('.', ',')

// ── Add product ──────────────────────────────────────────────
const addProductId = ref<number | null>(null)
const addPortionGrams = ref<number>(100)
const addUnitsPerBox = ref<number>(4)

const addProductData = computed(() => {
    if (!addProductId.value) return null
    return props.products.find(p => p.id === addProductId.value) ?? null
})

function addToOrder() {
    if (!addProductData.value) return
    const p = addProductData.value
    orderItems.value.push({
        product_id: p.id,
        product_name: p.name,
        components: p.components.map(c => ({ ...c })),
        final_price: 0,
        portion_grams: addPortionGrams.value,
        units_per_box: addUnitsPerBox.value,
    })
    editingIndex.value = orderItems.value.length - 1
    addProductId.value = null
}

function removeItem(i: number) {
    orderItems.value.splice(i, 1)
    if (editingIndex.value >= orderItems.value.length) {
        editingIndex.value = Math.max(0, orderItems.value.length - 1)
    }
}

// Prefill all order items from props
const orderItems = ref<OrderItem[]>(
    props.order.items.map(item => {
        const product = props.products.find(p => p.id === item.product_id)
        return {
            product_id: item.product_id,
            product_name: product?.name ?? `Product #${item.product_id}`,
            components: item.components.map(c => ({
                id: c.component_id,
                name: c.name,
                grams: c.grams,
                price_per_kg: c.price_per_kg,
            })),
            final_price: item.final_price,
            portion_grams: item.portion_grams ?? 100,
            units_per_box: item.units_per_box ?? 1,
        }
    })
)

// Index of the currently edited item
const editingIndex = ref<number>(0)

const editingItem = computed(() => orderItems.value[editingIndex.value] ?? null)

const freeGrams = computed(() => {
    if (!editingItem.value) return 0
    const used = editingItem.value.components.reduce((s, c) => s + (c.grams || 0), 0)
    return Math.max(0, TOTAL_WEIGHT - used)
})

const decreaseComponent = (i: number) => {
    if (!editingItem.value) return
    const c = editingItem.value.components[i]
    c.grams = Math.max(0, c.grams - STEP)
}

const increaseComponent = (i: number) => {
    if (!editingItem.value || freeGrams.value <= 0) return
    const c = editingItem.value.components[i]
    const add = Math.min(STEP, freeGrams.value)
    c.grams += add
}

const takeAllFree = (i: number) => {
    if (!editingItem.value || freeGrams.value <= 0) return
    editingItem.value.components[i].grams += freeGrams.value
}

const setGrams = (i: number, ev: Event) => {
    if (!editingItem.value) return
    const raw = parseInt((ev.target as HTMLInputElement).value, 10)
    if (isNaN(raw)) return
    const othersTotal = editingItem.value.components.reduce((s, c, idx) =>
        idx !== i ? s + (c.grams || 0) : s, 0)
    const clamped = Math.max(0, Math.min(raw, TOTAL_WEIGHT - othersTotal))
    editingItem.value.components[i].grams = clamped
    ;(ev.target as HTMLInputElement).value = String(clamped)
}

const pricePerKg = computed(() => {
    if (!editingItem.value) return 0
    const raw = editingItem.value.components.reduce((sum, c) => {
        return sum + (c.price_per_kg * c.grams) / 1000
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

function computeItemPrice(item: OrderItem): number {
    const raw = item.components.reduce((sum, c) => sum + (c.price_per_kg * c.grams) / 1000, 0)
    const pkgPerKg = Math.round(raw * 100) / 100
    const base =
        pkgPerKg +
        Number(costs.value.packaging_material) +
        Number(costs.value.production) +
        Number(costs.value.packaging) +
        Number(costs.value.transportation) +
        Number(costs.value.multi_delivery)
    return +(base * (1 + Number(costs.value.sell_percent) / 100)).toFixed(2)
}

const saveOrder = () => {
    router.put(`/calculator/${props.order.id}`, {
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

        items: orderItems.value.map(item => ({
            product_id: item.product_id,
            final_price: computeItemPrice(item),
            portion_grams: item.portion_grams,
            units_per_box: item.units_per_box,
            components: item.components.map(c => ({
                component_id: c.id,
                grams: c.grams,
                price_per_kg: c.price_per_kg,
            })),
        })),
    }, {
        onSuccess: () => {
            confirm.require({
                header: 'Updated',
                message: 'Order updated successfully!',
                icon: 'pi pi-check-circle',
                acceptLabel: 'OK',
                rejectLabel: '',
                rejectClass: 'hidden_btn',
            })
        },
        onError: (e) => {
            console.error('UPDATE ORDER ERROR', e)
        }
    })
}
</script>

<template>
    <Head title="Edit order" />
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

            <!-- MAIN GRID -->
            <div class="grid grid-cols-12 gap-3">

                <!-- LEFT: Components editor -->
                <div class="col-span-7 space-y-3">
                    <template v-if="editingItem">
                        <!-- Components -->
                        <div class="rounded-xl bg-white p-3 shadow space-y-1">
                            <div class="text-sm font-semibold mb-2">{{ editingItem.product_name }} — components</div>
                            <div v-for="(c, i) in editingItem.components" :key="c.id"
                                 class="flex items-center justify-between border-b py-2 last:border-0">
                                <div class="text-xs uppercase text-gray-400 min-w-0 flex-1 pr-2 truncate">{{ c.name }}</div>
                                <div class="flex items-center gap-1 shrink-0">
                                    <button class="h-7 w-7 rounded-full border text-base leading-none" @click="decreaseComponent(i)">−</button>
                                    <div class="flex items-center">
                                        <input
                                            type="number" min="0" step="1"
                                            :value="c.grams"
                                            @input="setGrams(i, $event)"
                                            class="w-16 rounded border p-1 text-center text-sm"
                                        />
                                        <span class="text-xs text-gray-400 ml-0.5">g</span>
                                    </div>
                                    <button class="h-7 w-7 rounded-full border text-base leading-none" @click="increaseComponent(i)">+</button>
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
                        <div class="rounded-xl bg-white p-3 shadow space-y-2">
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <label>Portion (g)
                                    <input type="number" step="1" min="1" v-model.number="editingItem.portion_grams"
                                           class="mt-0.5 w-full rounded border p-1.5" />
                                </label>
                                <label>Units/box
                                    <input type="number" step="1" min="1" v-model.number="editingItem.units_per_box"
                                           class="mt-0.5 w-full rounded border p-1.5" />
                                </label>
                            </div>
                            <div class="text-xs text-gray-400 text-center">
                                box net weight = {{ editingItem.portion_grams > 0 ? nf(editingItem.portion_grams * editingItem.units_per_box / 1000, 3) : '—' }} kg
                            </div>
                            <div class="text-center">
                                <span class="text-2xl font-bold text-blue-600">{{ nf(finalPrice) }} €</span>
                                <span class="text-xs text-gray-400 ml-1">/kg</span>
                                <div v-if="editingItem.portion_grams > 0" class="text-xs text-gray-500 mt-0.5">
                                    {{ nf(+(finalPrice * editingItem.portion_grams / 1000).toFixed(2)) }} €/portion ·
                                    {{ nf(+(finalPrice * editingItem.portion_grams / 1000).toFixed(2) * editingItem.units_per_box) }} €/box
                                </div>
                            </div>
                        </div>


                        <!-- BOTTOM ROW: Costs accordion + Save order -->
                        <div class="flex gap-3 items-stretch">

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
                    </template>
                    <div v-else class="rounded-xl bg-white p-4 shadow text-sm text-gray-400">
                        Select a product from the list to edit it.
                    </div>
                </div>

                <!-- RIGHT: Items + Add product -->
                <div class="col-span-5">
                    <div class="rounded-xl bg-white p-3 shadow space-y-3">
                        <div class="text-sm font-semibold">Order items</div>

                        <!-- Scrollable list -->
                        <div class="space-y-1 max-h-[45vh] overflow-y-auto pr-1">
                            <div v-for="(item, i) in orderItems" :key="i"
                                 class="relative rounded border p-2 cursor-pointer text-xs"
                                 :class="editingIndex === i ? 'border-blue-500 bg-blue-50' : 'hover:border-gray-400'"
                                 @click="editingIndex = i">
                                <button class="absolute top-1 right-1 text-red-400 hover:text-red-600 px-1"
                                        title="Remove" @click.stop="removeItem(i)">✕</button>
                                <div class="font-semibold pr-4">{{ item.product_name }}</div>
                                <div class="text-gray-500">
                                    {{ nf(computeItemPrice(item)) }} €/kg · {{ item.portion_grams }}g × {{ item.units_per_box }}
                                </div>
                                <ul class="text-gray-400 mt-0.5">
                                    <li v-for="c in item.components" :key="c.id">{{ c.name }} – {{ c.grams }}g</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Add product -->
                        <div class="border-t pt-3 space-y-2">
                            <div class="text-xs font-medium text-gray-500">Add product</div>
                            <Select v-model="addProductId" :options="products" optionLabel="name" optionValue="id"
                                    filter filterPlaceholder="Search..." placeholder="Select product" class="w-full"
                                    autoFilterFocus />
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <label>Portion (g)
                                    <input type="number" min="1" step="1" v-model.number="addPortionGrams"
                                           class="mt-0.5 w-full rounded border p-1" />
                                </label>
                                <label>Units/box
                                    <input type="number" min="1" step="1" v-model.number="addUnitsPerBox"
                                           class="mt-0.5 w-full rounded border p-1" />
                                </label>
                            </div>
                            <button class="w-full rounded bg-green-600 py-1.5 text-sm text-white disabled:opacity-40 hover:bg-green-700 transition"
                                    :disabled="!addProductId" @click="addToOrder">
                                ➕ Add to order
                            </button>
                        </div>


                        <!-- Save order button -->
                        <button style="width: 100%;" class="rounded bg-blue-600 px-6 py-2 text-sm text-white hover:bg-blue-700 transition whitespace-nowrap"
                                @click="saveOrder">
                            Save order
                        </button>
                    </div>
                </div>
            </div>


        </div>
    </AppLayout>
</template>
