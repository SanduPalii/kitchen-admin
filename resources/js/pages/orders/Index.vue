<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import ConfirmDialog from 'primevue/confirmdialog'
import { useConfirm } from 'primevue/useconfirm'
import { PencilIcon, Trash2Icon } from 'lucide-vue-next'

const confirm = useConfirm()

const props = defineProps<{
    orders: {
        id: number
        price: number
        size: number
        approved: boolean
        date: string
        client: { id: number; name: string } | null
        location: { id: number; name: string } | null
    }[]
}>()

const editOrder = (id: number) => {
    router.visit(`/calculator/${id}/edit`)
}

const deleteOrder = (id: number) => {
    confirm.require({
        header: 'Delete order',
        message: `Are you sure you want to delete order #${id}? This action cannot be undone.`,
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        acceptLabel: 'Delete',
        rejectLabel: 'Cancel',
        accept: () => {
            router.delete(`/orders/${id}`, {
                preserveScroll: true,
            })
        },
    })
}
</script>

<template>
    <Head title="Orders" />
    <AppLayout>
        <ConfirmDialog />

        <div class="p-6 space-y-4">
            <h1 class="text-xl font-bold">Orders</h1>

            <div class="overflow-x-auto rounded-lg border bg-white shadow">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100">
                    <tr>
                        <th class="border p-2">#</th>
                        <th class="border p-2 text-left">Client</th>
                        <th class="border p-2 text-left">Location</th>
                        <th class="border p-2 text-center">Size</th>
                        <th class="border p-2 text-center">Price</th>
                        <th class="border p-2 text-center">Status</th>
                        <th class="border p-2 text-center w-[120px]">Actions</th>
                    </tr>
                    </thead>

                    <tbody>
                    <tr
                        v-for="o in orders"
                        :key="o.id"
                        class="hover:bg-gray-50 transition"
                    >
                        <td class="border p-2 text-center">{{ o.id }}</td>
                        <td class="border p-2">{{ o.client?.name ?? '—' }}</td>
                        <td class="border p-2">{{ o.location?.name ?? '—' }}</td>
                        <td class="border p-2 text-center">{{ o.size }}</td>
                        <td class="border p-2 text-center font-semibold">{{ o.price }} €</td>
                        <td class="border p-2 text-center">
                            <span
                                class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium"
                                :class="o.approved
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-gray-100 text-gray-500'"
                            >
                                {{ o.approved ? 'Approved' : 'Draft' }}
                            </span>
                        </td>
                        <td class="border p-2">
                            <div class="flex items-center justify-center gap-3">
                                <!-- EDIT -->
                                <button
                                    class="text-blue-600 hover:text-blue-800 transition"
                                    title="Edit order"
                                    @click="editOrder(o.id)"
                                >
                                    <PencilIcon class="w-5 h-5" />
                                </button>

                                <!-- DELETE -->
                                <button
                                    class="text-red-600 hover:text-red-800 transition"
                                    title="Delete order"
                                    @click="deleteOrder(o.id)"
                                >
                                    <Trash2Icon class="w-5 h-5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
