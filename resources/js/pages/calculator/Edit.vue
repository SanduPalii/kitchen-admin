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
    order: {
        id: number
        client_id: number
        location_id: number
        size: number
        items: {
            product_id: number
            final_price: number
            packaging_material: number
            production: number
            packaging: number
            transportation: number
            multi_delivery: number
            sell_percent: number
            components: object
        }[]
    }
    products: Product[]
    clients: { id: number; name: string }[]
    locations: { id: number; name: string; price: number }[]
}>()
console.log('props', props)
const TOTAL_WEIGHT = 1000
const step = 5

function cloneProduct(p?: Product | null): Product | null {
    if (!p) return null
    return { ...p, components: (p.components ?? []).map(c => ({ ...c })) }
}

const products = ref<Product[]>(props.products)

const selectedProductId = ref<number | null>(props.order.items[0]?.product_id ?? null)
const selectedProduct = ref<Product | null>(cloneProduct(
    products.value.find(p => p.id === selectedProductId.value)
))

const selectedClientId = ref<number | null>(props.order.client_id)
const selectedLocationId = ref<number | null>(props.order.location_id)

const orderItems = ref<OrderItem[]>(
    (props.order.items ?? []).map(item => {
        const product = products.value.find(p => p.id === item.product_id)!

        return {
            product_id: product.id,
            product_name: product.name,
            final_price: item.final_price,
            components: (item.components ?? []).map(c => {
                const original = product.components.find(pc => pc.id === c.component_id)!
                return {
                    id: original.id,
                    name: original.name,
                    grams: c.grams,
                    price_per_kg: c.price_per_kg,
                }
            })
        }
    })
)

const costs = ref({
    packaging_material: props.order.items[0]?.packaging_material ?? 0,
    production: props.order.items[0]?.production ?? 0,
    packaging: props.order.items[0]?.packaging ?? 0,
    transportation: props.order.items[0]?.transportation ?? 0,
    multi_delivery: props.order.items[0]?.multi_delivery ?? 0,
    sell_percent: props.order.items[0]?.sell_percent ?? 30,
})

watch(selectedProductId, (id) => {
    const p = products.value.find(p => p.id === id) ?? null
    selectedProduct.value = cloneProduct(p)
})

const freeGrams = computed(() => {
    if (!selectedProduct.value) return 0
    const used = selectedProduct.value.components.reduce((s, c) => s + (Number(c.grams) || 0), 0)
    return Math.max(0, TOTAL_WEIGHT - used)
})

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

const saveOrder = () => {
    router.put(`/calculator/${props.order.id}`, {
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
        })),
    }, {
        preserveScroll: true,
        onSuccess: () => {
            confirm.require({
                header: 'Updated',
                message: 'Order updated successfully!',
                icon: 'pi pi-check-circle',
                acceptLabel: 'OK',
            })
        }
    })
}
</script>

<template>
    <Head title="Edit calculator" />
    <AppLayout>
        <ConfirmDialog />

        <div class="grid grid-cols-12 gap-6 p-6">
            <!-- LEFT -->
            <div class="col-span-8 space-y-6">

                <div class="rounded-xl bg-white p-4 shadow">
                    <div class="font-semibold mb-2">Client</div>
                    <select v-model="selectedClientId" class="w-full rounded border p-2">
                        <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                </div>

                <div class="rounded-xl bg-white p-4 shadow">
                    <div class="font-semibold mb-2">Location</div>
                    <select v-model="selectedLocationId" class="w-full rounded border p-2">
                        <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }}</option>
                    </select>
                </div>

                <div class="rounded-xl bg-white p-4 shadow">
                    <div class="font-semibold mb-2">Product</div>
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
                    </div>

                    <div class="text-center mt-4">
                        <span class="rounded-full bg-gray-100 px-4 py-1 text-sm">
                            Price/kg: {{ pricePerKg.toFixed(2) }} €
                        </span>
                    </div>
                </div>

                <!-- Costs -->
                <div class="rounded-xl bg-white p-4 shadow space-y-3">
                    <div class="font-semibold">Costs</div>
                    <input v-model.number="costs.packaging_material" class="w-full border p-2 rounded" />
                    <input v-model.number="costs.production" class="w-full border p-2 rounded" />
                    <input v-model.number="costs.packaging" class="w-full border p-2 rounded" />
                    <input v-model.number="costs.transportation" class="w-full border p-2 rounded" />
                    <input v-model.number="costs.multi_delivery" class="w-full border p-2 rounded" />
                    <input v-model.number="costs.sell_percent" class="w-full border p-2 rounded" />
                </div>

                <div class="rounded-xl bg-white p-4 shadow text-center">
                    <div class="text-3xl font-bold text-blue-600">{{ finalPrice }} €</div>
                    <button class="mt-4 rounded bg-green-600 px-4 py-2 text-white" @click="saveOrder">
                        Update order
                    </button>
                </div>
            </div>

            <!-- RIGHT -->
            <div class="col-span-4">
                <div class="rounded-xl bg-white p-6 shadow space-y-4 sticky top-6">
                    <div class="font-semibold">Order items</div>

                    <div v-for="(item, i) in orderItems" :key="i" class="border rounded p-2">
                        <div class="font-semibold">{{ item.product_name }}</div>
                        <div class="text-xs text-gray-500">{{ item.final_price }} €</div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
