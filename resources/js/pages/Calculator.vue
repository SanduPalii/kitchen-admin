<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import ConfirmDialog from 'primevue/confirmdialog'
import { useConfirm } from 'primevue/useconfirm'
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
}

const confirm = useConfirm()

const props = defineProps<{
    products: Product[]
    clients: { id: number; name: string }[]
    locations: { id: number; name: string; price: number }[]
}>()

const TOTAL_WEIGHT = 1000
const step = 5

const products = ref<Product[]>(props.products ?? [])

const selectedProductId = ref<number | null>(products.value.length ? products.value[0].id : null)
const selectedProduct = ref<Product | null>(cloneProduct(products.value[0] ?? null))

const selectedClientId = ref<number | null>(null)
const selectedLocationId = ref<number | null>(props.locations?.[0]?.id ?? null)

const orderItems = ref<OrderItem[]>([])

const costs = ref({
    packaging_material: 0.45,
    production: 0.12,
    packaging: 0.08,
    transportation: 0.45,
    multi_delivery: 0.12,
    sell_percent: 30,
})

function cloneProduct(p?: Product | null): Product | null {
    if (!p) return null
    return { ...p, components: (p.components ?? []).map(c => ({ ...c })) }
}

watch(selectedProductId, (id) => {
    const p = products.value.find(p => p.id === id) ?? null
    selectedProduct.value = cloneProduct(p)
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

const pricePerKg = computed(() => {
    if (!selectedProduct.value) return 0
    return selectedProduct.value.components.reduce((sum, c) => {
        return sum + ((Number(c.price_per_kg) || 0) * (Number(c.grams) || 0)) / 1000
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

    orderItems.value.push({
        product_id: selectedProduct.value.id,
        product_name: selectedProduct.value.name,
        components: selectedProduct.value.components.map(c => ({ ...c })),
        final_price: finalPrice.value,
    })
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
        items: orderItems.value.map(i => ({
            product_id: i.product_id,
            final_price: i.final_price,

            packaging_material: costs.value.packaging_material,
            production: costs.value.production,
            packaging: costs.value.packaging,
            transportation: costs.value.transportation,
            multi_delivery: costs.value.multi_delivery,
            sell_percent: costs.value.sell_percent,

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

        <div class="grid grid-cols-12 gap-6 p-6">
            <!-- LEFT -->
            <div class="col-span-8 space-y-6">
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

                <template v-else>
                    <!-- Client -->
                    <div class="rounded-xl bg-white p-4 shadow">
                        <div class="font-semibold mb-2">Select client</div>
                        <select v-model="selectedClientId" class="w-full rounded border p-2">
                            <option :value="null" disabled>Select client</option>
                            <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>

                    <!-- Location -->
                    <div class="rounded-xl bg-white p-4 shadow">
                        <div class="font-semibold mb-2">Select location</div>
                        <select v-model="selectedLocationId" class="w-full rounded border p-2">
                            <option :value="null" disabled>Select location</option>
                            <option v-for="l in locations" :key="l.id" :value="l.id">
                                {{ l.name }} ({{ l.price }} €)
                            </option>
                        </select>
                    </div>

                    <!-- Product -->
                    <div class="rounded-xl bg-white p-4 shadow">
                        <div class="font-semibold mb-2">Select product</div>
                        <select v-model="selectedProductId" class="w-full rounded border p-2">
                            <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                    </div>

                    <!-- Components -->
                    <div v-if="selectedProduct" class="rounded-xl bg-white p-4 shadow space-y-4">
                        <div
                            v-for="(c, i) in selectedProduct.components"
                            :key="c.id"
                            class="flex items-center justify-between border-b pb-3"
                        >
                            <div>
                                <div class="text-sm uppercase text-gray-500">{{ c.name }}</div>
                                <div class="text-xl font-semibold">{{ c.grams }} g</div>
                            </div>

                            <div class="flex items-center gap-2">
                                <button class="h-8 w-8 rounded-full border" @click="decreaseComponent(i, step)">-</button>
                                <button class="h-8 w-8 rounded-full border" @click="increaseComponent(i, step)">+</button>
                                <button
                                    v-if="freeGrams > 0"
                                    class="h-8 w-8 rounded-full border text-yellow-600"
                                    @click="takeAllFree(i)"
                                    title="Take all free grams"
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
                        <div class="font-semibold">Additional costs</div>

                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <label>
                                Packaging material
                                <input type="number" step="0.01" v-model.number="costs.packaging_material"
                                       class="w-full rounded border p-2" />
                            </label>

                            <label>
                                Production
                                <input type="number" step="0.01" v-model.number="costs.production"
                                       class="w-full rounded border p-2" />
                            </label>

                            <label>
                                Packaging
                                <input type="number" step="0.01" v-model.number="costs.packaging"
                                       class="w-full rounded border p-2" />
                            </label>

                            <label>
                                Transportation
                                <input type="number" step="0.01" v-model.number="costs.transportation"
                                       class="w-full rounded border p-2" />
                            </label>

                            <label>
                                Multi delivery
                                <input type="number" step="0.01" v-model.number="costs.multi_delivery"
                                       class="w-full rounded border p-2" />
                            </label>

                            <label>
                                Sell percent (%)
                                <input type="number" step="1" v-model.number="costs.sell_percent"
                                       class="w-full rounded border p-2" />
                            </label>
                        </div>
                    </div>


                    <!-- Final -->
                    <div class="rounded-xl bg-white p-4 shadow text-center space-y-3">
                        <div class="text-gray-500 text-sm">Final price</div>
                        <div class="text-3xl font-bold text-blue-600">{{ finalPrice }} €</div>

                        <button class="rounded bg-green-600 px-4 py-2 text-white" @click="addToOrder">
                            ➕ Add to order
                        </button>
                    </div>
                </template>
            </div>

            <!-- RIGHT -->
            <div class="col-span-4">
                <div class="rounded-xl bg-white p-6 shadow space-y-4 sticky top-6">
                    <div class="font-semibold">Order items</div>

                    <div v-if="!orderItems.length" class="text-sm text-gray-500">
                        No items yet.
                    </div>

                    <div v-for="(item, i) in orderItems" :key="i" class="border rounded p-2">
                        <div class="font-semibold">{{ item.product_name }}</div>
                        <div class="text-xs text-gray-500">{{ item.final_price }} €</div>

                        <ul class="text-xs mt-1">
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
