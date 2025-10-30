<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { Booking } from '@/types';

interface Props {
    bookings: Booking[];
}

const { bookings } = defineProps<Props>();

// State for managing withdrawals
const withdrawingBooking = ref<number | null>(null);
const showWithdrawModal = ref(false);
const selectedBooking = ref<Booking | null>(null);

// Filter and sort bookings
const activeBookings = computed(() => 
    bookings.filter(booking => booking.status !== 'withdrawn' && booking.status !== 'cancelled')
);

const pastBookings = computed(() => 
    bookings.filter(booking => booking.status === 'withdrawn' || booking.status === 'cancelled' || booking.status === 'completed')
);

// Status styling
const getStatusClass = (status: string) => {
    const statusClasses = {
        'pending': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
        'confirmed': 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
        'cancelled': 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
        'withdrawn': 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400',
        'completed': 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
    };
    return statusClasses[status as keyof typeof statusClasses] || 'bg-gray-100 text-gray-800';
};

const getStatusIcon = (status: string) => {
    const icons = {
        'pending': '⏳',
        'confirmed': '✅',
        'cancelled': '❌',
        'withdrawn': '↩️',
        'completed': '🏁',
    };
    return icons[status as keyof typeof icons] || '📋';
};

// Format dates
const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const formatDateTime = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Calculate number of nights
const calculateNights = (checkIn: string, checkOut: string) => {
    const start = new Date(checkIn);
    const end = new Date(checkOut);
    const diffTime = Math.abs(end.getTime() - start.getTime());
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
};

// Withdrawal functions
const initiateWithdraw = (booking: Booking) => {
    selectedBooking.value = booking;
    showWithdrawModal.value = true;
};

const confirmWithdraw = () => {
    if (!selectedBooking.value) return;
    
    withdrawingBooking.value = selectedBooking.value.id;
    
    router.patch(route('bookings.withdraw', selectedBooking.value.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            showWithdrawModal.value = false;
            selectedBooking.value = null;
        },
        onFinish: () => {
            withdrawingBooking.value = null;
        }
    });
};

const cancelWithdraw = () => {
    showWithdrawModal.value = false;
    selectedBooking.value = null;
};

// Check if booking can be withdrawn
const canWithdraw = (booking: Booking) => {
    return booking.status === 'pending' || booking.status === 'confirmed';
};

// Get days until check-in
const getDaysUntilCheckIn = (checkInDate: string) => {
    const today = new Date();
    const checkIn = new Date(checkInDate);
    const diffTime = checkIn.getTime() - today.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
};
</script>

<template>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">My Bookings</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Manage your booking enquiries and view updates
            </p>
        </div>

        <!-- Active Bookings -->
        <div v-if="activeBookings.length > 0" class="mb-12">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-6">Active Bookings</h2>
            <div class="space-y-6">
                <div 
                    v-for="booking in activeBookings" 
                    :key="`active-${booking.id}`"
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden"
                >
                    <div class="p-6">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-4">
                            <div class="flex items-center space-x-4 mb-4 lg:mb-0">
                                <div class="flex-shrink-0">
                                    <span class="text-2xl">{{ getStatusIcon(booking.status) }}</span>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        <Link 
                                            :href="route('properties.show', booking.property.id)"
                                            class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                                        >
                                            {{ booking.property.title }}
                                        </Link>
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Booking #{{ booking.id }} • {{ formatDateTime(booking.created_at) }}
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-4">
                                <span :class="['px-3 py-1 rounded-full text-xs font-medium', getStatusClass(booking.status)]">
                                    {{ booking.status.charAt(0).toUpperCase() + booking.status.slice(1) }}
                                </span>
                                
                                <button
                                    v-if="canWithdraw(booking)"
                                    @click="initiateWithdraw(booking)"
                                    :disabled="withdrawingBooking === booking.id"
                                    class="px-4 py-2 text-sm font-medium text-red-600 hover:text-red-700 border border-red-300 hover:border-red-400 rounded-lg transition-colors disabled:opacity-50"
                                >
                                    {{ withdrawingBooking === booking.id ? 'Withdrawing...' : 'Withdraw' }}
                                </button>
                            </div>
                        </div>

                        <!-- Booking Details Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-4">
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Check-in</h4>
                                <p class="text-sm text-gray-900 dark:text-gray-100">{{ formatDate(booking.check_in_date) }}</p>
                                <p v-if="getDaysUntilCheckIn(booking.check_in_date) > 0" class="text-xs text-blue-600 dark:text-blue-400">
                                    {{ getDaysUntilCheckIn(booking.check_in_date) }} days away
                                </p>
                            </div>
                            
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Check-out</h4>
                                <p class="text-sm text-gray-900 dark:text-gray-100">{{ formatDate(booking.check_out_date) }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ calculateNights(booking.check_in_date, booking.check_out_date) }} nights
                                </p>
                            </div>
                            
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Guests</h4>
                                <p class="text-sm text-gray-900 dark:text-gray-100">{{ booking.number_of_guests }} guest{{ booking.number_of_guests > 1 ? 's' : '' }}</p>
                            </div>
                            
                            <div v-if="booking.total_price">
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Total</h4>
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    {{ new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(booking.total_price) }}
                                </p>
                            </div>
                        </div>

                        <!-- Special Requests -->
                        <div v-if="booking.special_requests" class="mb-4">
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Special Requests</h4>
                            <p class="text-sm text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                                {{ booking.special_requests }}
                            </p>
                        </div>

                        <!-- Status-specific information -->
                        <div v-if="booking.status === 'confirmed'" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                            <div class="flex">
                                <svg class="flex-shrink-0 w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-green-800 dark:text-green-300">
                                        Booking Confirmed!
                                    </h3>
                                    <p class="mt-1 text-sm text-green-700 dark:text-green-400">
                                        Your booking has been confirmed. You'll receive check-in details 24 hours before your arrival.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div v-else-if="booking.status === 'pending'" class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                            <div class="flex">
                                <svg class="flex-shrink-0 w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-300">
                                        Awaiting Confirmation
                                    </h3>
                                    <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-400">
                                        We're reviewing your booking request. You'll receive an update within 24 hours.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Past Bookings -->
        <div v-if="pastBookings.length > 0">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-6">Past Bookings</h2>
            <div class="space-y-4">
                <div 
                    v-for="booking in pastBookings" 
                    :key="`past-${booking.id}`"
                    class="bg-gray-50 dark:bg-gray-700/30 rounded-lg border border-gray-200 dark:border-gray-600 p-4"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="text-lg">{{ getStatusIcon(booking.status) }}</span>
                            <div>
                                <h3 class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ booking.property.title }}
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ formatDate(booking.check_in_date) }} - {{ formatDate(booking.check_out_date) }} • 
                                    {{ calculateNights(booking.check_in_date, booking.check_out_date) }} nights
                                </p>
                            </div>
                        </div>
                        <span :class="['px-3 py-1 rounded-full text-xs font-medium', getStatusClass(booking.status)]">
                            {{ booking.status.charAt(0).toUpperCase() + booking.status.slice(1) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="activeBookings.length === 0 && pastBookings.length === 0" class="text-center py-12">
            <div class="text-6xl mb-4">🏖️</div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No bookings yet</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6">Start planning your perfect vacation in Bali!</p>
            <Link 
                :href="route('properties.index')"
                class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors"
            >
                Browse Properties
            </Link>
        </div>
    </div>

    <!-- Withdraw Confirmation Modal -->
    <div v-if="showWithdrawModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="cancelWithdraw"></div>

            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                                Withdraw Booking
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Are you sure you want to withdraw your booking for 
                                    <strong>{{ selectedBooking?.property.title }}</strong>? 
                                    This action cannot be undone.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button
                        @click="confirmWithdraw"
                        :disabled="withdrawingBooking !== null"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
                    >
                        {{ withdrawingBooking ? 'Withdrawing...' : 'Withdraw Booking' }}
                    </button>
                    <button
                        @click="cancelWithdraw"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-600 dark:text-gray-200 dark:border-gray-500 dark:hover:bg-gray-500"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>