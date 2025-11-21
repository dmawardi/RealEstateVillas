<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, computed } from 'vue';
import { formatPaginationLabel } from '@/utils';
import BookingCard from '@/components/bookings/BookingCard.vue';
import { paginatedBookings } from '@/types';

interface Props {
    bookings: paginatedBookings;
    filters: {
        search?: string;
        status?: string | string[];
        source?: string | string[];
        booking_type?: string | string[];
        property_id?: number;
        check_in_from?: string;
        check_in_to?: string;
        created_from?: string;
        created_to?: string;
        commission_paid?: boolean;
    };
    statusOptions: Record<string, string>;
    sourceOptions: Record<string, string>;
    bookingTypeOptions: Record<string, string>;
    properties: Array<{
        id: number;
        title: string;
        property_id: string;
    }>;
    stats: {
        total_bookings: number;
        pending_bookings: number;
        confirmed_bookings: number;
        total_revenue: number;
        commission_owed: number;
    };
}

const { bookings, filters, statusOptions, sourceOptions, bookingTypeOptions, properties, stats } = defineProps<Props>();

// Filter state
const searchQuery = ref(filters.search || '');
const selectedStatus = ref(filters.status || '');
const selectedSource = ref(filters.source || '');
const selectedBookingType = ref(filters.booking_type || '');
const selectedProperty = ref(filters.property_id || '');
const checkInFrom = ref(filters.check_in_from || '');
const checkInTo = ref(filters.check_in_to || '');
const createdFrom = ref(filters.created_from || '');
const createdTo = ref(filters.created_to || '');
const commissionPaid = ref(filters.commission_paid !== undefined ? filters.commission_paid : '');

const search = () => {
    router.get(route('admin.bookings.index'), {
        search: searchQuery.value,
        status: selectedStatus.value,
        source: selectedSource.value,
        booking_type: selectedBookingType.value,
        property_id: selectedProperty.value,
        check_in_from: checkInFrom.value,
        check_in_to: checkInTo.value,
        created_from: createdFrom.value,
        created_to: createdTo.value,
        commission_paid: commissionPaid.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    searchQuery.value = '';
    selectedStatus.value = '';
    selectedSource.value = '';
    selectedBookingType.value = '';
    selectedProperty.value = '';
    checkInFrom.value = '';
    checkInTo.value = '';
    createdFrom.value = '';
    createdTo.value = '';
    commissionPaid.value = '';
    router.get(route('admin.bookings.index'), {}, {
        preserveState: true,
        replace: true,
    });
};

const hasFilters = computed(() => {
    return searchQuery.value || selectedStatus.value || selectedSource.value || 
           selectedBookingType.value || selectedProperty.value || checkInFrom.value || 
           checkInTo.value || createdFrom.value || createdTo.value || commissionPaid.value;
});

// Utility functions
const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
};
</script>

<template>
    <Head title="Bookings Management" />

    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 lg:p-8">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-6">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                                    Bookings Management
                                </h1>
                                <p class="text-gray-600 dark:text-gray-400">
                                    Manage property bookings and reservations
                                </p>
                            </div>
                            <div class="mt-4 lg:mt-0">
                                <Link 
                                    :href="route('admin.bookings.create')"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    New Booking
                                </Link>
                            </div>
                        </div>

                        <!-- Stats Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ stats.total_bookings }}</div>
                                <div class="text-sm text-blue-800 dark:text-blue-300">Total Bookings</div>
                            </div>
                            <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4">
                                <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ stats.pending_bookings }}</div>
                                <div class="text-sm text-yellow-800 dark:text-yellow-300">Pending</div>
                            </div>
                            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                                <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ stats.confirmed_bookings }}</div>
                                <div class="text-sm text-green-800 dark:text-green-300">Confirmed</div>
                            </div>
                            <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-lg p-4">
                                <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ formatCurrency(stats.total_revenue) }}</div>
                                <div class="text-sm text-indigo-800 dark:text-indigo-300">Total Revenue</div>
                            </div>
                            <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4">
                                <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ formatCurrency(stats.commission_owed) }}</div>
                                <div class="text-sm text-red-800 dark:text-red-300">Commission Owed</div>
                            </div>
                        </div>

                        <!-- Filters -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 mb-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                                <!-- Search -->
                                <div class="lg:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Search Bookings
                                    </label>
                                    <input
                                        v-model="searchQuery"
                                        type="text"
                                        placeholder="Search by guest name, email, booking ID, property..."
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        @keyup.enter="search"
                                    />
                                </div>

                                <!-- Status Filter -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Status
                                    </label>
                                    <select 
                                        v-model="selectedStatus"
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                        <option value="">All Statuses</option>
                                        <option v-for="(label, value) in statusOptions" :key="value" :value="value">
                                            {{ label }}
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
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                        <option value="">All Sources</option>
                                        <option v-for="(label, value) in sourceOptions" :key="value" :value="value">
                                            {{ label }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                                <!-- Booking Type Filter -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Type
                                    </label>
                                    <select 
                                        v-model="selectedBookingType"
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                        <option value="">All Types</option>
                                        <option v-for="(label, value) in bookingTypeOptions" :key="value" :value="value">
                                            {{ label }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Property Filter -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Property
                                    </label>
                                    <select 
                                        v-model="selectedProperty"
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                        <option value="">All Properties</option>
                                        <option v-for="property in properties" :key="property.id" :value="property.id">
                                            {{ property.title }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Commission Filter -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Commission
                                    </label>
                                    <select 
                                        v-model="commissionPaid"
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                        <option value="">All</option>
                                        <option value="true">Paid</option>
                                        <option value="false">Unpaid</option>
                                    </select>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex items-end space-x-2">
                                    <button
                                        @click="search"
                                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
                                    >
                                        Search
                                    </button>
                                    <button
                                        v-if="hasFilters"
                                        @click="clearFilters"
                                        class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors"
                                    >
                                        Clear
                                    </button>
                                </div>
                            </div>

                            <!-- Date Filters -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Check-in From
                                    </label>
                                    <input
                                        v-model="checkInFrom"
                                        type="date"
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Check-in To
                                    </label>
                                    <input
                                        v-model="checkInTo"
                                        type="date"
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Created From
                                    </label>
                                    <input
                                        v-model="createdFrom"
                                        type="date"
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Created To
                                    </label>
                                    <input
                                        v-model="createdTo"
                                        type="date"
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Bookings List -->
                        <div v-if="bookings.data && bookings.data.length > 0" class="space-y-4">
                            <BookingCard 
                                v-for="booking in bookings.data" 
                                :booking="booking" 
                                :key="booking.id"
                            />
                        </div>

                        <!-- Empty State -->
                        <div v-else class="text-center py-12">
                            <div class="mx-auto h-12 w-12 text-gray-400">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 48 48">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 011-1h30a1 1 0 011 1v4M8 7h32M8 7l2 36h28l2-36M18 17v16M24 17v16M30 17v16" />
                                </svg>
                            </div>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No bookings found</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ hasFilters ? 'Try adjusting your search criteria.' : 'Get started by creating your first booking.' }}
                            </p>
                            <div class="mt-6">
                                <Link 
                                    v-if="!hasFilters"
                                    :href="route('admin.bookings.create')"
                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    New Booking
                                </Link>
                                <button 
                                    v-else
                                    @click="clearFilters"
                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                                >
                                    Clear Filters
                                </button>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="bookings.data && bookings.data.length > 0 && bookings.last_page > 1" class="mt-8">
                            <nav class="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3 sm:px-6">
                                <div class="hidden sm:block">
                                    <p class="text-sm text-gray-700 dark:text-gray-300">
                                        Showing
                                        <span class="font-medium">{{ ((bookings.current_page - 1) * bookings.per_page) + 1 }}</span>
                                        to
                                        <span class="font-medium">{{ Math.min(bookings.current_page * bookings.per_page, bookings.total) }}</span>
                                        of
                                        <span class="font-medium">{{ bookings.total }}</span>
                                        results
                                    </p>
                                </div>
                                <div class="flex-1 flex justify-between sm:justify-end">
                                    <Link 
                                        v-for="link in bookings.links" 
                                        :key="link.label"
                                        :href="link.url ?? ''"
                                        :class="[
                                            'relative inline-flex items-center px-4 py-2 text-sm font-medium border transition-colors',
                                            link.active 
                                                ? 'z-10 bg-blue-50 border-blue-500 text-blue-600 dark:bg-blue-900 dark:border-blue-400 dark:text-blue-300' 
                                                : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700',
                                            !link.url ? 'cursor-not-allowed opacity-50' : 'cursor-pointer'
                                        ]"
                                        :preserve-scroll="true"
                                        :aria-disabled="!link.url"
                                    >
                                        {{ formatPaginationLabel(link.label) }}
                                    </Link>
                                </div>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>