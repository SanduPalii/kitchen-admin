<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { computed, ref } from 'vue'

type Item = {
    name: string
    type: string
    portion_grams: number
    food_cost_per_kg: number
}

const props = defineProps<{
    order: {
        id: number
        date: string
        client_name: string
        commission_pct: number
        packaging_material: number
        production: number
        packaging: number
        transportation: number
        multi_delivery: number
        sell_percent: number
    }
    items: Item[]
}>()

const commissionPct = ref(props.order.commission_pct ?? 5)
const costs = ref({
    packaging_material: props.order.packaging_material ?? 0.45,
    production:         props.order.production         ?? 0.12,
    packaging:          props.order.packaging          ?? 0.08,
    transportation:     props.order.transportation     ?? 0.45,
    multi_delivery:     props.order.multi_delivery     ?? 0.12,
    sell_percent:       props.order.sell_percent       ?? 30,
})

const saving = ref(false)
const saved  = ref(false)

function saveSettings() {
    saving.value = true
    saved.value  = false
    router.patch(`/orders/${props.order.id}/settings`, {
        commission_pct:     commissionPct.value,
        packaging_material: costs.value.packaging_material,
        production:         costs.value.production,
        packaging:          costs.value.packaging,
        transportation:     costs.value.transportation,
        multi_delivery:     costs.value.multi_delivery,
        sell_percent:       costs.value.sell_percent,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            saved.value = true
            setTimeout(() => { saved.value = false }, 2000)
        },
        onFinish: () => { saving.value = false },
    })
}

const rows = computed(() =>
    (props.items ?? []).map(item => {
        const additionalPerKg =
            costs.value.packaging_material +
            costs.value.production +
            costs.value.packaging +
            costs.value.transportation +
            costs.value.multi_delivery

        const productCost  = item.food_cost_per_kg * item.portion_grams / 1000
        const addCosts     = (item.food_cost_per_kg + additionalPerKg) * item.portion_grams / 1000
        const sellingPrice = addCosts * (1 + costs.value.sell_percent / 100)
        const commission   = sellingPrice * commissionPct.value / 100
        const margin       = sellingPrice - commission - addCosts

        return { ...item, product_cost: productCost, add_costs: addCosts, selling_price: sellingPrice, commission, margin }
    })
)

const fmt  = (v: number) => Number(v).toFixed(4).replace('.', ',')
const fmt2 = (v: number) => Number(v).toFixed(2).replace('.', ',')
</script>

<template>
    <Head :title="`Pricing Internal #${order.id}`" />

    <AppLayout>
        <div class="p-6 space-y-4">

            <!-- Header -->
            <div>
                <h1 class="text-xl font-bold">Internal Pricing — Order #{{ order.id }}</h1>
                <div class="text-sm text-gray-500 mt-1">
                    Client: <span class="font-medium text-gray-700">{{ order.client_name }}</span>
                    &nbsp;·&nbsp; Date: {{ order.date }}
                </div>
            </div>

            <!-- Settings panel -->
            <div class="rounded-xl bg-white border shadow-sm p-4 space-y-3">
                <div class="text-sm font-semibold text-gray-700">Settings</div>

                <div class="grid grid-cols-2 gap-3 text-xs sm:grid-cols-4 xl:grid-cols-7">
                    <!-- Commission -->
                    <label class="flex flex-col gap-1">
                        <span class="text-gray-500 font-medium">Commission %</span>
                        <input v-model.number="commissionPct" type="number" min="0" max="100" step="0.5"
                               class="rounded border px-2 py-1.5 text-center font-semibold focus:outline-none focus:border-purple-400" />
                    </label>

                    <!-- Additional costs -->
                    <label class="flex flex-col gap-1">
                        <span class="text-gray-500 font-medium">Pkg. mat. €</span>
                        <input v-model.number="costs.packaging_material" type="number" step="0.01" min="0"
                               class="rounded border px-2 py-1.5 text-center focus:outline-none focus:border-blue-400" />
                    </label>
                    <label class="flex flex-col gap-1">
                        <span class="text-gray-500 font-medium">Production €</span>
                        <input v-model.number="costs.production" type="number" step="0.01" min="0"
                               class="rounded border px-2 py-1.5 text-center focus:outline-none focus:border-blue-400" />
                    </label>
                    <label class="flex flex-col gap-1">
                        <span class="text-gray-500 font-medium">Packaging €</span>
                        <input v-model.number="costs.packaging" type="number" step="0.01" min="0"
                               class="rounded border px-2 py-1.5 text-center focus:outline-none focus:border-blue-400" />
                    </label>
                    <label class="flex flex-col gap-1">
                        <span class="text-gray-500 font-medium">Transport €</span>
                        <input v-model.number="costs.transportation" type="number" step="0.01" min="0"
                               class="rounded border px-2 py-1.5 text-center focus:outline-none focus:border-blue-400" />
                    </label>
                    <label class="flex flex-col gap-1">
                        <span class="text-gray-500 font-medium">Multi del. €</span>
                        <input v-model.number="costs.multi_delivery" type="number" step="0.01" min="0"
                               class="rounded border px-2 py-1.5 text-center focus:outline-none focus:border-blue-400" />
                    </label>
                    <label class="flex flex-col gap-1">
                        <span class="text-gray-500 font-medium">Markup %</span>
                        <input v-model.number="costs.sell_percent" type="number" step="1" min="0"
                               class="rounded border px-2 py-1.5 text-center focus:outline-none focus:border-blue-400" />
                    </label>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <p class="text-xs text-gray-400">Changes update the table in real time. Press Save to persist.</p>
                    <button
                        @click="saveSettings"
                        :disabled="saving"
                        class="rounded px-4 py-1.5 text-sm font-medium transition"
                        :class="saved
                            ? 'bg-green-100 text-green-700 border border-green-300'
                            : 'bg-purple-600 text-white hover:bg-purple-700 disabled:opacity-50'"
                    >
                        {{ saved ? '✓ Saved' : saving ? '...' : 'Save' }}
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto rounded-xl border bg-white shadow">
                <table class="w-full text-sm border-collapse">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="border px-3 py-2 text-left font-semibold">Product</th>
                            <th class="border px-3 py-2 text-center font-semibold whitespace-nowrap">
                                Food cost<br><span class="text-xs text-gray-400 font-normal">(per portion)</span>
                            </th>
                            <th class="border px-3 py-2 text-center font-semibold whitespace-nowrap">
                                Add costs<br><span class="text-xs text-gray-400 font-normal">(food + extra)</span>
                            </th>
                            <th class="border px-3 py-2 text-center font-semibold whitespace-nowrap">
                                Selling Price<br><span class="text-xs text-gray-400 font-normal">(with markup)</span>
                            </th>
                            <th class="border px-3 py-2 text-center font-semibold whitespace-nowrap">
                                Commission<br><span class="text-xs text-gray-400 font-normal">({{ fmt2(commissionPct) }}%)</span>
                            </th>
                            <th class="border px-3 py-2 text-center font-semibold whitespace-nowrap">
                                Margin<br><span class="text-xs text-gray-400 font-normal">(SP − comm − costs)</span>
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="(row, i) in rows"
                            :key="i"
                            class="hover:bg-blue-50/40 transition"
                            :class="i % 2 === 0 ? 'bg-white' : 'bg-gray-50/40'"
                        >
                            <td class="border px-3 py-2">
                                <div class="font-medium">{{ row.name }}</div>
                                <div class="text-xs text-gray-400 mt-0.5">
                                    {{ row.portion_grams }}g
                                    <span v-if="row.type === 'vegan'" class="text-green-600 font-medium ml-1">vegan</span>
                                    <span v-else-if="row.type === 'vegetarian'" class="text-green-600 font-medium ml-1">vegetarian</span>
                                </div>
                            </td>
                            <td class="border px-3 py-2 text-center font-mono text-gray-700">
                                {{ fmt(row.product_cost) }} €
                            </td>
                            <td class="border px-3 py-2 text-center font-mono text-gray-700">
                                {{ fmt(row.add_costs) }} €
                            </td>
                            <td class="border px-3 py-2 text-center font-mono font-semibold text-blue-700">
                                {{ fmt(row.selling_price) }} €
                            </td>
                            <td class="border px-3 py-2 text-center font-mono text-orange-600">
                                {{ fmt(row.commission) }} €
                            </td>
                            <td
                                class="border px-3 py-2 text-center font-mono font-semibold"
                                :class="row.margin >= 0 ? 'text-green-700' : 'text-red-600'"
                            >
                                {{ fmt(row.margin) }} €
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p class="text-xs text-gray-400">
                <b>Add costs</b> = (food + pkg.mat + production + packaging + transport + multi-del) × portion/1000 &nbsp;|&nbsp;
                <b>Selling Price</b> = Add costs × (1 + markup%) &nbsp;|&nbsp;
                <b>Margin</b> = SP − Commission − Add costs
            </p>

        </div>
    </AppLayout>
</template>
