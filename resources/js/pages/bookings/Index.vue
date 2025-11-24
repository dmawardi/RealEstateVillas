<script setup lang="ts">
import { paginatedBookings, SEO } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';
import BookingList from '@/components/BookingList.vue';
import SEOHead from '@/components/SEOHead.vue';
import {router} from '@inertiajs/vue3'
import { computed, ref } from 'vue';

interface Props {
    bookings: paginatedBookings;
    seoData?: SEO;
    filters?: {
        search?: string;
        status?: string;
        source?: string;
        booking_type?: string;
        date_from?: string;
        date_to?: string;
    };
}

const { bookings, seoData, filters } = defineProps<Props>();
// Filter state
const searchTerm = ref(filters?.search || '');
const selectedStatus = ref(filters?.status || '');
const selectedSource = ref(filters?.source || '');
const selectedBookingType = ref(filters?.booking_type || '');
const dateFrom = ref(filters?.date_from || '');
const dateTo = ref(filters?.date_to || '');

// Status options
const statusOptions = [
    { value: '', label: 'All Statuses' },
    { value: 'pending', label: 'Pending' },
    { value: 'confirmed', label: 'Confirmed' },
    { value: 'cancelled', label: 'Cancelled' },
    { value: 'completed', label: 'Completed' },
    { value: 'withdrawn', label: 'Withdrawn' },
];
// Source options
const sourceOptions = [
    { value: '', label: 'All Sources' },
    { value: 'direct', label: 'Direct Booking' },
    { value: 'airbnb', label: 'Airbnb' },
    { value: 'booking_com', label: 'Booking.com' },
    { value: 'agoda', label: 'Agoda' },
    { value: 'other', label: 'Other' },
];

// Booking type options
const bookingTypeOptions = [
    { value: '', label: 'All Types' },
    { value: 'booking', label: 'Booking' },
    { value: 'inquiry', label: 'Inquiry' },
    { value: 'blocked', label: 'Blocked' },
    { value: 'maintenance', label: 'Maintenance' },
];

// Computed properties
const hasActiveFilters = computed(() => {
    return selectedStatus.value || selectedSource.value || selectedBookingType.value || 
           dateFrom.value || dateTo.value || searchTerm.value;
});

const hasBookings = computed(() => bookings.data.length > 0);

// Methods
const applyFilters = () => {
    const filterParams = {
        search: searchTerm.value || undefined,
        status: selectedStatus.value || undefined,
        source: selectedSource.value || undefined,
        booking_type: selectedBookingType.value || undefined,
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
    };

    // Remove empty values
    Object.keys(filterParams).forEach(key => {
        const typedKey = key as keyof typeof filterParams;
        if (filterParams[typedKey] === undefined || filterParams[typedKey] === '') {
            delete filterParams[typedKey];
        }
    });

    router.get('/my-bookings', filterParams, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    searchTerm.value = '';
    selectedStatus.value = '';
    selectedSource.value = '';
    selectedBookingType.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    
    router.get('/my-bookings', {}, {
        preserveState: true,
        replace: true,
    });
};

const handlePageChange = (page: number) => {
    router.get('/my-bookings', {
        ...filters,
        page
    }, {
        preserveState: true,
        replace: true,
    });
};
</script>

<template>
    <SEOHead v-if="seoData" :seoData="seoData" />

    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">My Bookings</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-2">
                    Manage your villa rental bookings and reservations
                </p>
            </div>

            <!-- Filters Section -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    Filter Bookings
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Search
                        </label>
                        <input
                            v-model="searchTerm"
                            type="text"
                            placeholder="Guest name, email, property..."
                            class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm"
                            @keyup.enter="applyFilters"
                        />
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Status
                        </label>
                        <select
                            v-model="selectedStatus"
                            class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm"
                        >
                            <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </div>

                    <!-- Source Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Source
                        </label>
                        <select
                            v-model="selectedSource"
                            class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm"
                        >
                            <option v-for="option in sourceOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </div>

                    <!-- Booking Type Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Type
                        </label>
                        <select
                            v-model="selectedBookingType"
                            class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm"
                        >
                            <option v-for="option in bookingTypeOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </div>

                    <!-- Date From -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Check-in From
                        </label>
                        <input
                            v-model="dateFrom"
                            type="date"
                            class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm"
                        />
                    </div>

                    <!-- Date To -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Check-out To
                        </label>
                        <input
                            v-model="dateTo"
                            type="date"
                            class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm"
                        />
                    </div>
                </div>

                <!-- Filter Actions -->
                <div class="flex items-center gap-3 mt-4">
                    <button
                        @click="applyFilters"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors"
                    >
                        Apply Filters
                    </button>
                    
                    <button
                        v-if="hasActiveFilters"
                        @click="clearFilters"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors"
                    >
                        Clear All
                    </button>
                    
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ bookings.total }} booking{{ bookings.total !== 1 ? 's' : '' }} found
                    </div>
                </div>
            </div>

            <!-- Bookings Content -->
            <div v-if="hasBookings">
                <!-- Results Summary -->
                <div class="mb-4 flex items-center justify-between">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        Showing {{ bookings.from }}-{{ bookings.to }} of {{ bookings.total }} bookings
                    </div>
                    <div v-if="bookings.last_page > 1" class="text-sm text-gray-500 dark:text-gray-400">
                        Page {{ bookings.current_page }} of {{ bookings.last_page }}
                    </div>
                </div>

                <!-- Booking List -->
                <BookingList :bookings="bookings.data" />

                <!-- Pagination -->
                <div v-if="bookings.last_page > 1" class="mt-8">
                    <PaginationControls
                        :current-page="bookings.current_page"
                        :total-pages="bookings.last_page"
                        @page-changed="handlePageChange"
                        variant="default"
                    />
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12">
                <div class="max-w-md mx-auto">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    
                    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ hasActiveFilters ? 'No bookings match your filters' : 'No bookings yet' }}
                    </h3>
                    
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        {{ hasActiveFilters 
                            ? 'Try adjusting your filters to find more bookings.' 
                            : 'Start exploring our properties and make your first booking.' }}
                    </p>

                    <div class="mt-6">
                        <button
                            v-if="hasActiveFilters"
                            @click="clearFilters"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors"
                        >
                            Clear Filters
                        </button>
                        <a
                            v-else
                            href="/properties"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors inline-block"
                        >
                            Browse Properties
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>