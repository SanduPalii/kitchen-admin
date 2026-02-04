<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';

const form = useForm({
    name: '',
    price: '',
});

const submit = () => {
    form.post('/locations');
};
</script>

<template>
    <Head title="Create location" />
    <AppLayout>
        <div class="max-w-md space-y-4 rounded bg-white p-6 shadow">

            <h1 class="text-xl font-bold">Create location</h1>

            <div class="grid gap-1">
                <input
                    v-model="form.name"
                    class="w-full rounded border p-2"
                    placeholder="Location name"
                />
                <InputError :message="form.errors.name" />
            </div>

            <div class="grid gap-1">
                <input
                    v-model.number="form.price"
                    type="number"
                    step="0.01"
                    class="w-full rounded border p-2"
                    placeholder="Delivery price"
                />
                <InputError :message="form.errors.price" />
            </div>

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
