<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import DatePicker from 'primevue/datepicker'
import Select from 'primevue/select'
import { computed, ref, watch, onMounted, onBeforeUnmount, nextTick } from 'vue'
import {
    TrendingUpIcon, ShoppingBagIcon, CheckCircleIcon,
    BarChart3Icon, UsersIcon,
} from 'lucide-vue-next'
import {
    Chart,
    BarController, BarElement,
    LineController, LineElement,
    DoughnutController, ArcElement,
    CategoryScale, LinearScale,
    PointElement, Tooltip, Legend,
} from 'chart.js'

Chart.register(
    BarController, BarElement,
    LineController, LineElement,
    DoughnutController, ArcElement,
    CategoryScale, LinearScale,
    PointElement, Tooltip, Legend,
)

// ─── Types ────────────────────────────────────────────────
type Order = {
    id: number
    date: string
    price: number
    size: number
    approved: boolean
    client_id: number | null
    client_name: string
    location_id: number | null
    location_name: string
    types: Record<string, number>
}

const props = defineProps<{
    orders: Order[]
    clients: { id: number; name: string }[]
    locations: { id: number; name: string }[]
}>()

// ─── Filters ──────────────────────────────────────────────
const fDateFrom   = ref<Date | null>(null)
const fDateTo     = ref<Date | null>(null)
const fClientId   = ref<number | null>(null)
const fLocationId = ref<number | null>(null)

const hasFilters = computed(() =>
    !!(fDateFrom.value || fDateTo.value || fClientId.value || fLocationId.value)
)

const toISO = (d: Date | null) => {
    if (!d) return ''
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
}

const clearFilters = () => {
    fDateFrom.value = fDateTo.value = fClientId.value = fLocationId.value = null
}

// ─── Filtered orders ──────────────────────────────────────
const filtered = computed(() => {
    const from = toISO(fDateFrom.value)
    const to   = toISO(fDateTo.value)
    return props.orders.filter(o => {
        const d = o.date.slice(0, 10)
        if (from && d < from) return false
        if (to   && d > to)   return false
        if (fClientId.value   && o.client_id   !== fClientId.value)   return false
        if (fLocationId.value && o.location_id !== fLocationId.value) return false
        return true
    })
})

// ─── KPIs ─────────────────────────────────────────────────
const totalRevenue  = computed(() => filtered.value.reduce((s, o) => s + o.price, 0))
const totalOrders   = computed(() => filtered.value.length)
const approvedCount = computed(() => filtered.value.filter(o => o.approved).length)
const avgOrder      = computed(() => totalOrders.value ? totalRevenue.value / totalOrders.value : 0)
const clientCount   = computed(() =>
    new Set(filtered.value.filter(o => o.client_id).map(o => o.client_id)).size
)
const approvedPct = computed(() =>
    totalOrders.value ? Math.round(approvedCount.value / totalOrders.value * 100) : 0
)

// ─── Formatters ───────────────────────────────────────────
const fmt = (v: number) => {
    const [int, dec] = v.toFixed(2).split('.')
    return '€\u00A0' + int.replace(/\B(?=(\d{3})+(?!\d))/g, '\u00A0') + ',' + dec
}

// ─── Monthly data (last 12 months) ────────────────────────
const monthlyData = computed(() => {
    const now = new Date()
    const months: { key: string; label: string }[] = []
    for (let i = 11; i >= 0; i--) {
        const d   = new Date(now.getFullYear(), now.getMonth() - i, 1)
        const key = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`
        const label = d.toLocaleString('en', { month: 'short' }) + " '" + String(d.getFullYear()).slice(2)
        months.push({ key, label })
    }
    const map: Record<string, { revenue: number; count: number }> = {}
    months.forEach(m => { map[m.key] = { revenue: 0, count: 0 } })
    filtered.value.forEach(o => {
        const k = o.date.slice(0, 7)
        if (map[k]) { map[k].revenue += o.price; map[k].count++ }
    })
    return {
        labels:  months.map(m => m.label),
        revenue: months.map(m => +map[m.key].revenue.toFixed(2)),
        counts:  months.map(m => map[m.key].count),
    }
})

// ─── Top clients ──────────────────────────────────────────
const topClients = computed(() => {
    const map: Record<string, { name: string; revenue: number; count: number }> = {}
    filtered.value.forEach(o => {
        const k = String(o.client_id ?? '__none')
        if (!map[k]) map[k] = { name: o.client_name, revenue: 0, count: 0 }
        map[k].revenue += o.price
        map[k].count++
    })
    return Object.values(map).sort((a, b) => b.revenue - a.revenue).slice(0, 8)
})

// ─── By location ──────────────────────────────────────────
const byLocation = computed(() => {
    const map: Record<string, { name: string; revenue: number; count: number }> = {}
    filtered.value.forEach(o => {
        const k = String(o.location_id ?? '__none')
        if (!map[k]) map[k] = { name: o.location_name, revenue: 0, count: 0 }
        map[k].revenue += o.price
        map[k].count++
    })
    return Object.values(map).sort((a, b) => b.revenue - a.revenue)
})

// ─── Product type distribution ────────────────────────────
const typeDist = computed(() => {
    const c: Record<string, number> = { base: 0, vegan: 0, vegetarian: 0 }
    filtered.value.forEach(o => {
        Object.entries(o.types).forEach(([t, n]) => { c[t] = (c[t] ?? 0) + (n as number) })
    })
    return c
})

// ─── Chart refs & instances ───────────────────────────────
const refRevenue  = ref<HTMLCanvasElement | null>(null)
const refClients  = ref<HTMLCanvasElement | null>(null)
const refLocation = ref<HTMLCanvasElement | null>(null)
const refTypes    = ref<HTMLCanvasElement | null>(null)
const refApproved = ref<HTMLCanvasElement | null>(null)

// eslint-disable-next-line @typescript-eslint/no-explicit-any
let charts: any[] = []

const C = {
    indigo:    '#6366f1',
    indigoBg:  '#6366f1bb',
    sky:       '#38bdf8',
    skyBg:     '#38bdf8bb',
    green:     '#22c55e',
    greenLight:'#86efac',
    slate:     '#94a3b8',
    amber:     '#f59e0b',
    amberBg:   '#f59e0b22',
    palette:   ['#6366f1','#38bdf8','#22c55e','#f59e0b','#f43f5e','#a855f7','#14b8a6','#fb923c'],
}

const tooltipLabel = (unit: string) => ({
    callbacks: {
        label: (ctx: { raw: unknown }) => ` ${unit} ${Number(ctx.raw).toFixed(2)}`,
    },
})

function destroyAll() {
    charts.forEach(c => c?.destroy())
    charts = []
}

function drawRevenue() {
    if (!refRevenue.value) return
    const { labels, revenue, counts } = monthlyData.value
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const c = new Chart(refRevenue.value, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    type: 'bar' as const,
                    label: 'Revenue (€)',
                    data: revenue,
                    backgroundColor: C.indigoBg,
                    borderColor: C.indigo,
                    borderWidth: 1,
                    borderRadius: 4,
                    yAxisID: 'yRev',
                },
                {
                    type: 'line' as const,
                    label: 'Orders',
                    data: counts,
                    borderColor: C.amber,
                    backgroundColor: C.amberBg,
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointBackgroundColor: C.amber,
                    fill: false,
                    tension: 0.35,
                    yAxisID: 'yOrd',
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', labels: { boxWidth: 12, padding: 14 } },
                tooltip: {
                    callbacks: {
                        label: ctx =>
                            ctx.datasetIndex === 0
                                ? ` € ${Number(ctx.raw).toFixed(2)}`
                                : ` ${ctx.raw} orders`,
                    },
                },
            },
            scales: {
                yRev: {
                    position: 'left',
                    beginAtZero: true,
                    ticks: { callback: v => '€ ' + v },
                    grid: { color: '#f1f5f9' },
                },
                yOrd: {
                    position: 'right',
                    beginAtZero: true,
                    ticks: { precision: 0 },
                    grid: { drawOnChartArea: false },
                },
            },
        },
    } as never)
    charts.push(c)
}

function drawClients() {
    if (!refClients.value) return
    const data = topClients.value
    const c = new Chart(refClients.value, {
        type: 'bar',
        data: {
            labels: data.map(d => d.name),
            datasets: [{
                label: 'Revenue (€)',
                data: data.map(d => d.revenue),
                backgroundColor: C.indigoBg,
                borderColor: C.indigo,
                borderWidth: 1,
                borderRadius: 4,
            }],
        },
        options: {
            indexAxis: 'y' as const,
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx =>
                            ` € ${Number(ctx.raw).toFixed(2)}  ·  ${data[ctx.dataIndex].count} orders`,
                    },
                },
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { callback: v => '€ ' + v },
                    grid: { color: '#f1f5f9' },
                },
                y: { ticks: { font: { size: 11 } } },
            },
        },
    })
    charts.push(c)
}

function drawDoughnut(
    canvas: HTMLCanvasElement | null,
    labels: string[],
    data: number[],
    colors: string[],
    extra?: object,
) {
    if (!canvas || !data.some(v => v > 0)) return
    const c = new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{ data, backgroundColor: colors, borderWidth: 2, borderColor: '#fff' }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 12, padding: 10, font: { size: 11 } },
                },
                ...extra,
            },
        },
    })
    charts.push(c)
}

function renderAll() {
    nextTick(() => {
        destroyAll()
        drawRevenue()
        drawClients()
        drawDoughnut(
            refApproved.value,
            ['Approved', 'Draft'],
            [approvedCount.value, totalOrders.value - approvedCount.value],
            [C.green, C.slate],
        )
        drawDoughnut(
            refLocation.value,
            byLocation.value.map(l => l.name),
            byLocation.value.map(l => l.revenue),
            C.palette,
            { tooltip: tooltipLabel('€') },
        )
        drawDoughnut(
            refTypes.value,
            ['Base', 'Vegan', 'Vegetarian'],
            [typeDist.value.base || 0, typeDist.value.vegan || 0, typeDist.value.vegetarian || 0],
            [C.sky, C.green, C.greenLight],
        )
    })
}

onMounted(renderAll)
watch(filtered, renderAll)
onBeforeUnmount(destroyAll)
</script>

<template>
    <Head title="Dashboard" />
    <AppLayout>
        <div class="space-y-4 p-5">

            <!-- ── Filters ─────────────────────────────── -->
            <div class="flex flex-wrap items-end gap-3 rounded-xl border bg-white p-4 shadow-sm">
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-gray-500">Date from</label>
                    <DatePicker
                        v-model="fDateFrom"
                        dateFormat="dd.mm.yy"
                        placeholder="dd.mm.yyyy"
                        showIcon showButtonBar
                        :manualInput="false"
                        inputClass="!py-1.5 !text-sm"
                    />
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-gray-500">Date to</label>
                    <DatePicker
                        v-model="fDateTo"
                        dateFormat="dd.mm.yy"
                        placeholder="dd.mm.yyyy"
                        showIcon showButtonBar
                        :manualInput="false"
                        inputClass="!py-1.5 !text-sm"
                    />
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-gray-500">Client</label>
                    <Select
                        v-model="fClientId"
                        :options="clients"
                        optionLabel="name"
                        optionValue="id"
                        placeholder="All clients"
                        showClear
                        class="min-w-[160px] text-sm"
                    />
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-gray-500">Location</label>
                    <Select
                        v-model="fLocationId"
                        :options="locations"
                        optionLabel="name"
                        optionValue="id"
                        placeholder="All locations"
                        showClear
                        class="min-w-[150px] text-sm"
                    />
                </div>
                <button
                    v-if="hasFilters"
                    class="rounded-lg border px-3 py-1.5 text-sm text-gray-500 transition hover:border-red-300 hover:text-red-600"
                    @click="clearFilters"
                >
                    Clear
                </button>
                <div class="ml-auto self-end text-sm text-gray-400">
                    {{ filtered.length }} / {{ orders.length }} orders
                </div>
            </div>

            <!-- ── KPI cards ───────────────────────────── -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">

                <div class="rounded-xl border bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-400">Revenue</span>
                        <TrendingUpIcon class="h-4 w-4 text-indigo-400" />
                    </div>
                    <div class="mt-2 text-2xl font-bold tracking-tight text-gray-800">{{ fmt(totalRevenue) }}</div>
                </div>

                <div class="rounded-xl border bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-400">Orders</span>
                        <ShoppingBagIcon class="h-4 w-4 text-sky-400" />
                    </div>
                    <div class="mt-2 text-2xl font-bold tracking-tight text-gray-800">{{ totalOrders }}</div>
                </div>

                <div class="rounded-xl border bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-400">Approved</span>
                        <CheckCircleIcon class="h-4 w-4 text-green-400" />
                    </div>
                    <div class="mt-2 text-2xl font-bold tracking-tight text-gray-800">{{ approvedCount }}</div>
                    <div class="mt-0.5 text-xs text-gray-400">{{ approvedPct }}% of orders</div>
                </div>

                <div class="rounded-xl border bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-400">Avg order</span>
                        <BarChart3Icon class="h-4 w-4 text-amber-400" />
                    </div>
                    <div class="mt-2 text-2xl font-bold tracking-tight text-gray-800">{{ fmt(avgOrder) }}</div>
                </div>

                <div class="rounded-xl border bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-400">Clients</span>
                        <UsersIcon class="h-4 w-4 text-purple-400" />
                    </div>
                    <div class="mt-2 text-2xl font-bold tracking-tight text-gray-800">{{ clientCount }}</div>
                </div>

            </div>

            <!-- ── Revenue & Orders by month ──────────── -->
            <div class="rounded-xl border bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold text-gray-600">Revenue & Orders — last 12 months</h2>
                <div style="height:260px">
                    <canvas ref="refRevenue"></canvas>
                </div>
            </div>

            <!-- ── Top clients + Status ────────────────── -->
            <div class="grid gap-4 lg:grid-cols-3">
                <div class="rounded-xl border bg-white p-5 shadow-sm lg:col-span-2">
                    <h2 class="mb-4 text-sm font-semibold text-gray-600">Top clients by revenue</h2>
                    <div style="height:260px">
                        <canvas ref="refClients"></canvas>
                    </div>
                </div>
                <div class="rounded-xl border bg-white p-5 shadow-sm">
                    <h2 class="mb-4 text-sm font-semibold text-gray-600">Order status</h2>
                    <div style="height:260px">
                        <canvas ref="refApproved"></canvas>
                    </div>
                    <div class="mt-3 flex justify-center gap-4 text-sm">
                        <span class="font-semibold text-green-600">{{ approvedCount }} approved</span>
                        <span class="text-gray-400">{{ totalOrders - approvedCount }} draft</span>
                    </div>
                </div>
            </div>

            <!-- ── By location + Product types ───────── -->
            <div class="grid gap-4 lg:grid-cols-2">
                <div class="rounded-xl border bg-white p-5 shadow-sm">
                    <h2 class="mb-4 text-sm font-semibold text-gray-600">Revenue by location</h2>
                    <div v-if="byLocation.length" style="height:240px">
                        <canvas ref="refLocation"></canvas>
                    </div>
                    <div v-else class="flex h-40 items-center justify-center text-sm text-gray-400">
                        No data
                    </div>
                </div>
                <div class="rounded-xl border bg-white p-5 shadow-sm">
                    <h2 class="mb-4 text-sm font-semibold text-gray-600">Product type distribution</h2>
                    <div style="height:240px">
                        <canvas ref="refTypes"></canvas>
                    </div>
                    <div class="mt-3 flex justify-center gap-5 text-xs text-gray-500">
                        <span>Base: <b>{{ typeDist.base || 0 }}</b></span>
                        <span class="text-green-600">Vegan: <b>{{ typeDist.vegan || 0 }}</b></span>
                        <span class="text-green-400">Vegetarian: <b>{{ typeDist.vegetarian || 0 }}</b></span>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
