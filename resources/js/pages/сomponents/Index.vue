<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Table from '@/components/ui/table/Table.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmDialog from 'primevue/confirmdialog';
import { useConfirm } from 'primevue/useconfirm';
import { components as componentsRoute } from '@/routes';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Components',
        href: componentsRoute().url,
    },
];

const confirm = useConfirm();

const props = defineProps<{
    components: {
        id: number;
        name: string;
        type: string;
        cost: number;
    }[];
    columns: {
        field: string;
        header: string;
        body?: (row: any) => any;
    }[];
}>();

const onDelete = (row: any) => {
    confirm.require({
        message: `Вы уверены, что хотите удалить компонент "${row.name}"?`,
        header: 'Подтверждение удаления',
        icon: 'pi pi-exclamation-triangle',
        acceptLabel: 'Удалить',
        rejectLabel: 'Отмена',
        accept: () => {
            router.delete(`/components/${row.id}`, {
                preserveScroll: true,
            });
        },
    });
};
</script>

<template>
    <Head title="Components" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <ConfirmDialog />

        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="flex justify-end">
                <button
                    class="rounded bg-blue-600 px-4 py-2 text-white"
                    @click="router.visit('/components/create')"
                >
                    + Add component
                </button>
            </div>

            <Table
                :rows="props.components"
                :columns="props.columns"
                update="components"
                @delete="onDelete"
            />
        </div>
    </AppLayout>
</template>
