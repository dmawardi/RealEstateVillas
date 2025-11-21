<script setup lang="ts">
import { Booking } from '@/types';
import { formatDate, formatPrice } from '@/utils/formatters';
import { sourceLabels, statusLabels } from '@/utils/labels';
import {Link, router} from '@inertiajs/vue3'
import { computed } from 'vue';

interface Props {
    booking: Booking;
}

const {booking} = defineProps<Props>();

const nights = computed(() => {
    const checkIn = new Date(booking.check_in_date);
    const checkOut = new Date(booking.check_out_date);
    const diffTime = Math.abs(checkOut.getTime() - checkIn.getTime());
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
});

const getGuestName = (booking: Booking) => {
    const name = `${booking.first_name || ''} ${booking.last_name || ''}`.trim();
    return name || 'No name provided';
};
// Delete booking
const deleteBooking = (booking: Booking) => {
    const guestName = getGuestName(booking);
    if (confirm(`Are you sure you want to delete the booking for "${guestName}"?`)) {
        router.delete(route('admin.bookings.destroy', booking.id), {
            preserveScroll: true,
        });
    }
};

const getStatusClass = (status: string) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        confirmed: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        cancelled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        completed: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        blocked: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
        withdrawn: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
    };
    return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
};

const getSourceClass = (source: string) => {
    const classes = {
        direct: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        airbnb: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        booking_com: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200',
        agoda: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
        owner_blocked: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
        maintenance: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        other: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
    };
    return classes[source as keyof typeof classes] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
};
</script>
<template>
    <div 
        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 hover:shadow-md transition-shadow"
    >
        <div class="flex items-start justify-between">
            <div class="flex-1 min-w-0">
                <!-- Header Row -->
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center space-x-3">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ getGuestName(booking) }}
                        </h3>
                        <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getStatusClass(booking.status)]">
                            {{ statusLabels[booking.status] }}
                        </span>
                        <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getSourceClass(booking.source)]">
                            {{ sourceLabels[booking.source] }}
                        </span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            #{{ booking.id }}
                        </span>
                        <span v-if="booking.external_booking_id" class="text-sm text-gray-500 dark:text-gray-400">
                            ({{ booking.external_booking_id }})
                        </span>
                    </div>
                </div>

                <!-- Property and Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                    <div>
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Property</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">{{ booking.property.title }}</div>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Dates</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            {{ formatDate(new Date(booking.check_in_date)) }} - {{ formatDate(new Date(booking.check_out_date)) }}
                            <span v-if="nights" class="text-xs text-gray-500">
                                ({{ nights }} nights)
                            </span>
                        </div>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Guests</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            {{ booking.number_of_guests }} guests
                            <span v-if="booking.number_of_rooms">
                                • {{ booking.number_of_rooms }} rooms
                            </span>
                        </div>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Price</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            {{ formatPrice(booking.total_price ?? 0) }}
                            <span v-if="booking.commission_amount" class="text-xs text-gray-500">
                                ({{ formatPrice(booking.commission_amount) }} comm.)
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div v-if="booking.email || booking.phone" class="mb-3">
                    <div class="flex items-center space-x-4 text-sm text-gray-600 dark:text-gray-400">
                        <span v-if="booking.email">📧 {{ booking.email }}</span>
                        <span v-if="booking.phone">📞 {{ booking.phone }}</span>
                    </div>
                </div>

                <!-- Special Requests & Notes -->
                <div v-if="booking.special_requests || booking.notes" class="text-sm">
                    <div v-if="booking.special_requests" class="mb-1">
                        <span class="font-medium text-gray-900 dark:text-gray-100">Requests:</span>
                        <span class="text-gray-600 dark:text-gray-400">{{ booking.special_requests }}</span>
                    </div>
                    <div v-if="booking.notes" class="mb-1">
                        <span class="font-medium text-gray-900 dark:text-gray-100">Notes:</span>
                        <span class="text-gray-600 dark:text-gray-400">{{ booking.notes }}</span>
                    </div>
                </div>

                <!-- Created Date -->
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    Created {{ formatDate(new Date(booking.created_at)) }}
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-start space-x-2 ml-4">
                <Link 
                    :href="route('admin.bookings.show', booking.id)"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                >
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    View
                </Link>
                <Link 
                    :href="route('admin.bookings.edit', booking.id)"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                >
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </Link>
                <button
                    @click="deleteBooking(booking)"
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:bg-red-900 dark:text-red-200 dark:hover:bg-red-800 transition-colors"
                >
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Delete
                </button>
            </div>
        </div>
    </div>
</template>