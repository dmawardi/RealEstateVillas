<script setup lang="ts">
// filepath: /Users/d/Web Development/projects/RealEstate/resources/js/components/dashboard/DashboardStats.vue
import { Booking, Property } from '@/types';
import { formatCurrency } from '@/utils';
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import PropertyStatCard from '@/components/dashboard/PropertyStatCard.vue';
import PaginationControls from '../ui/navigation/PaginationControls.vue';

interface Stats {
    total_properties: number;
    active_bookings: number;
    pending_bookings: number;
    monthly_revenue: number;
    properties_needing_pricing: Property[];
    recent_bookings: Booking[];
}

interface Props {
    stats: Stats;
}

const { stats } = defineProps<Props>();

// Pagination state
const urgentCurrentPage = ref(1);
const noPricingCurrentPage = ref(1);
const warningCurrentPage = ref(1);
const itemsPerPage = 6;

const getPendingBookingsWarning = computed(() => {
    if (stats.pending_bookings === 0) return 'success';
    if (stats.pending_bookings <= 3) return 'warning';
    return 'error';
});

const getPricingWarning = computed(() => {
    if (stats.properties_needing_pricing.length === 0) return 'success';
    if (stats.properties_needing_pricing.length <= 2) return 'warning';
    return 'error';
});

// Group properties by urgency
const pricingByUrgency = computed(() => {
    const now = new Date();
    const threeMonthsFromNow = new Date();
    threeMonthsFromNow.setMonth(now.getMonth() + 3);
    
    const urgent: Property[] = [];
    const warning: Property[] = [];
    const noPricing: Property[] = [];
    
    stats.properties_needing_pricing.forEach(property => {
        if (!property.pricing || property.pricing.length === 0) {
            noPricing.push(property);
        } else {
            const latestEndDate = property.pricing
                .filter(p => p.end_date)
                .map(p => new Date(p.end_date!))
                .sort((a, b) => b.getTime() - a.getTime())[0];
            
            if (latestEndDate && latestEndDate <= threeMonthsFromNow) {
                urgent.push(property);
            } else {
                warning.push(property);
            }
        }
    });
    
    return { urgent, warning, noPricing };
});

// Pagination computed properties
const urgentPaginated = computed(() => {
    const startIndex = (urgentCurrentPage.value - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    return {
        items: pricingByUrgency.value.urgent.slice(startIndex, endIndex),
        totalPages: Math.ceil(pricingByUrgency.value.urgent.length / itemsPerPage),
        totalItems: pricingByUrgency.value.urgent.length
    };
});

const noPricingPaginated = computed(() => {
    const startIndex = (noPricingCurrentPage.value - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    return {
        items: pricingByUrgency.value.noPricing.slice(startIndex, endIndex),
        totalPages: Math.ceil(pricingByUrgency.value.noPricing.length / itemsPerPage),
        totalItems: pricingByUrgency.value.noPricing.length
    };
});

const warningPaginated = computed(() => {
    const startIndex = (warningCurrentPage.value - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    return {
        items: pricingByUrgency.value.warning.slice(startIndex, endIndex),
        totalPages: Math.ceil(pricingByUrgency.value.warning.length / itemsPerPage),
        totalItems: pricingByUrgency.value.warning.length
    };
});

// Pagination functions
const goToUrgentPage = (page: number) => {
    urgentCurrentPage.value = page;
};

const goToNoPricingPage = (page: number) => {
    noPricingCurrentPage.value = page;
};

const goToWarningPage = (page: number) => {
    warningCurrentPage.value = page;
};
</script>

<template>
    <div class="space-y-4">
        <!-- Stats Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Properties -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Properties</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ stats.total_properties }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Active Bookings -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Bookings</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ stats.active_bookings }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Pending Bookings -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending Bookings</p>
                        <div class="flex items-center space-x-2">
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ stats.pending_bookings }}</p>
                            <span v-if="getPendingBookingsWarning === 'warning'" class="w-3 h-3 bg-yellow-400 rounded-full animate-pulse" title="Needs attention"></span>
                            <span v-else-if="getPendingBookingsWarning === 'error'" class="w-3 h-3 bg-red-400 rounded-full animate-pulse" title="Urgent attention required"></span>
                        </div>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Monthly Revenue -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Monthly Revenue</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ formatCurrency(stats.monthly_revenue) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pricing Alert with Property List -->
        <div v-if="stats.properties_needing_pricing.length > 0" class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
            <div :class="[
                'p-4 border-l-4',
                getPricingWarning === 'warning' 
                    ? 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-400' 
                    : 'bg-red-50 dark:bg-red-900/20 border-red-400'
            ]">
                <div class="flex items-start justify-between">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg :class="[
                                'w-5 h-5',
                                getPricingWarning === 'warning' ? 'text-yellow-400' : 'text-red-400'
                            ]" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 :class="[
                                'text-sm font-medium',
                                getPricingWarning === 'warning' ? 'text-yellow-800 dark:text-yellow-200' : 'text-red-800 dark:text-red-200'
                            ]">
                                {{ stats.properties_needing_pricing.length }} Properties Need Pricing Update
                            </h3>
                            <p :class="[
                                'mt-1 text-sm',
                                getPricingWarning === 'warning' ? 'text-yellow-700 dark:text-yellow-300' : 'text-red-700 dark:text-red-300'
                            ]">
                                Properties with pricing ending within 6 months or no future pricing set.
                            </p>
                        </div>
                    </div>
                    <div class="ml-4 flex-shrink-0">
                        <Link 
                            href="/admin/properties?filter=pricing_needed"
                            :class="[
                                'text-sm font-medium hover:underline',
                                getPricingWarning === 'warning' ? 'text-yellow-800 dark:text-yellow-200' : 'text-red-800 dark:text-red-200'
                            ]"
                        >
                            View All →
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Properties List -->
            <div class="p-6 border-t border-gray-200 dark:border-gray-700">
                <!-- Urgent Properties (3 months or less) -->
                <div v-if="urgentPaginated.totalItems > 0" class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-sm font-semibold text-red-800 dark:text-red-200 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            Urgent (≤3 months) - {{ urgentPaginated.totalItems }} properties
                        </h4>
                        <div v-if="urgentPaginated.totalPages > 1" class="text-xs text-gray-500 dark:text-gray-400">
                            Page {{ urgentCurrentPage }} of {{ urgentPaginated.totalPages }}
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 mb-4">
                        <PropertyStatCard
                            v-for="property in urgentPaginated.items" 
                            :key="`urgent-${property.id}`"
                            :property="property"
                            card-class="bg-red-50 dark:bg-red-900/10 border-red-200 dark:border-red-800"
                            text-class="text-red-600 dark:text-red-400"
                        />
                    </div>

                    <!-- Urgent Pagination -->
                     <PaginationControls
                        :current-page="urgentCurrentPage"
                        :total-pages="urgentPaginated.totalPages"
                        @page-changed="goToUrgentPage"
                        variant="red"
                    />
                    
                </div>

                <!-- No Pricing Properties -->
                <div v-if="noPricingPaginated.totalItems > 0" class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-sm font-semibold text-red-800 dark:text-red-200 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                            No Future Pricing - {{ noPricingPaginated.totalItems }} properties
                        </h4>
                        <div v-if="noPricingPaginated.totalPages > 1" class="text-xs text-gray-500 dark:text-gray-400">
                            Page {{ noPricingCurrentPage }} of {{ noPricingPaginated.totalPages }}
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 mb-4">
                        <PropertyStatCard
                            v-for="property in noPricingPaginated.items" 
                            :key="`no-pricing-${property.id}`"
                            :property="property"
                            card-class="bg-red-50 dark:bg-red-900/10 border-red-200 dark:border-red-800"
                            text-class="text-red-600 dark:text-red-400"
                        />
                    </div>

                    <!-- No Pricing Pagination -->
                    <PaginationControls
                        :current-page="noPricingCurrentPage"
                        :total-pages="noPricingPaginated.totalPages"
                        @page-changed="goToNoPricingPage"
                        variant="red"
                    />
                </div>

                <!-- Warning Properties (3-6 months) -->
                <div v-if="warningPaginated.totalItems > 0">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-sm font-semibold text-yellow-800 dark:text-yellow-200 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Warning (3-6 months) - {{ warningPaginated.totalItems }} properties
                        </h4>
                        <div v-if="warningPaginated.totalPages > 1" class="text-xs text-gray-500 dark:text-gray-400">
                            Page {{ warningCurrentPage }} of {{ warningPaginated.totalPages }}
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 mb-4">
                        <PropertyStatCard
                            v-for="property in warningPaginated.items" 
                            :key="`warning-${property.id}`"
                            :property="property"
                            card-class="bg-yellow-50 dark:bg-yellow-900/10 border-yellow-200 dark:border-yellow-800"
                            text-class="text-yellow-600 dark:text-yellow-400"
                        />
                    </div>

                    <!-- Warning Pagination -->
                     
                    <PaginationControls
                        :current-page="warningCurrentPage"
                        :total-pages="warningPaginated.totalPages"
                        @page-changed="goToWarningPage"
                        variant="yellow"
                    />
                </div>
            </div>
        </div>
    </div>
</template>