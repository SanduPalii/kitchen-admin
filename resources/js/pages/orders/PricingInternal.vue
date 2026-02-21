<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { computed, ref } from 'vue'

type Item = {
    name: string
    type: string
    portion_grams: number
    product_cost: number
    add_costs: number
    selling_price: number
    sell_percent: number
}

const props = defineProps<{
    order: {
        id: number
        date: string
        client_name: string
        commission_pct: number
    }
    items: Item[]
}>()

const commissionPct = ref(props.order.commission_pct ?? 5)
const saving = ref(false)
const saved = ref(false)

function saveCommission() {
    saving.value = true
    saved.value = false
    router.patch(`/orders/${props.order.id}/commission`, {
        commission_pct: commissionPct.value,
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
    props.items.map(item => {
        const commission = item.selling_price * commissionPct.value / 100
        const margin = item.selling_price - commission - item.add_costs
        return {
            ...item,
            commission: commission,
            margin: margin,
        }
    })
)

const fmt = (v: number) => v.toFixed(4).replace('.', ',')
const fmt2 = (v: number) => v.toFixed(2).replace('.', ',')
</script>

<template>
    <Head :title="`Pricing Internal #${order.id}`" />

    <AppLayout>
        <div class="p-6 space-y-6">

            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold">Internal Pricing — Order #{{ order.id }}</h1>
                    <div class="text-sm text-gray-500 mt-1">
                        Client: <span class="font-medium text-gray-700">{{ order.client_name }}</span>
                        &nbsp;·&nbsp; Date: {{ order.date }}
                    </div>
                </div>

                <div class="flex items-center gap-2 bg-white border rounded-lg px-4 py-2 shadow-sm">
                    <label class="text-sm font-medium text-gray-600 whitespace-nowrap">Commission %</label>
                    <input
                        v-model.number="commissionPct"
                        type="number"
                        min="0"
                        max="100"
                        step="0.5"
                        class="w-20 rounded border px-2 py-1 text-sm text-center font-semibold"
                    />
                    <button
                        @click="saveCommission"
                        :disabled="saving"
                        class="rounded px-3 py-1 text-sm font-medium transition"
                        :class="saved
                            ? 'bg-green-100 text-green-700 border border-green-300'
                            : 'bg-purple-600 text-white hover:bg-purple-700 disabled:opacity-50'"
                    >
                        {{ saved ? 'Saved!' : saving ? '...' : 'Save' }}
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto rounded-lg border bg-white shadow">
                <table class="w-full text-sm border-collapse">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="border px-3 py-2 text-left font-semibold">Product</th>
                            <th class="border px-3 py-2 text-center font-semibold whitespace-nowrap">
                                Product<br><span class="text-xs text-gray-500 font-normal">(per portion)</span>
                            </th>
                            <th class="border px-3 py-2 text-center font-semibold whitespace-nowrap">
                                Add costs<br><span class="text-xs text-gray-500 font-normal">(food + extra)</span>
                            </th>
                            <th class="border px-3 py-2 text-center font-semibold whitespace-nowrap">
                                Selling Price<br><span class="text-xs text-gray-500 font-normal">(with markup)</span>
                            </th>
                            <th class="border px-3 py-2 text-center font-semibold whitespace-nowrap">
                                Commission<br><span class="text-xs text-gray-500 font-normal">({{ commissionPct }}%)</span>
                            </th>
                            <th class="border px-3 py-2 text-center font-semibold whitespace-nowrap">
                                Margin<br><span class="text-xs text-gray-500 font-normal">(SP − comm − costs)</span>
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="(row, i) in rows"
                            :key="i"
                            class="hover:bg-gray-50 transition"
                            :class="i % 2 === 0 ? 'bg-white' : 'bg-gray-50/40'"
                        >
                            <td class="border px-3 py-2">
                                <div class="font-medium">{{ row.name }}</div>
                                <div class="text-xs text-gray-400 mt-0.5">
                                    {{ row.portion_grams }}g
                                    <span v-if="row.type === 'vegan'" class="text-green-600 font-medium ml-1">vegan</span>
                                    <span v-else-if="row.type === 'vegetarian'" class="text-green-600 font-medium ml-1">vegetarian</span>
                                    &nbsp;·&nbsp; markup: {{ row.sell_percent }}%
                                </div>
                            </td>

                            <td class="border px-3 py-2 text-center font-mono">
                                {{ fmt(row.product_cost) }} €
                            </td>

                            <td class="border px-3 py-2 text-center font-mono">
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

            <div class="text-xs text-gray-400">
                Formulas: <b>Add costs</b> = (food cost + packaging + production + packaging + transport + multi-delivery) × portion/1000 &nbsp;|&nbsp;
                <b>Selling Price</b> = Add costs × (1 + markup%) &nbsp;|&nbsp;
                <b>Margin</b> = Selling Price − Commission − Add costs
            </div>

        </div>
    </AppLayout>
</template>
