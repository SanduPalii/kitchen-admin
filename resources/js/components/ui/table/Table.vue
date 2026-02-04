<script setup lang="ts" generic="T">
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { Trash2Icon, PencilIcon } from 'lucide-vue-next';


defineProps<{
    rows: T[];
    columns: {
        field: keyof T | string;
        header: string;
        body?: (row: T) => any;
    }[];
}>();

const emit = defineEmits<{
    (e: 'delete', row: T): void
}>()
</script>

<template>
    <DataTable :value="rows" tableStyle="min-width: 50rem">
        <Column
            v-for="col in columns"
            :key="String(col.field)"
            :field="col.field"
            :header="col.header"
        >
            <template v-if="col.body" #body="slotProps">
                {{ col.body(slotProps.data) }}
            </template>
        </Column>
        <Column header="Actions" style="width: 120px">
            <template #body="{ data }">
                <div style="display:flex;gap: 10px;align-items:center;">
                    <a :href="'/ingredients/'+data.id">
                        <PencilIcon style="cursor: pointer;"/>
                    </a>
                    <div
                        @click="emit('delete', data)"
                    >
                        <Trash2Icon style="cursor:pointer; color:var(--color-rose-600);"/>
                    </div>
                </div>
            </template>
        </Column>

        <template #footer>
            In total there are {{ rows.length }} items.
        </template>
    </DataTable>
</template>
