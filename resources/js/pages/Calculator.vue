<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
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

const props = defineProps<{
    products: Product[]
    clients: { id: number; name: string }[]
    locations: { id: number; name: string; price: number }[]
}>()

const TOTAL_WEIGHT = 1000
const step = 5

const products = ref(props.products)
const selectedProductId = ref<number>(products.value[0]?.id)
const selectedProduct = ref<Product>(cloneProduct(products.value[0]))
const selectedClientId = ref<number | null>(null)

const orderItems = ref<OrderItem[]>([])

const costs = ref({
    packaging_material: 0.45,
    production: 0.12,
    packaging: 0.08,
    transportation: 0.45,
    multi_delivery: 0.12,
    sell_percent: 30,
})

function cloneProduct(p: Product): Product {
    return {
        ...p,
        components: p.components.map(c => ({ ...c })),
    }
}

/**
 * Смена продукта → обновляем компоненты
 */
watch(selectedProductId, (id) => {
    const p = products.value.find(p => p.id === id)
    if (p) {
        selectedProduct.value = cloneProduct(p)
    }
})

/**
 * Свободные граммы
 */
const freeGrams = computed(() => {
    const used = selectedProduct.value.components.reduce((s, c) => s + c.grams, 0)
    return Math.max(0, TOTAL_WEIGHT - used)
})

const decreaseComponent = (i: number, delta: number) => {
    const c = selectedProduct.value.components[i]
    c.grams = Math.max(0, c.grams - delta)
}

const increaseComponent = (i: number, delta: number) => {
    if (freeGrams.value <= 0) return
    const c = selectedProduct.value.components[i]
    const add = Math.min(delta, freeGrams.value)
    c.grams += add
}

const takeAllFree = (i: number) => {
    if (freeGrams.value <= 0) return
    selectedProduct.value.components[i].grams += freeGrams.value
}

/**
 * Цена за 1 кг
 */
const pricePerKg = computed(() => {
    return selectedProduct.value.components.reduce((sum, c) => {
        return sum + (c.price_per_kg * c.grams) / 1000
    }, 0)
})

/**
 * Финальная цена
 */
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

/**
 * Добавить товар в заказ
 */
const addToOrder = () => {
    if (!selectedClientId.value) {
        alert('Выберите клиента')
        return
    }

    orderItems.value.push({
        product_id: selectedProduct.value.id,
        product_name: selectedProduct.value.name,
        components: selectedProduct.value.components.map(c => ({ ...c })),
        final_price: finalPrice.value,
    })
}
</script>

<template>
    <Head title="Calculator" />
    <AppLayout>
        <div class="grid grid-cols-12 gap-6 p-6">

            <!-- LEFT -->
            <div class="col-span-8 space-y-6">

                <!-- Client -->
                <div class="rounded-xl bg-white p-4 shadow">
                    <div class="font-semibold mb-2">Select client</div>
                    <select v-model="selectedClientId" class="w-full rounded border p-2">
                        <option :value="null" disabled>Select client</option>
                        <option v-for="c in clients" :key="c.id" :value="c.id">
                            {{ c.name }}
                        </option>
                    </select>
                </div>

                <!-- Product -->
                <div class="rounded-xl bg-white p-4 shadow">
                    <div class="font-semibold mb-2">Select product</div>
                    <select v-model="selectedProductId" class="w-full rounded border p-2">
                        <option v-for="p in products" :key="p.id" :value="p.id">
                            {{ p.name }}
                        </option>
                    </select>
                </div>

                <!-- Components -->
                <div class="rounded-xl bg-white p-4 shadow space-y-4">
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
                                title="Забрать все свободные граммы"
                            >⚡</button>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <span class="rounded-full bg-gray-100 px-4 py-1 text-sm">
                            Free grams: {{ freeGrams }} g · Price/kg: {{ pricePerKg.toFixed(2) }} €
                        </span>
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
            </div>

            <!-- RIGHT -->
            <div class="col-span-4">
                <div class="rounded-xl bg-white p-6 shadow space-y-4 sticky top-6">
                    <div class="font-semibold">Order items</div>

                    <div
                        v-for="(item, i) in orderItems"
                        :key="i"
                        class="border rounded p-2"
                    >
                        <div class="font-semibold">{{ item.product_name }}</div>
                        <div class="text-xs text-gray-500">
                            {{ item.final_price }} €
                        </div>

                        <ul class="text-xs mt-1">
                            <li v-for="c in item.components" :key="c.id">
                                {{ c.name }} – {{ c.grams }} g
                            </li>
                        </ul>
                    </div>

                    <button class="w-full rounded bg-blue-600 py-2 text-white">
                        Save order
                    </button>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
