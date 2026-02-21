<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import Select from 'primevue/select';
import { computed, ref } from 'vue';

const props = defineProps<{
    ingredients: { id: number; name: string; unit: string; kg_price: number }[];
}>();

const ingredientOptions = computed(() =>
    props.ingredients.map(ing => ({
        id: ing.id,
        label: `${ing.name} (${ing.unit})`,
    }))
)

const form = useForm({
    name: '',
    quantity: null,
    type: 'gravy',
    items: [{ ingredient_id: null as number | null, quantity: '0' }],
});

const addRow = () => {
    form.items.push({ ingredient_id: null, quantity: '0' });
};

const bulkCount = ref(5)
const addBulk = () => {
    for (let i = 0; i < bulkCount.value; i++) {
        form.items.push({ ingredient_id: null, quantity: '0' })
    }
}

const removeRow = (i: number) => {
    form.items.splice(i, 1);
};

const itemsError = ref('')

const submit = () => {
    const filtered = form.items.filter(i => i.ingredient_id !== null)

    if (filtered.length === 0) {
        itemsError.value = 'At least one ingredient must be selected.'
        return
    }

    itemsError.value = ''

    form
        .transform(data => ({
            ...data,
            items: filtered.map(i => ({
                ...i,
                quantity: Number(i.quantity),
            })),
        }))
        .post('/components')
};
</script>

<template>
    <Head title="Create component" />
    <AppLayout>
        <div class="max-w-3xl space-y-4 rounded bg-white p-6 shadow">

            <h1 class="text-xl font-bold">Create component</h1>

            <!-- Name -->
            <div class="grid gap-1">
                <input
                    v-model="form.name"
                    class="w-full rounded border p-2"
                    placeholder="Name"
                />
                <InputError :message="form.errors.name" />
            </div>

            <!-- Type -->
            <div class="grid gap-1">
                <select v-model="form.type" class="w-full rounded border p-2">
                    <option value="gravy">Gravy</option>
                    <option value="protein">Protein</option>
                    <option value="side_dish">Side dish</option>
                </select>
                <InputError :message="form.errors.type" />
            </div>

            <!-- Type -->
            <div class="grid gap-1">
                <input
                    v-model="form.quantity"
                    type="number"
                    step="1"
                    min="1"
                    class="w-full rounded border p-2"
                    placeholder="Quantity kg"
                />
                <InputError :message="form.errors.quantity" />
            </div>

            <!-- Bulk add -->
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500">Add</span>
                <input
                    v-model.number="bulkCount"
                    type="number"
                    min="1"
                    max="50"
                    class="w-16 rounded border p-1 text-center text-sm"
                />
                <span class="text-sm text-gray-500">empty rows</span>
                <button class="rounded bg-gray-200 px-3 py-1 text-sm" @click="addBulk">
                    Add
                </button>
            </div>

            <!-- Items -->
            <div class="space-y-2">
                <div
                    v-for="(item, i) in form.items"
                    :key="i"
                    class="flex flex-col gap-1 rounded py-2"
                >
                    <div class="flex gap-2">
                        <div class="flex-1">
                            <Select
                                v-model="item.ingredient_id"
                                :options="ingredientOptions"
                                optionLabel="label"
                                optionValue="id"
                                filter
                                filterPlaceholder="Search..."
                                placeholder="Select ingredient"
                                class="w-full"
                            />
                        </div>

                        <input
                            v-model="item.quantity"
                            type="number"
                            step="0.001"
                            min="0.0001"
                            class="w-32 rounded border p-2"
                            placeholder="Qty"
                        />

                        <button
                            v-if="form.items.length > 1"
                            class="rounded bg-red-500 px-2 text-white"
                            @click="removeRow(i)"
                        >
                            ✕
                        </button>
                    </div>

                    <InputError :message="form.errors[`items.${i}.ingredient_id`]" />
                    <InputError :message="form.errors[`items.${i}.quantity`]" />
                </div>
            </div>

            <button class="rounded bg-gray-200 px-3 py-1" @click="addRow">
                + Add ingredient
            </button>

            <p v-if="itemsError" class="text-sm text-red-600">{{ itemsError }}</p>

            <button
                class="rounded bg-blue-600 px-4 py-2 text-white"
                :disabled="form.processing"
                @click="submit"
            >
                Save
            </button>

        </div>
    </AppLayout>
</template>
