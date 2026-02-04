<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';

const props = defineProps<{
    component: {
        id: number;
        name: string;
        type: string;
        ingredients: { id: number; pivot: { quantity: number } }[];
    };
    ingredients: { id: number; name: string }[];
}>();

const form = useForm({
    name: props.component.name,
    type: props.component.type,
    items: props.component.ingredients.map(i => ({
        ingredient_id: i.id,
        quantity: String(i.pivot.quantity), // 👈 храним как строку для нормального ввода
    })),
});

const addRow = () => {
    form.items.push({ ingredient_id: null, quantity: '0' });
};

const removeRow = (i: number) => {
    form.items.splice(i, 1);
};

const submit = () => {
    // 👇 приводим quantity к числу перед отправкой
    const payload = {
        ...form.data(),
        items: form.items.map(i => ({
            ...i,
            quantity: Number(i.quantity),
        })),
    };

    form.put(`/components/${props.component.id}`, {
        data: payload,
    });
};
</script>

<template>
    <Head title="Edit component" />
    <AppLayout>
        <div class="max-w-3xl space-y-4 rounded bg-white p-6 shadow">

            <h1 class="text-xl font-bold">Edit component</h1>

            <!-- Name -->
            <div class="grid gap-1">
                <input v-model="form.name" class="w-full rounded border p-2" />
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

            <!-- Items -->
            <div class="space-y-3">
                <div
                    v-for="(item, i) in form.items"
                    :key="i"
                    class="rounded border p-3"
                >
                    <div class="flex gap-2">
                        <select v-model="item.ingredient_id" class="flex-1 rounded border p-2">
                            <option :value="null" disabled>Select ingredient</option>
                            <option
                                v-for="ing in ingredients"
                                :key="ing.id"
                                :value="ing.id"
                            >
                                {{ ing.name }}
                            </option>
                        </select>

                        <input
                            v-model="item.quantity"
                            type="number"
                            step="0.001"
                            min="0.0001"
                            class="w-32 rounded border p-2"
                        />
                    </div>

                    <InputError :message="form.errors[`items.${i}.quantity`]" />
                    <InputError :message="form.errors[`items.${i}.ingredient_id`]" />

                    <button
                        v-if="form.items.length > 1"
                        class="mt-2 text-red-500" style="cursor: pointer;"
                        @click="removeRow(i)"
                    >
                        Remove
                    </button>
                </div>
            </div>

            <button class="rounded bg-gray-200 px-3 py-2 mr-2" style="cursor: pointer;" @click="addRow">
                + Add ingredient
            </button>

            <button
                class="rounded bg-blue-600 px-4 py-2 text-white" style="cursor: pointer;"
                :disabled="form.processing"
                @click="submit"
            >
                Update
            </button>

        </div>
    </AppLayout>
</template>
