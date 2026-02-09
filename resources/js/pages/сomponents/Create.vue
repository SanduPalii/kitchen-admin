<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';

const props = defineProps<{
    ingredients: { id: number; name: string; unit: string; kg_price: number }[];
}>();

const form = useForm({
    name: '',
    quantity: null,
    type: 'gravy',
    items: [{ ingredient_id: null as number | null, quantity: '0' }],
});

const addRow = () => {
    form.items.push({ ingredient_id: null, quantity: '0' });
};

const removeRow = (i: number) => {
    form.items.splice(i, 1);
};

const submit = () => {
    const payload = {
        ...form.data(),
        items: form.items.map(i => ({
            ...i,
            quantity: Number(i.quantity),
        })),
    };

    form.post('/components', { data: payload });
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

            <!-- Items -->
            <div class="space-y-2">
                <div
                    v-for="(item, i) in form.items"
                    :key="i"
                    class="flex flex-col gap-1 rounded border p-2"
                >
                    <div class="flex gap-2">
                        <select
                            v-model="item.ingredient_id"
                            class="flex-1 rounded border p-2"
                        >
                            <option :value="null" disabled>Select ingredient</option>
                            <option
                                v-for="ing in props.ingredients"
                                :key="ing.id"
                                :value="ing.id"
                            >
                                {{ ing.name }} ({{ ing.unit }})
                            </option>
                        </select>

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
