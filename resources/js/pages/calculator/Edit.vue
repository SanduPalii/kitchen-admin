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

const confirm = useConfirm()

const props = defineProps<{
    order: {
        id: number
        client_id: number
        location_id: number
        items: {
            product_id: number
            final_price: number
            packaging_material: number
            production: number
            packaging: number
            transportation: number
            multi_delivery: number
            sell_percent: number
            components: {
                component_id: number
                grams: number
                price_per_kg: number
            }[]
        }[]
    }
    products: Product[]
    clients: { id: number; name: string }[]
    locations: { id: number; name: string; price: number }[]
}>()
console.log(props)
const TOTAL_WEIGHT = 1000
const STEP = 10

const products = ref<Product[]>(props.products)
const selectedProductId = ref<number | null>(
    props.order.items?.[0]?.product_id
        ? Number(props.order.items[0].product_id)
        : null
)


function buildSelectedProduct(productId: number | null) {
    if (!productId) return null

    const product = products.value.find(p => p.id === productId)
    const orderItem = props.order.items.find(i => i.product_id === productId)

    if (!product) return null

    return {
        ...product,
        components: product.components.map(pc => {
            const fromOrder = orderItem?.components.find(c => c.component_id === pc.id)

            return {
                id: pc.id,
                name: pc.name,
                grams: fromOrder?.grams ?? pc.grams,
                price_per_kg: fromOrder?.price_per_kg ?? pc.price_per_kg,
            }
        })
    }
}

const selectedProduct = ref<Product | null>(buildSelectedProduct(selectedProductId.value))

const selectedClientId = ref<number | null>(props.order.client_id)
const selectedLocationId = ref<number | null>(props.order.location_id)

const costs = ref({
    packaging_material: props.order.items[0]?.packaging_material ?? 0,
    production: props.order.items[0]?.production ?? 0,
    packaging: props.order.items[0]?.packaging ?? 0,
    transportation: props.order.items[0]?.transportation ?? 0,
    multi_delivery: props.order.items[0]?.multi_delivery ?? 0,
    sell_percent: props.order.items[0]?.sell_percent ?? 30,
})

watch(selectedProductId, (id) => {
    selectedProduct.value = buildSelectedProduct(id)
})

const freeGrams = computed(() => {
    if (!selectedProduct.value) return 0
    const used = selectedProduct.value.components.reduce((s, c) => s + (c.grams || 0), 0)
    return Math.max(0, TOTAL_WEIGHT - used)
})

const decreaseComponent = (i: number) => {
    const c = selectedProduct.value!.components[i]
    c.grams = Math.max(0, c.grams - STEP)
}

const increaseComponent = (i: number) => {
    if (freeGrams.value <= 0) return
    const c = selectedProduct.value!.components[i]
    const add = Math.min(STEP, freeGrams.value)
    c.grams += add
}

const takeAllFree = (i: number) => {
    if (freeGrams.value <= 0) return
    selectedProduct.value!.components[i].grams += freeGrams.value
}

const pricePerKg = computed(() => {
    if (!selectedProduct.value) return 0
    return selectedProduct.value.components.reduce((sum, c) => {
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

const saveOrder = () => {
    if (!selectedProduct.value) return

    router.put(`/calculator/${props.order.id}`, {
        client_id: selectedClientId.value,
        location_id: selectedLocationId.value,
        size: 1,
        items: [
            {
                product_id: selectedProduct.value.id,
                final_price: finalPrice.value,
                packaging_material: costs.value.packaging_material,
                production: costs.value.production,
                packaging: costs.value.packaging,
                transportation: costs.value.transportation,
                multi_delivery: costs.value.multi_delivery,
                sell_percent: costs.value.sell_percent,
                components: selectedProduct.value.components.map(c => ({
                    component_id: c.id,
                    grams: c.grams,
                    price_per_kg: c.price_per_kg,
                })),
            }
        ]
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
        }
    })
}
</script>

<template>
    <Head title="Edit calculator" />
    <AppLayout>
        <ConfirmDialog />

        <div class="grid grid-cols-12 gap-6 p-6">
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
                        <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name_en }}</option>
                    </select>
                </div>

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
        </div>
    </AppLayout>
</template>
