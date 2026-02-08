<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
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
    price_per_kg: number
    components: Component[]
}

const props = defineProps<{
    products: Product[]
    clients: { id: number; name: string }[]
    locations: { id: number; name: string; price: number }[]
}>()

const products = ref(props.products)
const selectedProduct = ref<Product>(products.value[0])

const TOTAL_WEIGHT = 1000
const step = 5

const costs = ref({
    packaging_material: 0.45,
    production: 0.12,
    packaging: 0.08,
    transportation: 0.45,
    multi_delivery: 0.12,
    sell_percent: 30,
})

/**
 * Свободные граммы
 */
const freeGrams = computed(() => {
    const used = selectedProduct.value.components.reduce((s, c) => s + c.grams, 0)
    return Math.max(0, TOTAL_WEIGHT - used)
})

/**
 * Уменьшить компонент (освобождает граммы)
 */
const decreaseComponent = (index: number, delta: number) => {
    const c = selectedProduct.value.components[index]
    const newValue = c.grams - delta
    if (newValue < 0) return
    c.grams = newValue
}

/**
 * Увеличить компонент (забирает свободные граммы)
 */
const increaseComponent = (index: number, delta: number) => {
    if (freeGrams.value <= 0) return
    const c = selectedProduct.value.components[index]
    const add = Math.min(delta, freeGrams.value)
    c.grams += add
}

/**
 * Забрать ВСЕ свободные граммы
 */
const takeAllFree = (index: number) => {
    if (freeGrams.value <= 0) return
    const c = selectedProduct.value.components[index]
    c.grams += freeGrams.value
}

/**
 * Цена за 1 кг продукта
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
    return (base + percent).toFixed(2)
})
</script>

<template>
    <Head title="Calculator" />

    <AppLayout>
        <div class="grid grid-cols-12 gap-6 p-6">

            <!-- LEFT -->
            <div class="col-span-8 space-y-6">

                <!-- Select product -->
                <div class="rounded-xl bg-white p-4 shadow">
                    <div class="font-semibold mb-2">Select product</div>
                    <select v-model="selectedProduct" class="w-full rounded border p-2">
                        <option v-for="p in products" :key="p.id" :value="p">
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
                            <div class="text-xs text-gray-400">{{ c.price_per_kg }} € / kg</div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button
                                class="h-8 w-8 rounded-full border"
                                @click="decreaseComponent(i, step)"
                            >-</button>

                            <button
                                class="h-8 w-8 rounded-full border"
                                :disabled="freeGrams <= 0"
                                @click="increaseComponent(i, step)"
                            >+</button>

                            <button
                                class="h-8 w-8 rounded-full border text-yellow-600"
                                :disabled="freeGrams <= 0"
                                title="Take all free grams"
                                @click="takeAllFree(i)"
                            >
                                ⚡
                            </button>
                        </div>
                    </div>

                    <div class="text-center mt-4">
            <span class="rounded-full bg-gray-100 px-4 py-1 text-sm">
              Free grams: {{ freeGrams }} g · Price per kg: {{ pricePerKg.toFixed(2) }} €
            </span>
                    </div>
                </div>

                <!-- Costs -->
                <div class="rounded-xl bg-white p-4 shadow space-y-3">
                    <div class="font-semibold">Auxiliary Costs</div>

                    <div class="grid grid-cols-3 gap-3">
                        <input v-model.number="costs.packaging_material" class="input" placeholder="Packaging material €" />
                        <input v-model.number="costs.production" class="input" placeholder="Production €" />
                        <input v-model.number="costs.packaging" class="input" placeholder="Packaging €" />
                        <input v-model.number="costs.transportation" class="input" placeholder="Transportation €" />
                        <input v-model.number="costs.multi_delivery" class="input" placeholder="Multi delivery €" />
                        <input v-model.number="costs.sell_percent" class="input" placeholder="Sell %" />
                    </div>
                </div>
            </div>

            <!-- RIGHT -->
            <div class="col-span-4">
                <div class="rounded-xl bg-white p-6 shadow space-y-4 sticky top-6">
                    <div class="text-center text-sm text-gray-500">FINAL PRICE</div>
                    <div class="text-center text-4xl font-bold text-blue-600">
                        {{ finalPrice }} €
                    </div>

                    <div class="flex gap-2">
                        <button class="w-full rounded border py-2">Preview</button>
                        <button class="w-full rounded bg-blue-600 py-2 text-white">
                            Export PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
