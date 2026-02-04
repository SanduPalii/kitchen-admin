<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { PencilIcon, Trash2Icon } from 'lucide-vue-next';

const props = defineProps<{
    products: {
        id: number;
        name_fi: string;
        name_ee: string;
        name_en: string;
        type: string;
    }[];
    columns: {
        field: string;
        header: string;
    }[];
}>();

const onEdit = (id: number) => {
    router.visit(`/products/${id}/edit`);
};

const onDelete = (id: number) => {
    if (confirm('Delete this product?')) {
        router.delete(`/products/${id}`);
    }
};
</script>

<template>
    <Head title="Products" />
    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">

            <div class="flex justify-end">
                <button
                    class="rounded bg-blue-600 px-4 py-2 text-white"
                    @click="router.visit('/products/create')"
                >
                    + Add product
                </button>
            </div>

            <DataTable :value="products" tableStyle="min-width: 60rem">
                <Column v-for="col in columns" :key="col.field" :field="col.field" :header="col.header" />

                <Column header="Actions" style="width: 120px">
                    <template #body="{ data }">
                        <div class="flex gap-3">
                            <PencilIcon class="cursor-pointer" @click="onEdit(data.id)" />
                            <Trash2Icon class="cursor-pointer text-red-600" @click="onDelete(data.id)" />
                        </div>
                    </template>
                </Column>
            </DataTable>

        </div>
    </AppLayout>
</template>
