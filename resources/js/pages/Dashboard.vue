<!-- resources/js/pages/Dashboard.vue -->
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import DashboardStats from '@/components/dashboard/DashboardStats.vue';
import TopProperties from '@/components/dashboard/TopProperties.vue';
import BookingsDashboard from '@/components/dashboard/BookingsDashboard.vue';

interface Props {
    stats: {
        total_properties: number;
        active_bookings: number;
        pending_bookings: number;
        monthly_revenue: number;
        properties_needing_pricing: Array<any>;
        recent_bookings: Array<any>;
    };
    topProperties: Array<any>;
    recentBookings: Array<any>;
}

const { stats, topProperties, recentBookings } = defineProps<Props>();

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
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 overflow-x-auto">
            <!-- Dashboard Statistics -->
            <DashboardStats :stats="stats" />
            
            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <!-- Top Properties -->
                <TopProperties :properties="topProperties" />
                
                <!-- Bookings Dashboard -->
                <div class="xl:col-span-1">
                    <BookingsDashboard :bookings="recentBookings" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>