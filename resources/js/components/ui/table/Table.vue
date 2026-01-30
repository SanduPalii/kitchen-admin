<script setup lang="ts" generic="T">
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';

defineProps<{
    rows: T[];
    columns: {
        field: keyof T | string;
        header: string;
        body?: (row: T) => any;
    }[];
}>();
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

        <template #footer>
            In total there are {{ rows.length }} items.
        </template>
    </DataTable>
</template>
