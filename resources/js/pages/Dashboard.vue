<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { DicesIcon, Users } from 'lucide-vue-next';

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
        <div class="flex h-full flex-1 flex-col rounded-xl p-4">
            <div class="grid auto-rows-min md:grid-cols-4">
                <div
                    class="min-h-52 max-w-sm cursor-pointer rounded-lg border border-gray-200 bg-white p-6 text-center shadow-sm hover:opacity-90 dark:border-gray-700 dark:bg-gray-800"
                >
                    <Link :href="route('games.index')">
                        <div class="mb-4 flex justify-center">
                            <component class="h-10 w-10" :is="DicesIcon" />
                        </div>

                        <h5 class="mb-2 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">Juegos</h5>

                        <p class="mb-3 font-normal text-gray-500 dark:text-gray-400">Crea y administra los juegos y sus sorteos.</p>
                    </Link>
                </div>

                <div
                    class="min-h-52 max-w-sm cursor-pointer rounded-lg border border-gray-200 bg-white p-6 text-center shadow-sm hover:opacity-90 dark:border-gray-700 dark:bg-gray-800"
                >
                    <Link :href="route('players.index')">
                        <div class="mb-4 flex justify-center">
                            <component class="h-10 w-10" :is="Users" />
                        </div>

                        <h5 class="mb-2 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">Jugadores</h5>

                        <p class="mb-3 font-normal text-gray-500 dark:text-gray-400">Administra las compras y cartones de los jugadores.</p>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
