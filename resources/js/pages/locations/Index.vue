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
    locations: {
        id: number;
        name: string;
        price: number;
    }[];
    columns: {
        field: string;
        header: string;
    }[];
}>();

const onEdit = (id: number) => {
    router.visit(`/locations/${id}/edit`);
};

const onDelete = (row: any) => {
    confirm.require({
        message: `Delete location "${row.name}"?`,
        header: 'Confirm delete',
        icon: 'pi pi-exclamation-triangle',
        acceptLabel: 'Delete',
        rejectLabel: 'Cancel',
        accept: () => {
            router.delete(`/locations/${row.id}`, { preserveScroll: true });
        },
    });
};
</script>

<template>
    <Head title="Locations" />
    <AppLayout>
        <ConfirmDialog />

        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="flex justify-end">
                <button
                    class="rounded bg-blue-600 px-4 py-2 text-white"
                    @click="router.visit('/locations/create')"
                >
                    + Add location
                </button>
            </div>

            <DataTable :value="locations" tableStyle="min-width: 40rem">
                <Column v-for="col in columns" :key="col.field" :field="col.field" :header="col.header" />

                <Column header="Actions" style="width: 120px">
                    <template #body="{ data }">
                        <div class="flex gap-3">
                            <PencilIcon class="cursor-pointer" @click="onEdit(data.id)" />
                            <Trash2Icon class="cursor-pointer text-red-600" @click="onDelete(data)" />
                        </div>
                    </template>
                </Column>

                <template #footer>
                    In total there are {{ locations.length }} locations.
                </template>
            </DataTable>
        </div>
    </AppLayout>
</template>
