<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';

const props = defineProps<{
    client: {
        id: number;
        name: string;
        phone: string;
        location_id: number;
        approved: boolean;
    };
    locations: { id: number; name: string }[];
}>();

const form = useForm({
    name: props.client.name,
    phone: props.client.phone,
    location_id: props.client.location_id,
    approved: !!props.client.approved,
});

const submit = () => form.put(`/clients/${props.client.id}`);
</script>

<template>
    <Head title="Edit client" />
    <AppLayout>
        <div class="mx-4 max-w-md space-y-4 rounded bg-white p-6 shadow sm:mx-auto">
            <h1 class="text-xl font-bold">Edit client</h1>

            <input v-model="form.name" class="w-full rounded border p-2" />
            <InputError :message="form.errors.name" />

            <input v-model="form.phone" class="w-full rounded border p-2" />
            <InputError :message="form.errors.phone" />

            <select v-model="form.location_id" class="w-full rounded border p-2">
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
                Update
            </button>
        </div>
    </AppLayout>
</template>
