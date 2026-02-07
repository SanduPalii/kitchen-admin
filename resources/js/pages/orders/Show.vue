<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    order: {
        id: number;
        price: number;
        size: number;
        approved: boolean;
        date: string;
        client: { name: string };
        location: { name: string };
        products: {
            id: number;
            name_en: string;
            pivot: {
                price: number;
                production_price: number;
                packaging_price: number;
                transportation_price: number;
                multi_delivery_price: number;
                sell_percent: number;
            };
        }[];
    };
}>();
</script>

<template>
    <Head :title="`Order #${order.id}`" />
    <AppLayout>
        <div class="p-4 space-y-4">
            <h1 class="text-xl font-bold">Order #{{ order.id }}</h1>

            <div class="grid grid-cols-2 gap-4">
                <div><b>Client:</b> {{ order.client?.name }}</div>
                <div><b>Location:</b> {{ order.location?.name }}</div>
                <div><b>Size:</b> {{ order.size }}</div>
                <div><b>Status:</b> {{ order.approved ? 'Approved' : 'Draft' }}</div>
                <div><b>Total price:</b> {{ order.price }}</div>
                <div><b>Date:</b> {{ order.date }}</div>
            </div>

            <h2 class="font-semibold mt-4">Products</h2>

            <table class="w-full border">
                <thead>
                <tr class="bg-gray-100">
                    <th class="border p-2">Product</th>
                    <th class="border p-2">Price</th>
                    <th class="border p-2">Sell %</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="p in order.products" :key="p.id">
                    <td class="border p-2">{{ p.name_en }}</td>
                    <td class="border p-2">{{ p.pivot.price }}</td>
                    <td class="border p-2">{{ p.pivot.sell_percent }}%</td>
                </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
