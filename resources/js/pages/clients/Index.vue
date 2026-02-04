<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { PencilIcon, Trash2Icon } from 'lucide-vue-next';
import ConfirmDialog from 'primevue/confirmdialog';
import { useConfirm } from 'primevue/useconfirm';

const confirm = useConfirm();

const props = defineProps<{
    clients: {
        id: number;
        name: string;
        phone: string;
        approved: boolean;
        location: { name: string };
    }[];
    columns: {
        field: string;
        header: string;
    }[];
}>();

const onEdit = (id: number) => router.visit(`/clients/${id}/edit`);

const onDelete = (row: any) => {
    confirm.require({
        message: `Delete client "${row.name}"?`,
        header: 'Confirm delete',
        icon: 'pi pi-exclamation-triangle',
        accept: () => router.delete(`/clients/${row.id}`),
    });
};
</script>

<template>
    <Head title="Clients" />
    <AppLayout>
        <ConfirmDialog />

        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="flex justify-end">
                <button
                    class="rounded bg-blue-600 px-4 py-2 text-white"
                    @click="router.visit('/clients/create')"
                >
                    + Add client
                </button>
            </div>

            <DataTable :value="clients" tableStyle="min-width: 60rem">
                <Column field="name" header="Name" />
                <Column field="phone" header="Phone" />
                <Column header="Location">
                    <template #body="{ data }">
                        {{ data.location?.name }}
                    </template>
                </Column>
                <Column header="Approved">
                    <template #body="{ data }">
                        <span
                            :class="data.approved ? 'text-green-600' : 'text-red-600'"
                        >
                            {{ data.approved ? 'Yes' : 'No' }}
                        </span>
                    </template>
                </Column>

                <Column header="Actions" style="width: 120px">
                    <template #body="{ data }">
                        <div class="flex gap-3">
                            <PencilIcon class="cursor-pointer" @click="onEdit(data.id)" />
                            <Trash2Icon class="cursor-pointer text-red-600" @click="onDelete(data)" />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>
    </AppLayout>
</template>
