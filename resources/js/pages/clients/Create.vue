<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';

const props = defineProps<{
    locations: { id: number; name: string }[];
}>();

const form = useForm({
    name: '',
    phone: '',
    location_id: null as number | null,
    approved: true,
});

const submit = () => form.post('/clients');
</script>

<template>
    <Head title="Create client" />
    <AppLayout>
        <div class="max-w-md space-y-4 rounded bg-white p-6 shadow">
            <h1 class="text-xl font-bold">Create client</h1>

            <input v-model="form.name" class="w-full rounded border p-2" placeholder="Name" />
            <InputError :message="form.errors.name" />

            <input v-model="form.phone" class="w-full rounded border p-2" placeholder="Phone" />
            <InputError :message="form.errors.phone" />

            <select v-model="form.location_id" class="w-full rounded border p-2">
                <option :value="null" disabled>Select location</option>
                <option v-for="l in locations" :key="l.id" :value="l.id">
                    {{ l.name }}
                </option>
            </select>
            <InputError :message="form.errors.location_id" />

            <label class="flex items-center gap-2">
                <input type="checkbox" v-model="form.approved" />
                Approved
            </label>

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
