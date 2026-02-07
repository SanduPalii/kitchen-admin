<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    orders: {
        id: number;
        price: number;
        size: number;
        approved: boolean;
        date: string;
        client: { id: number; name: string };
        location: { id: number; name: string };
    }[];
}>();
</script>

<template>
    <Head title="Orders" />
    <AppLayout>
        <div class="p-4 space-y-4">
            <h1 class="text-xl font-bold">Orders</h1>

            <table class="w-full border">
                <thead>
                <tr class="bg-gray-100">
                    <th class="border p-2">#</th>
                    <th class="border p-2">Client</th>
                    <th class="border p-2">Location</th>
                    <th class="border p-2">Size</th>
                    <th class="border p-2">Price</th>
                    <th class="border p-2">Status</th>
                    <th class="border p-2"></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="o in orders" :key="o.id">
                    <td class="border p-2">{{ o.id }}</td>
                    <td class="border p-2">{{ o.client?.name }}</td>
                    <td class="border p-2">{{ o.location?.name }}</td>
                    <td class="border p-2">{{ o.size }}</td>
                    <td class="border p-2">{{ o.price }}</td>
                    <td class="border p-2">
                        <span :class="o.approved ? 'text-green-600' : 'text-gray-400'">
                            {{ o.approved ? 'Approved' : 'Draft' }}
                        </span>
                    </td>
                    <td class="border p-2">
                        <button
                            class="text-blue-600 underline"
                            @click="router.visit(`/orders/${o.id}`)"
                        >
                            View
                        </button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
