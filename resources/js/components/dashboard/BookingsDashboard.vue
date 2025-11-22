<script setup lang="ts">
import { Booking } from '@/types';
import { formatCurrency, formatDate, getSourceClass, getStatusClass } from '@/utils';
import { Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

interface Props {
    bookings: Booking[];
}

const { bookings } = defineProps<Props>();

const statusFilter = ref<string>('all');

const statusOptions = [
    { value: 'all', label: 'All Bookings', count: bookings.length },
    { value: 'pending', label: 'Pending', count: bookings.filter(b => b.status === 'pending').length },
    { value: 'confirmed', label: 'Confirmed', count: bookings.filter(b => b.status === 'confirmed').length },
    { value: 'cancelled', label: 'Cancelled', count: bookings.filter(b => b.status === 'cancelled').length },
    { value: 'completed', label: 'Completed', count: bookings.filter(b => b.status === 'completed').length },
];

const filteredBookings = computed(() => {
    if (statusFilter.value === 'all') {
        return bookings;
    }
    return bookings.filter(booking => booking.status === statusFilter.value);
});

const pendingBookings = computed(() => {
    return bookings.filter(booking => booking.status === 'pending');
});

const upcomingCheckIns = computed(() => {
    const today = new Date();
    const nextWeek = new Date();
    nextWeek.setDate(today.getDate() + 7);
    
    return bookings.filter(booking => {
        const checkInDate = new Date(booking.check_in_date);
        return booking.status === 'confirmed' && 
               checkInDate >= today && 
               checkInDate <= nextWeek;
    });
});

const getGuestName = (booking: Booking) => {
    const name = `${booking.first_name || ''} ${booking.last_name || ''}`.trim();
    return name || 'No name provided';
};

const getDaysUntilCheckIn = (checkInDate: string) => {
    const today = new Date();
    const checkIn = new Date(checkInDate);
    const diffTime = checkIn.getTime() - today.getTime();
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
};
</script>

<template>
    <div class="space-y-6">
        <!-- Alert Sections -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <!-- Pending Bookings Alert -->
            <div v-if="pendingBookings.length > 0" class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-xl p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                            {{ pendingBookings.length }} Pending Booking{{ pendingBookings.length !== 1 ? 's' : '' }}
                        </h3>
                        <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                            These bookings require your attention for confirmation or rejection.
                        </p>
                        <div class="mt-2">
                            <Link 
                                href="/admin/bookings?status=pending"
                                class="text-sm font-medium text-yellow-800 dark:text-yellow-200 hover:text-yellow-600 dark:hover:text-yellow-100"
                            >
                                Review pending bookings →
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Check-ins -->
            <div v-if="upcomingCheckIns.length > 0" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-xl p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                            {{ upcomingCheckIns.length }} Upcoming Check-in{{ upcomingCheckIns.length !== 1 ? 's' : '' }}
                        </h3>
                        <p class="mt-1 text-sm text-blue-700 dark:text-blue-300">
                            Guests checking in within the next 7 days.
                        </p>
                        <div class="mt-2">
                            <Link 
                                href="/admin/bookings?status=confirmed"
                                class="text-sm font-medium text-blue-800 dark:text-blue-200 hover:text-blue-600 dark:hover:text-blue-100"
                            >
                                View upcoming arrivals →
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bookings Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recent Bookings</h3>
                    
                    <!-- Status Filter Tabs -->
                    <div class="mt-4 sm:mt-0">
                        <div class="flex space-x-1 bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
                            <button
                                v-for="option in statusOptions"
                                :key="option.value"
                                @click="statusFilter = option.value"
                                :class="[
                                    'px-3 py-2 text-sm font-medium rounded-md transition-colors',
                                    statusFilter === option.value
                                        ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-gray-100 shadow-sm'
                                        : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'
                                ]"
                            >
                                {{ option.label }}
                                <span v-if="option.count > 0" class="ml-1 text-xs opacity-75">({{ option.count }})</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Guest & Property
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Dates
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Status & Source
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Amount
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="booking in filteredBookings.slice(0, 10)" :key="booking.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ getGuestName(booking) }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ booking.property.title }}
                                        </div>
                                        <div class="text-xs text-gray-400 dark:text-gray-500">
                                            {{ booking.property.property_id }} • {{ booking.number_of_guests }} guests
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">
                                        {{ formatDate(new Date(booking.check_in_date)) }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        to {{ formatDate(new Date(booking.check_out_date)) }}
                                    </div>
                                    <div v-if="booking.status === 'confirmed'" class="text-xs text-blue-600 dark:text-blue-400">
                                        {{ getDaysUntilCheckIn(booking.check_in_date) }} days away
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="space-y-1">
                                        <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getStatusClass(booking.status)]">
                                            {{ booking.status.charAt(0).toUpperCase() + booking.status.slice(1) }}
                                        </span>
                                        <div>
                                            <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getSourceClass(booking.source)]">
                                                {{ booking.source.replace('_', '.') }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ formatCurrency(booking.total_price?? 0) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <Link 
                                        :href="route('admin.bookings.show', booking.id)"
                                        class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-200"
                                    >
                                        View
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600">
                <Link 
                    href="/admin/bookings"
                    class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 font-medium"
                >
                    View All Bookings →
                </Link>
            </div>
        </div>
    </div>
</template>