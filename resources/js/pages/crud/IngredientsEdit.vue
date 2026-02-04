<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ingredients } from '@/routes';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import Heading from '@/components/Heading.vue';
import { computed } from 'vue';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Create ingredients',
        href: ingredients().url,
    },
];
const props = defineProps<{
    ingredient: {
        id: number;
        name: string;
        price: number;
        size: number;
        unit: string;
    };
}>();

const form = useForm({
    name: props.ingredient.name,
    price: props.ingredient.price,
    size: props.ingredient.size,
    unit: props.ingredient.unit,
});
const submit = () => {
    form.put(`/ingredients/${props.ingredient.id}`);
};

const units = [
    'kg',
    'l',
    'pcs'
];

</script>

<template>
    <Head title="Add ingredient" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-4 py-6">

            <form @submit.prevent="submit" class="space-y-6 max-w-md" style="display: flex; flex-direction: column">

                <div class="grid gap-2">
                    <Label for="name">Name ingredient</Label>
                    <Input
                        id="name"
                        name="name"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="Name"
                        v-model="form.name"
                    />
                </div>
                <div class="grid gap-2">
                    <Label for="price">Price ingredient</Label>
                    <Input
                        id="price"
                        name="price"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="Price"
                        v-model="form.price"
                    />
                </div>
                <div class="grid gap-2">
                    <Label for="size">Size ingredient</Label>
                    <Input
                        id="size"
                        name="size"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="Size"
                        v-model="form.size"
                    />
                </div>
                <div class="grid gap-2">
                    <Label for="unit">Unit ingredient</Label>
                    <select v-model="form.unit" class="rounded border px-3 py-2">
                        <option v-for="unit in units" :key="unit" :value="unit">
                            {{ unit }}
                        </option>
                    </select>
                </div>

                <button class="rounded bg-green-600 px-4 py-2 text-white d-flex justify-center">Save</button>

            </form>
        </div>
    </AppLayout>
</template>

