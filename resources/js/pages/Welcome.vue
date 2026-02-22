<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login } from '@/routes';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: false,
    },
);

const features = [
    {
        icon: '📦',
        title: 'Orders & Calculator',
        desc: 'Create orders, calculate pricing per kg with full cost breakdown.',
    },
    {
        icon: '👥',
        title: 'Clients & Locations',
        desc: 'Manage clients, delivery locations and transportation costs.',
    },
    {
        icon: '🥗',
        title: 'Products & Ingredients',
        desc: 'Build products from components and ingredients with live pricing.',
    },
]
</script>

<template>
    <Head title="Dal's Kitchen — Admin Panel" />

    <div class="min-h-screen flex flex-col" style="background: #f8faf8; font-family: 'Instrument Sans', sans-serif;">

        <!-- ── Navbar ── -->
        <header class="relative z-10 flex items-center justify-between px-6 py-5 lg:px-12">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-9 h-9 rounded-xl"
                     style="background: #4E9054;">
                    <AppLogoIcon class="size-5 fill-current text-white" />
                </div>
                <span class="font-bold text-lg tracking-wide" style="color: #252525;">Dal's kitchen</span>
            </div>

            <nav>
                <Link v-if="$page.props.auth.user"
                      :href="dashboard()"
                      class="inline-flex items-center gap-2 rounded-lg px-5 py-2 text-sm font-semibold text-white transition-all hover:opacity-90 active:scale-95"
                      style="background: #4E9054;">
                    Dashboard →
                </Link>
                <Link v-else
                      :href="login()"
                      class="inline-flex items-center gap-2 rounded-lg px-5 py-2 text-sm font-semibold text-white transition-all hover:opacity-90 active:scale-95"
                      style="background: #4E9054;">
                    Log in
                </Link>
            </nav>
        </header>

        <!-- ── Hero ── -->
        <main class="flex-1 flex flex-col items-center justify-center px-6 py-16 lg:py-24 text-center relative overflow-hidden">

            <!-- Soft radial glow -->
            <div class="absolute inset-0 pointer-events-none"
                 style="background: radial-gradient(ellipse 80% 50% at 50% -10%, rgba(78,144,84,0.10) 0%, transparent 70%);"></div>

            <!-- Logo mark -->
            <div class="relative z-10 mb-6">
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-3xl shadow-lg"
                     style="background: linear-gradient(145deg, #5aab60, #3a7040);">
                    <AppLogoIcon class="size-12 fill-current text-white" />
                </div>
            </div>

            <!-- Badge -->
            <div class="relative z-10 inline-flex items-center gap-2 mb-5 px-3 py-1 rounded-full border text-xs font-semibold tracking-widest uppercase"
                 style="border-color: #c8e6c9; background: #f0faf0; color: #4E9054;">
                <span class="w-1.5 h-1.5 rounded-full" style="background: #4E9054;"></span>
                Admin Panel
            </div>

            <!-- Headline -->
            <h1 class="relative z-10 font-bold leading-none mb-4"
                style="font-size: clamp(2.5rem, 6vw, 5rem); color: #252525; letter-spacing: -0.02em;">
                Dal's kitchen
            </h1>

            <p class="relative z-10 max-w-md mb-10 leading-relaxed" style="color: #6b7280; font-size: 1.05rem;">
                The flavors of life are just a bite away.<br/>
                <span class="text-sm">Manage orders, clients, products and pricing — all in one place.</span>
            </p>

            <!-- CTA -->
            <div class="relative z-10">
                <Link v-if="$page.props.auth.user"
                      :href="dashboard()"
                      class="inline-flex items-center gap-2.5 rounded-2xl px-8 py-4 text-base font-semibold text-white transition-all hover:opacity-95 active:scale-[0.98]"
                      style="background: linear-gradient(135deg, #4E9054 0%, #3a7040 100%); box-shadow: 0 8px 24px rgba(78,144,84,0.30);">
                    Go to Dashboard
                    <span class="text-lg">→</span>
                </Link>
                <Link v-else
                      :href="login()"
                      class="inline-flex items-center gap-2.5 rounded-2xl px-8 py-4 text-base font-semibold text-white transition-all hover:opacity-95 active:scale-[0.98]"
                      style="background: linear-gradient(135deg, #4E9054 0%, #3a7040 100%); box-shadow: 0 8px 24px rgba(78,144,84,0.30);">
                    Log in to Admin Panel
                    <span class="text-lg">→</span>
                </Link>
            </div>

            <!-- Tagline under CTA -->
            <p class="relative z-10 mt-4 text-xs" style="color: #9ca3af;">
                A real taste journey to Asia
            </p>
        </main>

        <!-- ── Features strip ── -->
        <section class="px-6 pb-14 lg:px-12">
            <div class="mx-auto max-w-3xl grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div v-for="f in features" :key="f.title"
                     class="rounded-2xl border p-5 flex items-start gap-3.5 transition-shadow hover:shadow-md bg-white"
                     style="border-color: #e8f4e8;">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 text-lg"
                         style="background: #f1f8f1;">
                        {{ f.icon }}
                    </div>
                    <div>
                        <div class="font-semibold text-sm mb-0.5" style="color: #252525;">{{ f.title }}</div>
                        <div class="text-xs leading-relaxed" style="color: #6b7280;">{{ f.desc }}</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Footer ── -->
        <footer class="px-6 py-5 lg:px-12 flex flex-wrap items-center justify-between gap-3 border-t"
                style="border-color: #e8f4e8;">
            <div class="flex items-center gap-2">
                <div class="flex items-center justify-center w-6 h-6 rounded-md" style="background: #4E9054;">
                    <AppLogoIcon class="size-3.5 fill-current text-white" />
                </div>
                <span class="text-xs font-semibold" style="color: #252525;">Dal's kitchen</span>
            </div>
            <p class="text-xs" style="color: #9ca3af;">
                © {{ new Date().getFullYear() }} Dal's Kitchen. All rights reserved.
            </p>
            <a href="https://dalskitchen.com" target="_blank" rel="noopener noreferrer"
               class="text-xs font-medium hover:underline transition-colors"
               style="color: #4E9054;">
                dalskitchen.com →
            </a>
        </footer>

    </div>
</template>
