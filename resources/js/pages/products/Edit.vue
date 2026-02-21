<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import Select from 'primevue/select';

const props = defineProps<{
    product: {
        id: number;
        name_fi: string;
        name_ee: string;
        name_en: string;
        type: string;
        components: { id: number; pivot: { quantity: number } }[];
    };
    components: { id: number; name: string }[];
}>();

const form = useForm({
    name_fi: props.product.name_fi,
    name_ee: props.product.name_ee,
    name_en: props.product.name_en,
    type: props.product.type,
    items: props.product.components.map(c => ({
        component_id: c.id,
        quantity: c.pivot.quantity,
    })),
});

const addRow = () => form.items.push({ component_id: null, quantity: 1 });
const removeRow = (i: number) => form.items.splice(i, 1);
const submit = () => form.put(`/products/${props.product.id}`);
</script>

<template>
    <Head title="Edit product" />
    <AppLayout>
        <div class="max-w-3xl space-y-4 rounded bg-white p-6 shadow">

            <h1 class="text-xl font-bold">Edit product</h1>

            <input v-model="form.name_fi" class="w-full rounded border p-2" />
            <InputError :message="form.errors.name_fi" />

            <input v-model="form.name_ee" class="w-full rounded border p-2" />
            <InputError :message="form.errors.name_ee" />

            <input v-model="form.name_en" class="w-full rounded border p-2" />
            <InputError :message="form.errors.name_en" />

            <select v-model="form.type" class="w-full rounded border p-2">
                <option value="base">Base</option>
                <option value="vegan">Vegan</option>
                <option value="vegetarian">Vegetarian</option>
            </select>
            <InputError :message="form.errors.type" />

            <div class="space-y-2">
                <div
                    v-for="(item, i) in form.items"
                    :key="i"
                    class="flex gap-2 rounded py-2"
                >
                    <div class="flex-1">
                        <Select
                            v-model="item.component_id"
                            :options="components"
                            optionLabel="name"
                            optionValue="id"
                            filter
                            filterPlaceholder="Search..."
                            placeholder="Select component"
                            class="w-full"
                        />
                    </div>

                    <input v-model.number="item.quantity" type="number" step="0.001" class="w-32 rounded border p-2" />

                    <button
                        v-if="form.items.length > 1"
                        class="rounded bg-red-500 px-2 text-white"
                        @click="removeRow(i)"
                    >
                        ✕
                    </button>
                </div>

                <InputError :message="form.errors[`items.${i}.component_id`]" />
                <InputError :message="form.errors[`items.${i}.quantity`]" />
            </div>

            <button class="rounded bg-gray-200 px-3 py-1" @click="addRow">
                + Add component
            </button>

            <button
                class="rounded bg-blue-600 px-4 py-2 text-white"
                :disabled="form.processing"
                @click="submit"
            >
                Update
            </button>

        </div>
    </AppLayout>
</template>
