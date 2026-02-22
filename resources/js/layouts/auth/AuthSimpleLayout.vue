<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Link } from '@inertiajs/vue3';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex min-h-screen bg-white">

        <!-- ── Left brand panel ── -->
        <div class="hidden lg:flex lg:w-[44%] xl:w-[42%] flex-col relative overflow-hidden select-none"
             style="background: linear-gradient(145deg, #3c7042 0%, #4E9054 45%, #5aab60 100%);">

            <!-- Decorative blobs -->
            <div class="absolute -top-28 -left-28 w-[360px] h-[360px] rounded-full"
                 style="background: rgba(255,255,255,0.07);"></div>
            <div class="absolute -bottom-36 -right-20 w-[300px] h-[300px] rounded-full"
                 style="background: rgba(255,255,255,0.06);"></div>
            <div class="absolute top-1/2 -translate-y-1/2 -right-12 w-[140px] h-[140px] rounded-full"
                 style="background: rgba(255,255,255,0.04);"></div>

            <!-- Main content -->
            <div class="relative z-10 flex flex-col h-full px-10 xl:px-14 py-10 xl:py-12">

                <!-- Logo top -->
                <Link :href="home()" class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl"
                         style="background: rgba(255,255,255,0.18);">
                        <AppLogoIcon class="size-6 fill-current text-white" />
                    </div>
                    <span class="text-white text-lg font-bold tracking-wide">dal's kitchen</span>
                </Link>

                <!-- Center block -->
                <div class="flex-1 flex flex-col justify-center py-10">
                    <div class="inline-block mb-5">
                        <span class="text-xs font-semibold tracking-widest uppercase px-3 py-1 rounded-full"
                              style="background: rgba(255,255,255,0.15); color: rgba(255,255,255,0.85);">
                            Admin Panel
                        </span>
                    </div>

                    <h1 class="text-white font-bold leading-tight mb-5"
                        style="font-size: clamp(2rem, 3vw, 2.75rem);">
                        The flavors<br/>of life are<br/>just a bite away.
                    </h1>

                    <p style="color: rgba(255,255,255,0.65); font-size: 0.95rem; line-height: 1.7; max-width: 20rem;">
                        A real taste journey to Asia — manage orders,
                        clients and products from one place.
                    </p>

                    <!-- Feature pills -->
                    <div class="flex flex-col gap-2 mt-8">
                        <div v-for="item in ['Orders & Calculator', 'Clients & Locations', 'Products & Ingredients']"
                             :key="item"
                             class="flex items-center gap-2 text-sm"
                             style="color: rgba(255,255,255,0.75);">
                            <span class="w-1.5 h-1.5 rounded-full flex-shrink-0"
                                  style="background: rgba(255,255,255,0.5);"></span>
                            {{ item }}
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="text-xs" style="color: rgba(255,255,255,0.35);">
                    © {{ new Date().getFullYear() }} Dal's Kitchen. All rights reserved.
                </div>
            </div>
        </div>

        <!-- ── Right form panel ── -->
        <div class="flex-1 flex flex-col items-center justify-center px-6 py-12 bg-white">

            <!-- Mobile logo -->
            <Link :href="home()" class="flex lg:hidden items-center gap-2.5 mb-10">
                <div class="flex items-center justify-center w-9 h-9 rounded-lg"
                     style="background: #4E9054;">
                    <AppLogoIcon class="size-5 fill-current text-white" />
                </div>
                <span class="font-bold text-lg tracking-wide" style="color: #252525;">dal's kitchen</span>
            </Link>

            <!-- Form container -->
            <div class="w-full max-w-[360px]">

                <!-- Title -->
                <div class="mb-7">
                    <h2 class="text-2xl font-bold tracking-tight" style="color: #252525;">
                        {{ title }}
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">{{ description }}</p>
                </div>

                <slot />
            </div>
        </div>
    </div>
</template>
