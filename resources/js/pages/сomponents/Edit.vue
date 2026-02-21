<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import Select from 'primevue/select';
import { Trash2Icon } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    component: {
        id: number;
        name: string;
        type: string;
        quantity: number;
        ingredients: { id: number; pivot: { quantity: number } }[];
    };
    ingredients: { id: number; name: string; unit: string; kg_price: number }[];
}>();

const ingredientOptions = props.ingredients.map(ing => ({
    id: ing.id,
    label: `${ing.name} (${ing.unit})`,
}))

const form = useForm({
    name: props.component.name,
    type: props.component.type,
    quantity: +props.component.quantity,
    items: props.component.ingredients.map(i => ({
        ingredient_id: i.id,
        quantity: String(i.pivot.quantity),
    })),
});

const addRow = () => form.items.push({ ingredient_id: null, quantity: '0' });

const bulkCount = ref(5)
const addBulk = () => {
    for (let i = 0; i < bulkCount.value; i++)
        form.items.push({ ingredient_id: null, quantity: '0' })
}

const removeRow = (i: number) => form.items.splice(i, 1);

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
            items: filtered.map(i => ({ ...i, quantity: Number(i.quantity) })),
        }))
        .put(`/components/${props.component.id}`)
};
</script>

<template>
    <Head title="Edit component" />
    <AppLayout>
        <div class="max-w-4xl space-y-4 rounded-xl bg-white p-5 shadow-sm">

            <h1 class="text-lg font-semibold text-gray-800">Edit component</h1>

            <!-- Name / Type / Quantity in one row -->
            <div class="flex gap-3">
                <div class="flex-1">
                    <input
                        v-model="form.name"
                        class="w-full rounded border px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-400"
                        placeholder="Name"
                    />
                    <InputError :message="form.errors.name" />
                </div>
                <div>
                    <select v-model="form.type" class="rounded border px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-400">
                        <option value="gravy">Gravy</option>
                        <option value="protein">Protein</option>
                        <option value="side_dish">Side dish</option>
                    </select>
                    <InputError :message="form.errors.type" />
                </div>
                <div>
                    <input
                        v-model="form.quantity"
                        type="number"
                        min="1"
                        class="w-28 rounded border px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-400"
                        placeholder="Qty kg"
                    />
                    <InputError :message="form.errors.quantity" />
                </div>
            </div>

            <!-- Bulk add -->
            <div class="flex items-center gap-2 text-sm text-gray-500">
                <span>Add</span>
                <input
                    v-model.number="bulkCount"
                    type="number" min="1" max="50"
                    class="w-14 rounded border px-2 py-1 text-center text-sm"
                />
                <span>empty rows</span>
                <button class="rounded bg-gray-100 px-3 py-1 text-sm hover:bg-gray-200 transition" @click="addBulk">
                    Add
                </button>
            </div>

            <!-- Items table -->
            <div class="rounded-lg border overflow-hidden">
                <!-- Header -->
                <div class="grid grid-cols-[2rem_1fr_8rem_2.5rem] gap-2 bg-gray-50 px-3 py-2 text-xs font-medium text-gray-500 border-b">
                    <span class="text-center">#</span>
                    <span>Ingredient</span>
                    <span class="text-center">Quantity</span>
                    <span></span>
                </div>

                <!-- Rows -->
                <div
                    v-for="(item, i) in form.items"
                    :key="i"
                    class="grid grid-cols-[2rem_1fr_8rem_2.5rem] gap-2 items-center px-3 py-1.5 border-b last:border-b-0 hover:bg-gray-50/60 transition"
                >
                    <span class="text-center text-xs text-gray-400">{{ i + 1 }}</span>

                    <Select
                        v-model="item.ingredient_id"
                        :options="ingredientOptions"
                        optionLabel="label"
                        optionValue="id"
                        filter
                        filterPlaceholder="Search..."
                        placeholder="Select ingredient"
                        class="w-full text-sm"
                    />

                    <input
                        v-model="item.quantity"
                        type="number"
                        step="0.001"
                        min="0.0001"
                        class="w-full rounded border px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-1 focus:ring-blue-400"
                    />

                    <button
                        v-if="form.items.length > 1"
                        class="flex items-center justify-center rounded p-1 text-gray-400 hover:bg-red-50 hover:text-red-500 transition"
                        title="Remove row"
                        @click="removeRow(i)"
                    >
                        <Trash2Icon class="h-4 w-4" />
                    </button>
                    <span v-else class="w-10"></span>
                </div>
            </div>

            <!-- Row errors (only non-empty) -->
            <div v-for="(item, i) in form.items" :key="`err-${i}`">
                <InputError :message="form.errors[`items.${i}.ingredient_id`]" />
                <InputError :message="form.errors[`items.${i}.quantity`]" />
            </div>

            <!-- Bottom actions -->
            <div class="flex items-center gap-3">
                <button
                    class="rounded border px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-50 transition"
                    @click="addRow"
                >
                    + Add ingredient
                </button>

                <p v-if="itemsError" class="text-sm text-red-600">{{ itemsError }}</p>

                <button
                    class="ml-auto rounded bg-blue-600 px-5 py-1.5 text-sm text-white hover:bg-blue-700 transition disabled:opacity-50"
                    :disabled="form.processing"
                    @click="submit"
                >
                    Update
                </button>
            </div>

        </div>
    </AppLayout>
</template>
