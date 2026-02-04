<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import Table from '@/components/ui/table/Table.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { ingredients  } from '@/routes';
import ConfirmDialog from 'primevue/confirmdialog';
import { useConfirm } from 'primevue/useconfirm';
import { type BreadcrumbItem } from '@/types';
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Ingredients',
        href: ingredients().url,
    },
];
const confirm = useConfirm();
const props = defineProps<{
    ingredients: {
        id: number;
        name: string;
        price: number;
        size: number;
        unit: string;
    }[];
    columns: {
        field: string;
        header: string;
    }[];
}>();

const selectedUnit = ref(props.filters?.unit ?? '');

const units = computed(() =>
    [...new Set(props.ingredients.map(i => i.unit))]
);

watch(selectedUnit, (unit) => {
    router.get(
        ingredients({
            query: {
                unit: unit || undefined,
            },
        }).url,
        {},
        {
            preserveState: true,
            replace: true,
        }
    );
});

const onDelete = (row: any) => {
    confirm.require({
        message: `Вы уверены, что хотите удалить "${row.name}"?`,
        header: 'Подтверждение удаления',
        icon: 'pi pi-exclamation-triangle',
        acceptLabel: 'Удалить',
        rejectLabel: 'Отмена',
        accept: () => {
            router.delete(`/ingredients/${row.id}`, {
                preserveScroll: true,
            });
        },
    });
};
</script>

<template>
    <Head title="Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <ConfirmDialog />

        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">

            <div class="flex items-center justify-between">
                <!-- Фильтр -->
                <select v-model="selectedUnit" class="rounded border px-3 py-2">
                    <option value="">All units</option>
                    <option v-for="unit in units" :key="unit" :value="unit">
                        {{ unit }}
                    </option>
                </select>

                <!-- Кнопка -->
                <button
                    class="rounded bg-blue-600 px-4 py-2 text-white"
                    @click="router.visit('/ingredients/create')"
                >
                    + Add ingredient
                </button>

            </div>

            <Table :rows="props.ingredients" :columns="props.columns" update="ingredients" @delete="onDelete"/>

        </div>
    </AppLayout>
</template>
