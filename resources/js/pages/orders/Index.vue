<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import ConfirmDialog from 'primevue/confirmdialog'
import { useConfirm } from 'primevue/useconfirm'
import {
    PencilIcon, Trash2Icon, EyeIcon, DownloadIcon,
    TableIcon, CopyIcon, CheckCircleIcon,
} from 'lucide-vue-next'
import DatePicker from 'primevue/datepicker'
import { computed, ref } from 'vue'

const confirm = useConfirm()
const nf = (v: number, d = 2) => Number(v).toFixed(d).replace('.', ',')

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

// ── Filters ──────────────────────────────────────────────
const filterClientId  = ref<number | null>(null)
const filterDateRange = ref<Date[] | null>(null)
const filterStatus    = ref<'all' | 'draft' | 'approved'>('all')

const toISO = (d: Date | null) => {
    if (!d) return ''
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
}

const filterFrom = computed(() => toISO(filterDateRange.value?.[0] ?? null))
const filterTo   = computed(() => toISO(filterDateRange.value?.[1] ?? null))

const hasFilters = computed(() =>
    !!(filterClientId.value || filterDateRange.value?.length || filterStatus.value !== 'all')
)

const clearFilters = () => {
    filterClientId.value  = null
    filterDateRange.value = null
    filterStatus.value    = 'all'
}

const uniqueClients = computed(() => {
    const seen = new Set<number>()
    return props.orders
        .filter(o => o.client && !seen.has(o.client.id) && seen.add(o.client.id))
        .map(o => o.client!)
})

const filteredOrders = computed(() =>
    props.orders.filter(o => {
        if (filterClientId.value && o.client?.id !== filterClientId.value) return false
        const date = o.date.slice(0, 10)
        if (filterFrom.value && date < filterFrom.value) return false
        if (filterTo.value   && date > filterTo.value)   return false
        if (filterStatus.value === 'approved' && !o.approved) return false
        if (filterStatus.value === 'draft'    &&  o.approved) return false
        return true
    })
)

// ── Actions ───────────────────────────────────────────────
const editOrder = (id: number) => router.visit(`/calculator/${id}/edit`)

const duplicateOrder = (id: number) =>
    router.post(`/orders/${id}/duplicate`, {}, { preserveScroll: true })

const approveOrder = (id: number) => {
    confirm.require({
        header: 'Approve order',
        message:
            'Once approved, this order will be locked — it cannot be edited or deleted. This action cannot be undone.',
        icon: 'pi pi-shield',
        acceptClass: 'p-button-success',
        acceptLabel: 'Approve',
        rejectLabel: 'Cancel',
        accept: () => router.patch(`/orders/${id}/approve`, {}, { preserveScroll: true }),
    })
}

const deleteOrder = (id: number) => {
    confirm.require({
        header: 'Delete order',
        message: `Are you sure you want to delete order #${id}? This action cannot be undone.`,
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        acceptLabel: 'Delete',
        rejectLabel: 'Cancel',
        accept: () => router.delete(`/orders/${id}`, { preserveScroll: true }),
    })
}
</script>

<template>
    <Head title="Orders" />
    <AppLayout>
        <ConfirmDialog />

        <div class="space-y-4 p-6">

            <div class="flex flex-wrap items-center justify-between gap-2">
                <h1 class="text-xl font-bold">Orders</h1>
                <a
                    href="/calculator"
                    class="rounded bg-blue-600 px-4 py-2 text-sm text-white transition hover:bg-blue-700"
                >
                    + New order
                </a>
            </div>

            <!-- ── Filters ── -->
            <div class="flex flex-wrap items-end gap-3 rounded-lg border bg-white p-4 shadow">

                <div class="flex w-full flex-col gap-1 text-sm sm:w-auto">
                    <label class="font-medium text-gray-500">Client</label>
                    <select v-model="filterClientId" class="w-full rounded border px-2 py-1.5 sm:min-w-[160px]">
                        <option :value="null">All clients</option>
                        <option v-for="c in uniqueClients" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                </div>

                <div class="flex w-full flex-col gap-1 text-sm sm:w-auto">
                    <label class="font-medium text-gray-500">Date range</label>
                    <DatePicker
                        v-model="filterDateRange"
                        selectionMode="range"
                        dateFormat="dd.mm.yy"
                        placeholder="Select range..."
                        showIcon
                        showButtonBar
                        :manualInput="false"
                        inputClass="!py-1.5 !text-sm !w-full sm:!w-52"
                    />
                </div>

                <div class="flex w-full flex-col gap-1 text-sm sm:w-auto">
                    <label class="font-medium text-gray-500">Status</label>
                    <select v-model="filterStatus" class="w-full rounded border px-2 py-1.5 sm:min-w-[130px]">
                        <option value="all">All statuses</option>
                        <option value="draft">Draft</option>
                        <option value="approved">Approved</option>
                    </select>
                </div>

                <button
                    v-if="hasFilters"
                    class="rounded border px-3 py-1.5 text-sm text-gray-500 transition hover:border-red-300 hover:text-red-600"
                    @click="clearFilters"
                >
                    Clear
                </button>

                <div class="w-full self-end text-sm text-gray-400 sm:ml-auto sm:w-auto">
                    {{ filteredOrders.length }} of {{ orders.length }}
                </div>
            </div>

            <!-- ── Table ── -->
            <div class="overflow-x-auto rounded-lg border bg-white shadow">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border p-2">#</th>
                            <th class="border p-2 text-left">Client</th>
                            <th class="border p-2 text-left">Location</th>
                            <th class="border p-2 text-center">Date</th>
                            <th class="border p-2 text-center">Size</th>
                            <th class="border p-2 text-center">Price</th>
                            <th class="border p-2 text-center">Status</th>
                            <th class="border p-2 text-center w-[230px]">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="o in filteredOrders"
                            :key="o.id"
                            class="transition hover:bg-gray-50"
                            :class="o.approved ? 'bg-green-50/40' : ''"
                        >
                            <td class="border p-2 text-center text-gray-500">{{ o.id }}</td>
                            <td class="border p-2">{{ o.client?.name ?? '—' }}</td>
                            <td class="border p-2">{{ o.location?.name ?? '—' }}</td>
                            <td class="border p-2 text-center text-gray-500">{{ o.date.slice(0, 10) }}</td>
                            <td class="border p-2 text-center">{{ o.size }}</td>
                            <td class="border p-2 text-center font-semibold">{{ nf(o.price) }} €</td>
                            <td class="border p-2 text-center">
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    :class="o.approved
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-gray-100 text-gray-500'"
                                >
                                    {{ o.approved ? 'Approved' : 'Draft' }}
                                </span>
                            </td>
                            <td class="border p-2">
                                <div class="flex items-center justify-center gap-2.5">

                                    <!-- APPROVE -->
                                    <button
                                        v-if="!o.approved"
                                        class="text-green-500 transition hover:text-green-700"
                                        title="Approve order"
                                        @click="approveOrder(o.id)"
                                    >
                                        <CheckCircleIcon class="h-5 w-5" />
                                    </button>
                                    <span
                                        v-else
                                        class="text-green-400"
                                        title="Approved"
                                    >
                                        <CheckCircleIcon class="h-5 w-5" />
                                    </span>

                                    <!-- EDIT -->
                                    <button
                                        v-if="!o.approved"
                                        class="text-blue-600 transition hover:text-blue-800"
                                        title="Edit order"
                                        @click="editOrder(o.id)"
                                    >
                                        <PencilIcon class="h-5 w-5" />
                                    </button>
                                    <span
                                        v-else
                                        class="cursor-not-allowed text-gray-300"
                                        title="Approved orders cannot be edited"
                                    >
                                        <PencilIcon class="h-5 w-5" />
                                    </span>

                                    <!-- DELETE -->
                                    <button
                                        v-if="!o.approved"
                                        class="text-red-600 transition hover:text-red-800"
                                        title="Delete order"
                                        @click="deleteOrder(o.id)"
                                    >
                                        <Trash2Icon class="h-5 w-5" />
                                    </button>
                                    <span
                                        v-else
                                        class="cursor-not-allowed text-gray-300"
                                        title="Approved orders cannot be deleted"
                                    >
                                        <Trash2Icon class="h-5 w-5" />
                                    </span>

                                    <!-- PDF PREVIEW -->
                                    <a
                                        :href="`/orders/${o.id}/pricing-pdf/preview`"
                                        target="_blank"
                                        class="text-blue-600 transition hover:text-blue-800"
                                        title="Preview PDF"
                                    >
                                        <EyeIcon class="h-5 w-5" />
                                    </a>

                                    <!-- PDF DOWNLOAD -->
                                    <a
                                        :href="`/orders/${o.id}/pricing-pdf`"
                                        class="text-green-600 transition hover:text-green-800"
                                        title="Download PDF"
                                    >
                                        <DownloadIcon class="h-5 w-5" />
                                    </a>

                                    <!-- INTERNAL PRICING -->
                                    <a
                                        :href="`/orders/${o.id}/pricing-internal`"
                                        class="text-purple-600 transition hover:text-purple-800"
                                        title="Internal pricing"
                                    >
                                        <TableIcon class="h-5 w-5" />
                                    </a>

                                    <!-- DUPLICATE -->
                                    <button
                                        class="text-gray-500 transition hover:text-gray-800"
                                        title="Duplicate order"
                                        @click="duplicateOrder(o.id)"
                                    >
                                        <CopyIcon class="h-5 w-5" />
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
