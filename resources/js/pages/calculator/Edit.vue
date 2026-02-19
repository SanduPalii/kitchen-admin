<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import ConfirmDialog from 'primevue/confirmdialog'
import { useConfirm } from 'primevue/useconfirm'
import { computed, ref } from 'vue'

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
        items: {
            product_id: number
            final_price: number
            portion_grams: number
            units_per_box: number
            packaging_material: number
            production: number
            packaging: number
            transportation: number
            multi_delivery: number
            sell_percent: number
            components: {
                component_id: number
                name: string
                grams: number
                price_per_kg: number
            }[]
        }[]
    }
    products: Product[]
    clients: { id: number; name: string }[]
    locations: { id: number; name: string; price: number }[]
}>()

const TOTAL_WEIGHT = 1000
const STEP = 10

const selectedClientId = ref<number | null>(props.order.client_id)
const selectedLocationId = ref<number | null>(props.order.location_id)

const costs = ref({
    packaging_material: props.order.items[0]?.packaging_material ?? 0.45,
    production: props.order.items[0]?.production ?? 0.12,
    packaging: props.order.items[0]?.packaging ?? 0.08,
    transportation: props.order.items[0]?.transportation ?? 0.45,
    multi_delivery: props.order.items[0]?.multi_delivery ?? 0.12,
    sell_percent: props.order.items[0]?.sell_percent ?? 30,
})

// Предзаполняем все позиции заказа из props
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

// Индекс редактируемой позиции
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

const pricePerKg = computed(() => {
    if (!editingItem.value) return 0
    return editingItem.value.components.reduce((sum, c) => {
        return sum + (c.price_per_kg * c.grams) / 1000
    }, 0)
})

const finalPrice = computed(() => {
    const base =
        pricePerKg.value +
        costs.value.packaging_material +
        costs.value.production +
        costs.value.packaging +
        costs.value.transportation +
        costs.value.multi_delivery
    const percent = base * (costs.value.sell_percent / 100)
    return +(base + percent).toFixed(2)
})

function computeItemPrice(item: OrderItem): number {
    const pkgPerKg = item.components.reduce((sum, c) => sum + (c.price_per_kg * c.grams) / 1000, 0)
    const base =
        pkgPerKg +
        costs.value.packaging_material +
        costs.value.production +
        costs.value.packaging +
        costs.value.transportation +
        costs.value.multi_delivery
    return +(base * (1 + costs.value.sell_percent / 100)).toFixed(2)
}

const saveOrder = () => {
    router.put(`/calculator/${props.order.id}`, {
        client_id: selectedClientId.value,
        location_id: selectedLocationId.value,
        size: orderItems.value.length,
        items: orderItems.value.map(item => ({
            product_id: item.product_id,
            final_price: computeItemPrice(item),
            portion_grams: item.portion_grams,
            units_per_box: item.units_per_box,
            packaging_material: costs.value.packaging_material,
            production: costs.value.production,
            packaging: costs.value.packaging,
            transportation: costs.value.transportation,
            multi_delivery: costs.value.multi_delivery,
            sell_percent: costs.value.sell_percent,
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

        <div class="grid grid-cols-12 gap-6 p-6">
            <!-- LEFT: editor -->
            <div class="col-span-8 space-y-6">

                <!-- Client -->
                <div class="rounded-xl bg-white p-4 shadow">
                    <div class="font-semibold mb-2">Client</div>
                    <select v-model="selectedClientId" class="w-full rounded border p-2">
                        <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                </div>

                <!-- Location -->
                <div class="rounded-xl bg-white p-4 shadow">
                    <div class="font-semibold mb-2">Location</div>
                    <select v-model="selectedLocationId" class="w-full rounded border p-2">
                        <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }}</option>
                    </select>
                </div>

                <template v-if="editingItem">
                    <!-- Components -->
                    <div class="rounded-xl bg-white p-4 shadow space-y-4">
                        <div class="font-semibold">
                            {{ editingItem.product_name }} — components
                        </div>

                        <div
                            v-for="(c, i) in editingItem.components"
                            :key="c.id"
                            class="flex items-center justify-between border-b pb-3"
                        >
                            <div>
                                <div class="text-sm uppercase text-gray-500">{{ c.name }}</div>
                                <div class="text-xl font-semibold">{{ c.grams }} g</div>
                            </div>

                            <div class="flex items-center gap-2">
                                <button class="h-8 w-8 rounded-full border" @click="decreaseComponent(i)">-</button>
                                <button class="h-8 w-8 rounded-full border" @click="increaseComponent(i)">+</button>
                                <button
                                    v-if="freeGrams > 0"
                                    class="h-8 w-8 rounded-full border text-yellow-600"
                                    title="Take all free grams"
                                    @click="takeAllFree(i)"
                                >⚡</button>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <span class="rounded-full bg-gray-100 px-4 py-1 text-sm">
                                Free grams: {{ freeGrams }} g · Price/kg: {{ pricePerKg.toFixed(2) }} €
                            </span>
                        </div>
                    </div>

                    <!-- Costs -->
                    <div class="rounded-xl bg-white p-4 shadow space-y-3">
                        <div class="font-semibold">Additional costs (shared for all products)</div>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <label>
                                Packaging material
                                <input type="number" step="0.01" v-model.number="costs.packaging_material" class="w-full rounded border p-2" />
                            </label>
                            <label>
                                Production
                                <input type="number" step="0.01" v-model.number="costs.production" class="w-full rounded border p-2" />
                            </label>
                            <label>
                                Packaging
                                <input type="number" step="0.01" v-model.number="costs.packaging" class="w-full rounded border p-2" />
                            </label>
                            <label>
                                Transportation
                                <input type="number" step="0.01" v-model.number="costs.transportation" class="w-full rounded border p-2" />
                            </label>
                            <label>
                                Multi delivery
                                <input type="number" step="0.01" v-model.number="costs.multi_delivery" class="w-full rounded border p-2" />
                            </label>
                            <label>
                                Sell percent (%)
                                <input type="number" step="1" v-model.number="costs.sell_percent" class="w-full rounded border p-2" />
                            </label>
                        </div>
                    </div>

                    <!-- Portion & Box -->
                    <div class="rounded-xl bg-white p-4 shadow space-y-3">
                        <div class="font-semibold">Portion & packaging</div>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <label>
                                Portion weight (g)
                                <input type="number" step="1" min="1" v-model.number="editingItem.portion_grams"
                                       class="w-full rounded border p-2" />
                            </label>
                            <label>
                                Units per box
                                <input type="number" step="1" min="1" v-model.number="editingItem.units_per_box"
                                       class="w-full rounded border p-2" />
                            </label>
                        </div>
                        <div class="text-xs text-gray-500 text-center">
                            1 kg → {{ editingItem.portion_grams > 0 ? (1000 / editingItem.portion_grams).toFixed(1) : '—' }} portions ·
                            box = {{ editingItem.portion_grams > 0 ? (editingItem.portion_grams * editingItem.units_per_box / 1000).toFixed(3) : '—' }} kg
                        </div>
                    </div>

                    <!-- Final -->
                    <div class="rounded-xl bg-white p-4 shadow text-center space-y-2">
                        <div class="text-gray-500 text-sm">Final price per kg</div>
                        <div class="text-3xl font-bold text-blue-600">{{ finalPrice }} €</div>
                        <div v-if="editingItem.portion_grams > 0" class="text-sm text-gray-500">
                            Per portion ({{ editingItem.portion_grams }}g):
                            {{ (finalPrice * editingItem.portion_grams / 1000).toFixed(4) }} € ·
                            Per box (×{{ editingItem.units_per_box }}):
                            {{ (finalPrice * editingItem.portion_grams / 1000 * editingItem.units_per_box).toFixed(4) }} €
                        </div>
                    </div>
                </template>
            </div>

            <!-- RIGHT: order items -->
            <div class="col-span-4">
                <div class="rounded-xl bg-white p-6 shadow space-y-4 sticky top-6">
                    <div class="font-semibold">Order items</div>

                    <div
                        v-for="(item, i) in orderItems"
                        :key="i"
                        class="border rounded p-2 cursor-pointer"
                        :class="editingIndex === i ? 'border-blue-500 bg-blue-50' : 'hover:border-gray-400'"
                        @click="editingIndex = i"
                    >
                        <div class="font-semibold text-sm">{{ item.product_name }}</div>
                        <div class="text-xs text-gray-500">{{ computeItemPrice(item) }} €/kg</div>
                        <div class="text-xs text-gray-500">
                            {{ item.portion_grams }}g × {{ item.units_per_box }} pcs/box
                        </div>
                        <ul class="text-xs mt-1 text-gray-600">
                            <li v-for="c in item.components" :key="c.id">
                                {{ c.name }} – {{ c.grams }} g
                            </li>
                        </ul>
                    </div>

                    <button class="w-full rounded bg-blue-600 py-2 text-white" @click="saveOrder">
                        Save order
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
