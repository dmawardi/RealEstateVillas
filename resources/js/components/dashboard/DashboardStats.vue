<script setup lang="ts">
import { Booking, Property } from '@/types';
import { formatCurrency } from '@/utils';
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import PaginationControls from '../ui/navigation/PaginationControls.vue';

interface Stats {
    total_properties: number;
    active_bookings: number;
    pending_bookings: number;
    monthly_revenue: number;
    properties_needing_pricing: {
        no_pricing: Property[];
        ending_soon: Property[];
        no_active_pricing: Property[];
        total_count: number;
    };
    recent_bookings: Booking[];
}

interface Props {
    stats: Stats;
}

const { stats } = defineProps<Props>();

// Pagination state
const currentPage = ref(1);
const itemsPerPage = 6;

const getPendingBookingsWarning = computed(() => {
    if (stats.pending_bookings === 0) return 'success';
    if (stats.pending_bookings <= 3) return 'warning';
    return 'error';
});

const getPricingWarning = computed(() => {
    if (stats.properties_needing_pricing.total_count === 0) return 'success';
    if (stats.properties_needing_pricing.total_count <= 2) return 'warning';
    return 'error';
});

// Combine all properties in order of priority: no_pricing (highest), no_active_pricing, ending_soon (lowest)
const allPropertiesSorted = computed(() => {
    const categorizedProperties: Array<Property & { category: string; urgency: string; displayText: string }> = [];
    
    // Add no_pricing properties first (highest priority)
    stats.properties_needing_pricing.no_pricing.forEach(property => {
        categorizedProperties.push({
            ...property,
            category: 'no_pricing',
            urgency: 'urgent',
            displayText: 'No pricing set'
        });
    });
    
    // Add no_active_pricing properties second
    stats.properties_needing_pricing.no_active_pricing.forEach(property => {
        categorizedProperties.push({
            ...property,
            category: 'no_active_pricing',
            urgency: 'urgent',
            displayText: 'No active pricing'
        });
    });
    
    // Add ending_soon properties last (lowest priority)
    stats.properties_needing_pricing.ending_soon.forEach(property => {
        categorizedProperties.push({
            ...property,
            category: 'ending_soon',
            urgency: 'warning',
            displayText: 'Pricing ending soon'
        });
    });
    
    return categorizedProperties;
});

// Pagination for sorted list
const sortedPaginated = computed(() => {
    const startIndex = (currentPage.value - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    return {
        items: allPropertiesSorted.value.slice(startIndex, endIndex),
        totalPages: Math.ceil(allPropertiesSorted.value.length / itemsPerPage),
        totalItems: allPropertiesSorted.value.length
    };
});

// Count urgent vs warning
const urgentCount = computed(() => 
    stats.properties_needing_pricing.no_pricing.length + stats.properties_needing_pricing.no_active_pricing.length
);

const warningCount = computed(() => 
    stats.properties_needing_pricing.ending_soon.length
);

// Pagination function
const goToPage = (page: number) => {
    currentPage.value = page;
};

console.log('Properties needing pricing', { 'properties needing pricing': stats.properties_needing_pricing });
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
        <div v-if="stats.properties_needing_pricing.total_count > 0" class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
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
                                {{ stats.properties_needing_pricing.total_count }} Properties Need Pricing Update
                            </h3>
                            <p :class="[
                                'mt-1 text-sm',
                                getPricingWarning === 'warning' ? 'text-yellow-700 dark:text-yellow-300' : 'text-red-700 dark:text-red-300'
                            ]">
                                {{ urgentCount }} urgent • {{ warningCount }} warning
                            </p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <span v-if="stats.properties_needing_pricing.no_pricing.length > 0" class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-200">
                                    {{ stats.properties_needing_pricing.no_pricing.length }} No Pricing
                                </span>
                                <span v-if="stats.properties_needing_pricing.no_active_pricing.length > 0" class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-200">
                                    {{ stats.properties_needing_pricing.no_active_pricing.length }} No Active Pricing
                                </span>
                                <span v-if="stats.properties_needing_pricing.ending_soon.length > 0" class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-200">
                                    {{ stats.properties_needing_pricing.ending_soon.length }} Ending Soon
                                </span>
                            </div>
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
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                        Properties by Priority
                    </h4>
                    <div v-if="sortedPaginated.totalPages > 1" class="text-xs text-gray-500 dark:text-gray-400">
                        Page {{ currentPage }} of {{ sortedPaginated.totalPages }}
                    </div>
                </div>
                
                <div class="space-y-3 mb-4">
                    <div
                        v-for="property in sortedPaginated.items" 
                        :key="property.id"
                        :class="[
                            'p-4 rounded-lg border flex items-center justify-between',
                            property.urgency === 'urgent' 
                                ? 'bg-red-50 dark:bg-red-900/10 border-red-200 dark:border-red-800' 
                                : 'bg-yellow-50 dark:bg-yellow-900/10 border-yellow-200 dark:border-yellow-800'
                        ]"
                    >
                        <div class="flex items-center space-x-4 flex-1">
                            <div :class="[
                                'w-3 h-3 rounded-full flex-shrink-0',
                                property.urgency === 'urgent' ? 'bg-red-500' : 'bg-yellow-500'
                            ]"></div>
                            
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h5 :class="[
                                            'font-medium truncate',
                                            property.urgency === 'urgent' 
                                                ? 'text-red-900 dark:text-red-100' 
                                                : 'text-yellow-900 dark:text-yellow-100'
                                        ]">
                                            {{ property.title }}
                                        </h5>
                                        <p :class="[
                                            'text-sm',
                                            property.urgency === 'urgent' 
                                                ? 'text-red-600 dark:text-red-300' 
                                                : 'text-yellow-600 dark:text-yellow-300'
                                        ]">
                                            {{ property.property_id }}
                                        </p>
                                    </div>
                                    
                                    <div class="text-right">
                                        <span :class="[
                                            'inline-flex items-center px-2 py-1 rounded-md text-xs font-medium',
                                            property.category === 'no_pricing' ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-200' :
                                            property.category === 'no_active_pricing' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-200' :
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-200'
                                        ]">
                                            {{ property.displayText }}
                                        </span>
                                        <div :class="[
                                            'text-xs mt-1 font-semibold',
                                            property.urgency === 'urgent' 
                                                ? 'text-red-500 dark:text-red-400' 
                                                : 'text-yellow-500 dark:text-yellow-400'
                                        ]">
                                            {{ property.urgency === 'urgent' ? 'URGENT' : 'WARNING' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <Link 
                            :href="route('admin.properties.show', property.slug)"
                            :class="[
                                'ml-4 px-3 py-1 text-xs font-medium rounded-md hover:opacity-80 transition-opacity',
                                property.urgency === 'urgent' 
                                    ? 'bg-red-600 text-white' 
                                    : 'bg-yellow-600 text-white'
                            ]"
                        >
                            Update Pricing
                        </Link>
                    </div>
                </div>

                <PaginationControls
                    v-if="sortedPaginated.totalPages > 1"
                    :current-page="currentPage"
                    :total-pages="sortedPaginated.totalPages"
                    @page-changed="goToPage"
                />
            </div>
        </div>
    </div>
</template>