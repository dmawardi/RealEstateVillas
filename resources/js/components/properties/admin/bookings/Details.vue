<script setup lang="ts">
import { formatPrice, formatDate } from '@/utils';
import { Booking } from '@/types';

interface Props {
    show: boolean;
    booking: Booking | null;
}

interface Emits {
    (e: 'close'): void;
    (e: 'edit', booking: Booking): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

// Helper functions
const getBookingStatusClass = (status: string) => {
    const classes = {
        'confirmed': 'bg-green-100 text-green-800 border-green-200',
        'pending': 'bg-yellow-100 text-yellow-800 border-yellow-200',
        'cancelled': 'bg-red-100 text-red-800 border-red-200',
        'completed': 'bg-blue-100 text-blue-800 border-blue-200',
        'blocked': 'bg-gray-100 text-gray-800 border-gray-200'
    };
    return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-800 border-gray-200';
};

const getSourceIcon = (source: string) => {
    const icons = {
        'direct': '🏠',
        'airbnb': '🏡',
        'booking_com': '🏨',
        'agoda': '🛏️',
        'owner_blocked': '🔒',
        'maintenance': '🔧',
        'other': '📋'
    };
    return icons[source as keyof typeof icons] || '📋';
};

const closeModal = () => {
    emit('close');
};

const editBooking = () => {
    if (props.booking) {
        emit('edit', props.booking);
    }
};
</script>

<template>
    <Teleport to="body">
        <div
            v-if="show && booking"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click="closeModal"
        >
            <div
                class="bg-white dark:bg-gray-800 rounded-lg max-w-2xl w-full max-h-full overflow-y-auto"
                @click.stop
            >
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        Booking Details
                    </h3>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div class="p-6 space-y-6">
                    <!-- Guest Information -->
                    <div>
                        <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-3">Guest Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</span>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    {{ booking.first_name }} {{ booking.last_name || '' }}
                                </p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</span>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ booking.email }}</p>
                            </div>
                            <div v-if="booking.phone">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone</span>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ booking.phone }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Guests</span>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ booking.number_of_guests }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Booking Details -->
                    <div>
                        <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-3">Booking Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Check-in</span>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ formatDate(new Date(booking.check_in_date)) }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Check-out</span>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ formatDate(new Date(booking.check_out_date)) }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</span>
                                <div class="mt-1">
                                    <span :class="getBookingStatusClass(booking.status)" class="px-2 py-1 rounded-full text-xs font-medium border">
                                        {{ booking.status }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Source</span>
                                <div class="mt-1 flex items-center space-x-2">
                                    <span>{{ getSourceIcon(booking.source) }}</span>
                                    <span class="text-sm text-gray-900 dark:text-gray-100">{{ booking.source }}</span>
                                </div>
                            </div>
                            <div v-if="booking.booking_type">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Type</span>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ booking.booking_type }}</p>
                            </div>
                            <div v-if="booking.external_booking_id">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">External ID</span>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ booking.external_booking_id }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Information -->
                    <div v-if="booking.total_price">
                        <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-3">Financial Information</h4>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                Total: {{ formatPrice(booking.total_price) }}
                            </div>
                        </div>
                    </div>

                    <!-- Special Requests -->
                    <div v-if="booking.special_requests">
                        <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-3">Special Requests</h4>
                        <p class="text-sm text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            {{ booking.special_requests }}
                        </p>
                    </div>

                    <!-- Notes -->
                    <div v-if="booking.notes">
                        <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-3">Internal Notes</h4>
                        <p class="text-sm text-gray-900 dark:text-gray-100 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4">
                            {{ booking.notes }}
                        </p>
                    </div>

                    <!-- Timestamps -->
                    <div class="pt-4 border-t border-gray-200 dark:border-gray-600">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-gray-500 dark:text-gray-400">
                            <div v-if="booking.created_at">
                                <span class="font-medium">Created:</span>
                                {{ formatDate(new Date(booking.created_at)) }}
                            </div>
                            <div v-if="booking.updated_at">
                                <span class="font-medium">Updated:</span>
                                {{ formatDate(new Date(booking.updated_at)) }}
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-600">
                        <button
                            @click="closeModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            Close
                        </button>
                        <button
                            @click="editBooking"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            Edit Booking
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>