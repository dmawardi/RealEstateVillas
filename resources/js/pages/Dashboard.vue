<!-- resources/js/pages/Dashboard.vue -->
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Booking, Property, SEO, type BreadcrumbItem } from '@/types';
import DashboardStats from '@/components/dashboard/DashboardStats.vue';
import TopProperties from '@/components/dashboard/TopProperties.vue';
import BookingsDashboard from '@/components/dashboard/BookingsDashboard.vue';
import SEOHead from '@/components/SEOHead.vue';

interface Props {
    stats?: {
        total_properties: number;
        active_bookings: number;
        pending_bookings: number;
        monthly_revenue: number;
        properties_needing_pricing: Array<any>;
        recent_bookings: Array<any>;
    };
    topProperties?: Array<Property>;
    recentBookings?: Array<Booking>;
    seoData?: SEO;
}

const { stats, topProperties, recentBookings, seoData } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];
</script>

<template>
    <SEOHead :seoData="seoData" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Admin Dashboard -->
        <div v-if="stats" class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 overflow-x-auto">
            <!-- Dashboard Statistics -->
            <DashboardStats :stats="stats" />
            
            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <!-- Top Properties -->
                <TopProperties :properties="topProperties" />
                
                <!-- Bookings Dashboard -->
                <div class="xl:col-span-1">
                    <BookingsDashboard :bookings="recentBookings ?? []" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>