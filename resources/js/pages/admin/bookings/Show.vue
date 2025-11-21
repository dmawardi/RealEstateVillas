<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { computed } from 'vue';
import { Booking } from '@/types';
import { formatCurrency, formatDate } from '@/utils';
import { bookingTypeLabels, sourceLabels, statusLabels } from '@/utils/labels';

interface Props {
    booking: Booking;
}

const { booking } = defineProps<Props>();

// Computed properties
const guestName = computed(() => {
    const name = `${booking.first_name || ''} ${booking.last_name || ''}`.trim();
    return name || 'No name provided';
});

const nights = computed(() => {
    const checkIn = new Date(booking.check_in_date);
    const checkOut = new Date(booking.check_out_date);
    const diffTime = checkOut.getTime() - checkIn.getTime();
    return Math.max(0, Math.ceil(diffTime / (1000 * 60 * 60 * 24)));
});

const daysUntilCheckIn = computed(() => {
    const checkIn = new Date(booking.check_in_date);
    const today = new Date();
    const diffTime = checkIn.getTime() - today.getTime();
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
});

const isUpcoming = computed(() => daysUntilCheckIn.value > 0);
const isActive = computed(() => {
    const today = new Date();
    const checkIn = new Date(booking.check_in_date);
    const checkOut = new Date(booking.check_out_date);
    return today >= checkIn && today < checkOut;
});

// Status styling
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

// Action handlers
const sendEmail = (email: string) => {
    window.location.href = `mailto:${email}`;
};

const callGuest = (phone: string) => {
    window.location.href = `tel:${phone}`;
};

const printDetails = () => {
    window.print();
};
</script>

<template>
    <Head :title="`Booking #${booking.id} - ${guestName}`" />

    <AppLayout>
        <div class="py-12">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg mb-6">
                    <div class="p-6 lg:p-8">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-6">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 truncate">
                                        {{ guestName }}
                                    </h1>
                                    <span :class="['inline-flex items-center px-3 py-1 rounded-full text-sm font-medium', getStatusClass(booking.status)]">
                                        {{ statusLabels[booking.status] }}
                                    </span>
                                    <span :class="['inline-flex items-center px-3 py-1 rounded-full text-sm font-medium', getSourceClass(booking.source)]">
                                        {{ sourceLabels[booking.source] }}
                                    </span>
                                </div>
                                <div class="flex items-center space-x-4 text-sm text-gray-600 dark:text-gray-400">
                                    <span>Booking #{{ booking.id }}</span>
                                    <span v-if="booking.external_booking_id">{{ booking.external_booking_id }}</span>
                                    <span>{{ bookingTypeLabels[booking.booking_type] }}</span>
                                </div>
                                <!-- Status Indicators -->
                                <div class="flex items-center space-x-4 mt-2">
                                    <div v-if="isActive" class="flex items-center text-green-600 dark:text-green-400">
                                        <div class="w-2 h-2 bg-green-600 dark:bg-green-400 rounded-full mr-2 animate-pulse"></div>
                                        <span class="text-sm font-medium">Currently Active</span>
                                    </div>
                                    <div v-else-if="isUpcoming && booking.status === 'confirmed'" class="flex items-center text-blue-600 dark:text-blue-400">
                                        <div class="w-2 h-2 bg-blue-600 dark:bg-blue-400 rounded-full mr-2"></div>
                                        <span class="text-sm font-medium">{{ daysUntilCheckIn }} days until check-in</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 lg:mt-0 flex space-x-3">
                                <Link 
                                    :href="route('admin.bookings.edit', booking.id)"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit Booking
                                </Link>
                                <Link 
                                    :href="route('admin.bookings.index')"
                                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                    </svg>
                                    Back to List
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Column - Main Details -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Property Information -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Property Details</h3>
                                <div class="space-y-3">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="font-semibold text-gray-900 dark:text-gray-100">{{ booking.property.title }}</h4>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ booking.property.street_name }}, {{ booking.property.district }}, {{ booking.property.regency }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-500">
                                                Property ID: {{ booking.property.property_id }}
                                            </p>
                                        </div>
                                        <Link 
                                            :href="route('properties.show', booking.property.slug)"
                                            class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 text-sm"
                                        >
                                            View Property →
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Booking Details -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Booking Details</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Check-in -->
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Check-in</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ formatDate(new Date(booking.check_in_date)) }}</dd>
                                    </div>
                                    <!-- Check-out -->
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Check-out</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ formatDate(new Date(booking.check_out_date)) }}</dd>
                                    </div>
                                    <!-- Duration -->
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Duration</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ nights }} night{{ nights !== 1 ? 's' : '' }}</dd>
                                    </div>
                                    <!-- Guests -->
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Guests</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                            {{ booking.number_of_guests }} guest{{ booking.number_of_guests !== 1 ? 's' : '' }}
                                            <span v-if="booking.number_of_rooms"> • {{ booking.number_of_rooms }} room{{ booking.number_of_rooms !== 1 ? 's' : '' }}</span>
                                        </dd>
                                    </div>
                                    <!-- Flexible Dates -->
                                    <div v-if="booking.flexible_dates">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Flexible Dates</dt>
                                        <dd class="mt-1">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                Yes
                                            </span>
                                        </dd>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Guest Information -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Guest Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Name -->
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ guestName }}</dd>
                                    </div>
                                    <!-- Email -->
                                    <div v-if="booking.email">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                            <a :href="`mailto:${booking.email}`" class="text-blue-600 dark:text-blue-400 hover:underline">
                                                {{ booking.email }}
                                            </a>
                                        </dd>
                                    </div>
                                    <!-- Phone -->
                                    <div v-if="booking.phone">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                            <a :href="`tel:${booking.phone}`" class="text-blue-600 dark:text-blue-400 hover:underline">
                                                {{ booking.phone }}
                                            </a>
                                        </dd>
                                    </div>
                                    <!-- User Account -->
                                    <div v-if="booking.user">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">User Account</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                            <!-- <Link 
                                                :href="route('admin.users.show', booking.user.id)"
                                                class="text-blue-600 dark:text-blue-400 hover:underline"
                                            >
                                                View Profile →
                                            </Link> -->
                                        </dd>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Special Requests & Notes -->
                        <div v-if="booking.special_requests || booking.notes" class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Additional Information</h3>
                                <div class="space-y-4">
                                    <!-- Special Requests -->
                                    <div v-if="booking.special_requests">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Special Requests</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-gray-700 p-3 rounded-md">
                                            {{ booking.special_requests }}
                                        </dd>
                                    </div>
                                    <!-- Internal Notes -->
                                    <div v-if="booking.notes">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Internal Notes</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100 bg-yellow-50 dark:bg-yellow-900/20 p-3 rounded-md border-l-4 border-yellow-400">
                                            {{ booking.notes }}
                                        </dd>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Financial & Meta -->
                    <div class="space-y-6">
                        <!-- Financial Details -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Financial Details</h3>
                                <div class="space-y-4">
                                    <!-- Total Price -->
                                    <div class="flex justify-between items-center pb-3 border-b border-gray-200 dark:border-gray-600">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Price</dt>
                                        <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ formatCurrency(booking.total_price?? 0) }}</dd>
                                    </div>
                                    <!-- Commission Details -->
                                    <div v-if="booking.commission_rate || booking.commission_amount" class="space-y-2">
                                        <div v-if="booking.commission_rate" class="flex justify-between items-center">
                                            <dt class="text-sm text-gray-500 dark:text-gray-400">Commission Rate</dt>
                                            <dd class="text-sm text-gray-900 dark:text-gray-100">{{ booking.commission_rate }}%</dd>
                                        </div>
                                        <div v-if="booking.commission_amount" class="flex justify-between items-center">
                                            <dt class="text-sm text-gray-500 dark:text-gray-400">Commission Amount</dt>
                                            <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ formatCurrency(booking.commission_amount) }}</dd>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <dt class="text-sm text-gray-500 dark:text-gray-400">Commission Status</dt>
                                            <dd>
                                                <span :class="[
                                                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                                    booking.commission_paid 
                                                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' 
                                                        : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                                ]">
                                                    {{ booking.commission_paid ? 'Paid' : 'Unpaid' }}
                                                </span>
                                            </dd>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Booking Metadata -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Booking Information</h3>
                                <div class="space-y-3">
                                    <!-- Created -->
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ formatDate(new Date(booking.created_at)) }}</dd>
                                    </div>
                                    <!-- Last Updated -->
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ formatDate(new Date(booking.updated_at)) }}</dd>
                                    </div>
                                    <!-- Source -->
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Source</dt>
                                        <dd class="mt-1">
                                            <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getSourceClass(booking.source)]">
                                                {{ sourceLabels[booking.source] }}
                                            </span>
                                        </dd>
                                    </div>
                                    <!-- Booking Type -->
                                    <button
                                        v-if="booking.email"
                                        class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors"
                                        @click="sendEmail(booking.email)"
                                    >
                                        📧 Send Email
                                    </button>
                                    <button
                                        v-if="booking.phone"
                                        class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors"
                                        @click="callGuest(booking.phone)"
                                    >
                                        📞 Call Guest
                                    </button>
                                    <button
                                        class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors"
                                        @click="printDetails()"
                                    >
                                        🖨️ Print Details
                                    </button>
                                    <Link
                                        :href="route('properties.show', booking.property.id)"
                                        class="block w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors"
                                    >
                                        🏠 View Property
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>