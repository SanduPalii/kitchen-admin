<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const nf = (v: number, d = 2) => Number(v).toFixed(d).replace('.', ',')

const props = defineProps<{
    order: {
        id: number
        price: number
        size: number
        approved: boolean
        date: string
        client: { name: string } | null
        location: { name: string } | null
        products: {
            id: number
            name_en: string
            pivot: {
                price: number
                packaging_material_price: number
                production_price: number
                packaging_price: number
                transportation_price: number
                multi_delivery_price: number
                sell_percent: number
            }
            components: {
                id: number
                name: string
                grams: number
                price_per_kg: number
            }[]
        }[]
    }
}>()
</script>

<template>
    <Head :title="`Order #${order.id}`" />

    <AppLayout>
        <div class="p-6 space-y-6">

            <h1 class="text-xl font-bold">Order #{{ order.id }}</h1>

            <div class="grid grid-cols-2 gap-3">
                <div><b>Client:</b> {{ order.client?.name }}</div>
                <div><b>Location:</b> {{ order.location?.name }}</div>
                <div><b>Status:</b> {{ order.approved ? 'Approved' : 'Draft' }}</div>
                <div><b>Total:</b> {{ nf(order.price) }} €</div>
                <div><b>Date:</b> {{ order.date }}</div>
            </div>

            <div
                v-for="p in order.products"
                :key="p.id"
                class="border rounded p-4"
            >
                <div class="font-semibold text-lg mb-2">
                    {{ p.name_en }} — {{ nf(p.pivot.price) }} €
                </div>

                <div class="text-sm text-gray-600 mb-2">
                    Packaging: {{ nf(p.pivot.packaging_price) }} |
                    Production: {{ nf(p.pivot.production_price) }} |
                    Transport: {{ nf(p.pivot.transportation_price) }} |
                    Margin: {{ nf(p.pivot.sell_percent) }}%
                </div>

                <table class="w-full text-sm border">
                    <thead>
                    <tr class="bg-gray-100">
                        <th class="border p-2 text-left">Component</th>
                        <th class="border p-2">Grams</th>
                        <th class="border p-2">€/kg</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="c in p.components" :key="c.id">
                        <td class="border p-2">{{ c.name }}</td>
                        <td class="border p-2 text-center">{{ c.grams }} g</td>
                        <td class="border p-2 text-center">{{ nf(c.price_per_kg) }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </AppLayout>
</template>
