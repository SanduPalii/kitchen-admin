<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import Select from 'primevue/select';
import { computed } from 'vue';

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
        quantity: Math.round(Number(c.pivot.quantity) || 0),
    })),
});

const totalGrams = computed(() =>
    form.items.reduce((s, item) => s + (Math.round(Number(item.quantity) || 0)), 0)
)

const duplicateComponentIds = computed(() => {
    const seen = new Set<number>()
    const dupes = new Set<number>()
    for (const item of form.items) {
        if (item.component_id !== null) {
            if (seen.has(item.component_id)) dupes.add(item.component_id)
            else seen.add(item.component_id)
        }
    }
    return dupes
})

const hasDuplicates = computed(() => duplicateComponentIds.value.size > 0)

const addRow = () => {
    if (form.items.length >= 3) return
    form.items.push({ component_id: null, quantity: 0 })
}

const removeRow = (i: number) => form.items.splice(i, 1)

const setQuantity = (i: number, ev: Event) => {
    const raw = parseInt((ev.target as HTMLInputElement).value, 10)
    if (isNaN(raw)) return
    const others = form.items.reduce((s, item, idx) =>
        idx !== i ? s + (Number(item.quantity) || 0) : s, 0)
    const clamped = Math.max(0, Math.min(raw, 1000 - others))
    form.items[i].quantity = clamped
    ;(ev.target as HTMLInputElement).value = String(clamped)
}

const submit = () => form.put(`/products/${props.product.id}`)
</script>

<template>
    <Head title="Edit product" />
    <AppLayout>
        <div class="max-w-3xl space-y-4 rounded bg-white p-6 shadow">

            <h1 class="text-xl font-bold">Edit product</h1>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Name (EN) <span class="text-red-500">*</span>
                </label>
                <input v-model="form.name_en" class="w-full rounded border p-2" placeholder="Name in English" />
                <InputError :message="form.errors.name_en" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Name (FI) <span class="text-xs text-gray-400 font-normal">(optional)</span>
                </label>
                <input v-model="form.name_fi" class="w-full rounded border p-2" placeholder="Name in Finnish" />
                <InputError :message="form.errors.name_fi" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Name (EE) <span class="text-xs text-gray-400 font-normal">(optional)</span>
                </label>
                <input v-model="form.name_ee" class="w-full rounded border p-2" placeholder="Name in Estonian" />
                <InputError :message="form.errors.name_ee" />
            </div>

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
                    class="flex flex-col gap-1 rounded py-2"
                >
                    <div class="flex gap-2 items-center">
                        <div class="flex-1">
                            <Select
                                v-model="item.component_id"
                                :options="components"
                                optionLabel="name"
                                optionValue="id"
                                filter
                                filterPlaceholder="Search..."
                        autoFilterFocus
                                placeholder="Select component"
                                class="w-full"
                            />
                        </div>

                        <div class="flex items-center gap-1">
                            <input
                                :value="item.quantity"
                                @input="setQuantity(i, $event)"
                                type="number" min="0" max="1000" step="1"
                                class="w-24 rounded border p-2 text-right"
                            />
                            <span class="text-sm text-gray-500">g</span>
                        </div>

                        <button
                            v-if="form.items.length > 1"
                            class="rounded bg-red-500 px-2 py-2 text-white"
                            @click="removeRow(i)"
                        >
                            ✕
                        </button>
                    </div>
                    <p v-if="item.component_id != null && duplicateComponentIds.has(item.component_id)"
                       class="text-xs text-red-500">
                        This component is already added
                    </p>
                </div>

                <!-- Total grams indicator -->
                <div class="flex items-center gap-2 text-sm">
                    <span :class="totalGrams > 1000 ? 'text-red-600 font-semibold' : 'text-gray-500'">
                        Total: {{ totalGrams }} / 1000 g
                    </span>
                    <span v-if="totalGrams > 1000" class="text-red-500 text-xs">⚠ exceeds 1 kg</span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button
                    class="rounded bg-gray-200 px-3 py-1 text-sm disabled:opacity-40 disabled:cursor-not-allowed"
                    :disabled="form.items.length >= 3"
                    @click="addRow"
                >
                    + Add component
                </button>
                <span v-if="form.items.length >= 3" class="text-xs text-gray-400">Max 3 components</span>
            </div>

            <button
                class="rounded bg-blue-600 px-4 py-2 text-white disabled:opacity-40"
                :disabled="form.processing || hasDuplicates"
                @click="submit"
            >
                Update
            </button>

        </div>
    </AppLayout>
</template>
