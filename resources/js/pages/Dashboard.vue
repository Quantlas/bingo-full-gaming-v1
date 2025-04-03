<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="rounded-xl bg-slate-50 transition duration-300 ease-in-out hover:-translate-y-1 dark:border-sidebar-border">
                    <a href="#" class="block">
                        <img
                            alt=""
                            src="https://plus.unsplash.com/premium_photo-1717810866948-5f975ec7fa36?q=80&w=1932&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                            class="h-64 w-full rounded-t-xl object-cover sm:h-80 lg:h-96"
                        />
                        <div class="p-4">
                            <h3 class="mt-4 text-lg font-bold text-gray-900 sm:text-xl">Sorteo Actual!</h3>

                            <p class="mt-2 max-w-sm text-gray-700">Este sorteo esta en progreso.</p>
                        </div>
                    </a>
                </div>
                <div class="rounded-xl bg-slate-50 transition duration-300 ease-in-out hover:-translate-y-1 dark:border-sidebar-border">
                    <a href="#" class="block">
                        <img
                            alt=""
                            src="https://plus.unsplash.com/premium_photo-1717972598410-6a47fc079a16?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                            class="h-64 w-full rounded-t-xl object-cover sm:h-80 lg:h-96"
                        />
                        <div class="p-4">
                            <h3 class="mt-4 text-lg font-bold text-gray-900 sm:text-xl">Proximo Sorteo</h3>

                            <p class="mt-2 max-w-sm text-gray-700">El proximo viernes a las 7 pm comienza el proximo sorteo.</p>
                        </div>
                    </a>
                </div>
                <div class="rounded-xl bg-slate-50 transition duration-300 ease-in-out hover:-translate-y-1 dark:border-sidebar-border">
                    <a href="#" class="block">
                        <img
                            alt=""
                            src="https://images.unsplash.com/photo-1576267423445-b2e0074d68a4?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                            class="h-64 w-full rounded-t-xl object-cover sm:h-80 lg:h-96"
                        />
                        <div class="p-4">
                            <h3 class="mt-4 text-lg font-bold text-gray-900 sm:text-xl">Ganadores Felices</h3>

                            <p class="mt-2 max-w-sm text-gray-700">Mira las historias de nuestros ganadores.</p>
                        </div>
                    </a>
                </div>
            </div>
            <!-- <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
            </div>
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border md:min-h-min">
                <PlaceholderPattern />
            </div> -->
        </div>
    </AppLayout>
</template>
