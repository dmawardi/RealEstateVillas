<script setup lang="ts">
import { ref, computed } from 'vue';
import { formatPrice, formatDate } from '@/utils';
import { Booking, Property } from '@/types';
import { useForm } from '@inertiajs/vue3';

interface Props {
    property: Property
}

const { property } = defineProps<Props>();

// View state
const currentView = ref<'list' | 'calendar'>('list');
const currentDate = ref(new Date());
const selectedBooking = ref<Booking | null>(null);
const showBookingModal = ref(false);

// Booking form states
const showBookingForm = ref(false);
const bookingForm = useForm({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    check_in_date: '',
    check_out_date: '',
    number_of_guests: 1,
    total_price: 0,
    source: 'direct',
    booking_type: 'booking',
    special_requests: '',
    external_booking_id: ''
});

// Calendar navigation
const currentMonth = computed(() => {
    return new Date(currentDate.value.getFullYear(), currentDate.value.getMonth(), 1);
});

const nextMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 1);
};

const previousMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1, 1);
};

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

const getSourceColor = (source: string) => {
    const colors = {
        'direct': 'bg-blue-500',
        'airbnb': 'bg-red-500',
        'booking_com': 'bg-blue-600',
        'agoda': 'bg-purple-500',
        'owner_blocked': 'bg-gray-500',
        'maintenance': 'bg-orange-500',
        'other': 'bg-gray-400'
    };
    return colors[source as keyof typeof colors] || 'bg-gray-400';
};

// Calendar generation
// Generates the calendar grid for the current month, including bookings for each day
const generateCalendar = () => {
    const year = currentMonth.value.getFullYear();
    const month = currentMonth.value.getMonth();
    const firstDay = new Date(year, month, 1);
    // Calculate the first date to display (start from the previous Sunday)
    const startDate = new Date(firstDay);
    startDate.setDate(startDate.getDate() - firstDay.getDay());
    
    const calendar = [];
    const currentDate = new Date(startDate);
    
    // Build up to 6 weeks (rows) for the calendar view
    for (let week = 0; week < 6; week++) {
        const weekDays = [];
        // Each week has 7 days (columns)
        for (let day = 0; day < 7; day++) {
            const date = new Date(currentDate);
            const isCurrentMonth = date.getMonth() === month;
            const dateStr = date.toISOString().split('T')[0];
            
            // Find bookings that overlap with this day
            const dayBookings = bookings.value.filter(booking => {
                const checkIn = new Date(booking.check_in_date);
                const checkOut = new Date(booking.check_out_date);
                // Include booking if this date is between check-in and check-out (inclusive)
                return date >= checkIn && date <= checkOut;
            });
            
            weekDays.push({
                date: new Date(date),
                dateStr,
                day: date.getDate(),
                isCurrentMonth,
                bookings: dayBookings,
                isToday: dateStr === new Date().toISOString().split('T')[0]
            });
            
            // Move to the next day
            currentDate.setDate(currentDate.getDate() + 1);
        }
        calendar.push(weekDays);
        
        // Stop early if we've passed the end of the current month and at least 5 weeks are shown
        if (currentDate.getMonth() !== month && week >= 4) break;
    }
    
    return calendar;
};

const bookings = computed(() => property.bookings || []);

// Computed property for the calendar grid, updates when currentMonth or bookings change
const calendarDays = computed(() => generateCalendar());

// Month names for display in the calendar header
const monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];

// Day names for display in the calendar grid header
const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

// Booking statistics for the summary section
const bookingStats = computed(() => {
    const total = bookings.value.length;
    const confirmed = bookings.value.filter(b => b.status === 'confirmed').length;
    const pending = bookings.value.filter(b => b.status === 'pending').length;
    // Only count revenue from confirmed or completed bookings
    const revenue = bookings.value
        .filter(b => b.status === 'confirmed' || b.status === 'completed')
        .reduce((sum, b) => sum + (b.total_price || 0), 0);
    
    return { total, confirmed, pending, revenue };
});

const openBookingDetails = (booking: Booking) => {
    selectedBooking.value = booking;
    showBookingModal.value = true;
};

const closeBookingModal = () => {
    showBookingModal.value = false;
    selectedBooking.value = null;
};

// Booking form functions
const openBookingForm = () => {
    // Reset form
    bookingForm.reset();
    // Set default dates (today + 1 for check-in, today + 2 for check-out)
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const dayAfter = new Date();
    dayAfter.setDate(dayAfter.getDate() + 2);
    
    bookingForm.check_in_date = tomorrow.toISOString().split('T')[0];
    bookingForm.check_out_date = dayAfter.toISOString().split('T')[0];
    bookingForm.number_of_guests = 1;
    bookingForm.source = 'direct';
    bookingForm.booking_type = 'booking';
    
    showBookingForm.value = true;
};

const closeBookingForm = () => {
    showBookingForm.value = false;
    bookingForm.reset();
};

// Form validation
const isFormValid = computed(() => {
    const checkInDate = new Date(bookingForm.check_in_date);
    const checkOutDate = new Date(bookingForm.check_out_date);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    return (
        bookingForm.first_name.trim() &&
        bookingForm.email.trim() &&
        bookingForm.check_in_date &&
        bookingForm.check_out_date &&
        checkInDate >= today &&
        checkOutDate > checkInDate &&
        bookingForm.number_of_guests > 0 &&
        bookingForm.number_of_guests <= (property.bedrooms ? property.bedrooms * 2 : 20) &&
        bookingForm.total_price >= 0
    );
});

const submitBooking = () => {
    // Additional client-side validation
    if (!isFormValid.value) {
        return;
    }
    
    // Validate dates
    const checkInDate = new Date(bookingForm.check_in_date);
    const checkOutDate = new Date(bookingForm.check_out_date);
    
    if (checkInDate >= checkOutDate) {
        alert('Check-out date must be after check-in date');
        return;
    }
    
    if (checkInDate < new Date()) {
        alert('Check-in date cannot be in the past');
        return;
    }
    
    bookingForm.post(route('properties.bookings.store', property.id), {
        onSuccess: () => {
            closeBookingForm();
            // Show success message - the backend will redirect with flash message
        },
        onError: (errors) => {
            console.error('Booking creation errors:', errors);
            // Form errors will be automatically displayed via bookingForm.errors
        }
    });
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    Property Bookings
                    <span class="text-sm font-normal text-gray-500 ml-2">({{ bookings.length }})</span>
                </h2>
                <div class="flex space-x-3">
                    <!-- Add Booking Button -->
                    <button
                        @click="openBookingForm"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center space-x-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        <span>Add Booking</span>
                    </button>
                    
                    <!-- View Toggle -->
                    <div class="flex bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
                        <button
                            @click="currentView = 'list'"
                            :class="[
                                'px-3 py-1 text-sm rounded-md transition-colors',
                                currentView === 'list' 
                                    ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-gray-100 shadow' 
                                    : 'text-gray-600 dark:text-gray-400'
                            ]"
                        >
                            List
                        </button>
                        <button
                            @click="currentView = 'calendar'"
                            :class="[
                                'px-3 py-1 text-sm rounded-md transition-colors',
                                currentView === 'calendar' 
                                    ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-gray-100 shadow' 
                                    : 'text-gray-600 dark:text-gray-400'
                            ]"
                        >
                            Calendar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Stats -->
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center">
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ bookingStats.total }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total Bookings</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-green-600">{{ bookingStats.confirmed }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Confirmed</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-yellow-600">{{ bookingStats.pending }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Pending</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-blue-600">{{ formatPrice(bookingStats.revenue) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Revenue</p>
                </div>
            </div>
        </div>

        <div class="p-6">
            <!-- Calendar View -->
            <div v-if="currentView === 'calendar'" class="space-y-4">
                <!-- Calendar Header -->
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ monthNames[currentMonth.getMonth()] }} {{ currentMonth.getFullYear() }}
                    </h3>
                    <div class="flex space-x-2">
                        <button
                            @click="previousMonth"
                            class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <button
                            @click="nextMonth"
                            class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Calendar Grid -->
                <div class="grid grid-cols-7 gap-1">
                    <!-- Day Headers -->
                    <div
                        v-for="dayName in dayNames"
                        :key="dayName"
                        class="p-2 text-center text-sm font-medium text-gray-500 dark:text-gray-400"
                    >
                        {{ dayName }}
                    </div>

                    <!-- Calendar Days -->
                    <template v-for="week in calendarDays" :key="week[0].dateStr">
                        <div
                            v-for="day in week"
                            :key="day.dateStr"
                            :class="[
                                'min-h-24 p-1 border border-gray-200 dark:border-gray-600 relative',
                                day.isCurrentMonth ? 'bg-white dark:bg-gray-800' : 'bg-gray-50 dark:bg-gray-700',
                                day.isToday ? 'ring-2 ring-blue-500' : ''
                            ]"
                        >
                            <!-- Day Number -->
                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ day.day }}
                            </div>

                            <!-- Bookings for this day -->
                            <div class="space-y-1">
                                <div
                                    v-for="booking in day.bookings.slice(0, 2)"
                                    :key="booking.id"
                                    @click="openBookingDetails(booking)"
                                    :class="[
                                        'text-xs px-1 py-0.5 rounded cursor-pointer truncate',
                                        getBookingStatusClass(booking.status)
                                    ]"
                                    :title="`${booking.first_name} ${booking.last_name || ''} - ${booking.status}`"
                                >
                                    <span :class="getSourceColor(booking.source)" class="inline-block w-2 h-2 rounded-full mr-1"></span>
                                    {{ booking.first_name }}
                                </div>
                                <div
                                    v-if="day.bookings.length > 2"
                                    class="text-xs text-gray-500 dark:text-gray-400"
                                >
                                    +{{ day.bookings.length - 2 }} more
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Calendar Legend -->
                <div class="flex flex-wrap gap-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Confirmed</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Pending</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Direct</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Airbnb</span>
                    </div>
                </div>
            </div>

            <!-- List View -->
            <div v-else-if="bookings.length > 0" class="space-y-4">
                <div
                    v-for="booking in bookings.slice(0, 10)"
                    :key="booking.id"
                    @click="openBookingDetails(booking)"
                    class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
                >
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-2">
                                <span class="text-lg">{{ getSourceIcon(booking.source) }}</span>
                                <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ booking.first_name }} {{ booking.last_name || '' }}
                                </h4>
                                <span :class="getBookingStatusClass(booking.status)" class="px-2 py-1 rounded-full text-xs font-medium border">
                                    {{ booking.status }}
                                </span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-2 text-sm text-gray-600 dark:text-gray-400">
                                <div>
                                    <span class="font-medium">Check-in:</span> {{ formatDate(new Date(booking.check_in_date)) }}
                                </div>
                                <div>
                                    <span class="font-medium">Check-out:</span> {{ formatDate(new Date(booking.check_out_date)) }}
                                </div>
                                <div>
                                    <span class="font-medium">Guests:</span> {{ booking.number_of_guests }}
                                </div>
                            </div>
                            <div v-if="booking.total_price" class="mt-2">
                                <span class="text-sm font-medium text-green-600">{{ formatPrice(booking.total_price) }}</span>
                                <span class="text-xs text-gray-500 ml-2">{{ booking.source }}</span>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span :class="getSourceColor(booking.source)" class="w-3 h-3 rounded-full"></span>
                        </div>
                    </div>
                </div>

                <div v-if="bookings.length > 10" class="text-center pt-4 border-t border-gray-200 dark:border-gray-600">
                    <button class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                        View All Bookings ({{ bookings.length }})
                    </button>
                </div>
            </div>

            <!-- No Bookings State -->
            <div v-else class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No bookings yet</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    This property doesn't have any bookings yet.
                </p>
            </div>
        </div>

        <!-- Booking Details Modal -->
        <Teleport to="body">
            <div
                v-if="showBookingModal && selectedBooking"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
                @click="closeBookingModal"
            >
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg max-w-2xl w-full max-h-full overflow-y-auto"
                    @click.stop
                >
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            Booking Details
                        </h3>
                        <button @click="closeBookingModal" class="text-gray-400 hover:text-gray-600">
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
                                        {{ selectedBooking.first_name }} {{ selectedBooking.last_name || '' }}
                                    </p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</span>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ selectedBooking.email }}</p>
                                </div>
                                <div v-if="selectedBooking.phone">
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone</span>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ selectedBooking.phone }}</p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Guests</span>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ selectedBooking.number_of_guests }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Booking Details -->
                        <div>
                            <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-3">Booking Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Check-in</span>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ formatDate(new Date(selectedBooking.check_in_date)) }}</p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Check-out</span>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ formatDate(new Date(selectedBooking.check_out_date)) }}</p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</span>
                                    <div class="mt-1">
                                        <span :class="getBookingStatusClass(selectedBooking.status)" class="px-2 py-1 rounded-full text-xs font-medium border">
                                            {{ selectedBooking.status }}
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Source</span>
                                    <div class="mt-1 flex items-center space-x-2">
                                        <span>{{ getSourceIcon(selectedBooking.source) }}</span>
                                        <span class="text-sm text-gray-900 dark:text-gray-100">{{ selectedBooking.source }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Financial Information -->
                        <div v-if="selectedBooking.total_price">
                            <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-3">Financial Information</h4>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    Total: {{ formatPrice(selectedBooking.total_price) }}
                                </div>
                            </div>
                        </div>

                        <!-- Special Requests -->
                        <div v-if="selectedBooking.special_requests">
                            <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-3">Special Requests</h4>
                            <p class="text-sm text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                {{ selectedBooking.special_requests }}
                            </p>
                        </div>

                        <!-- Notes -->
                        <div v-if="selectedBooking.notes">
                            <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-3">Internal Notes</h4>
                            <p class="text-sm text-gray-900 dark:text-gray-100 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4">
                                {{ selectedBooking.notes }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Booking Form Modal -->
        <Teleport to="body">
            <div
                v-if="showBookingForm"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
                @click="closeBookingForm"
            >
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg max-w-4xl w-full max-h-full overflow-y-auto"
                    @click.stop
                >
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            Create New Booking
                        </h3>
                        <button @click="closeBookingForm" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <form @submit.prevent="submitBooking" class="p-6 space-y-6">
                        <!-- Guest Information -->
                        <div>
                            <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">Guest Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        First Name <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="bookingForm.first_name"
                                        type="text"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        :class="{ 'border-red-500': bookingForm.errors.first_name }"
                                    />
                                    <div v-if="bookingForm.errors.first_name" class="mt-1 text-sm text-red-600">
                                        {{ bookingForm.errors.first_name }}
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Last Name
                                    </label>
                                    <input
                                        v-model="bookingForm.last_name"
                                        type="text"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        :class="{ 'border-red-500': bookingForm.errors.last_name }"
                                    />
                                    <div v-if="bookingForm.errors.last_name" class="mt-1 text-sm text-red-600">
                                        {{ bookingForm.errors.last_name }}
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Email <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="bookingForm.email"
                                        type="email"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        :class="{ 'border-red-500': bookingForm.errors.email }"
                                    />
                                    <div v-if="bookingForm.errors.email" class="mt-1 text-sm text-red-600">
                                        {{ bookingForm.errors.email }}
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Phone
                                    </label>
                                    <input
                                        v-model="bookingForm.phone"
                                        type="tel"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        :class="{ 'border-red-500': bookingForm.errors.phone }"
                                    />
                                    <div v-if="bookingForm.errors.phone" class="mt-1 text-sm text-red-600">
                                        {{ bookingForm.errors.phone }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Booking Details -->
                        <div>
                            <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">Booking Details</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Check-in Date <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="bookingForm.check_in_date"
                                        type="date"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        :class="{ 'border-red-500': bookingForm.errors.check_in_date }"
                                    />
                                    <div v-if="bookingForm.errors.check_in_date" class="mt-1 text-sm text-red-600">
                                        {{ bookingForm.errors.check_in_date }}
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Check-out Date <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="bookingForm.check_out_date"
                                        type="date"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        :class="{ 'border-red-500': bookingForm.errors.check_out_date }"
                                    />
                                    <div v-if="bookingForm.errors.check_out_date" class="mt-1 text-sm text-red-600">
                                        {{ bookingForm.errors.check_out_date }}
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Number of Guests <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model.number="bookingForm.number_of_guests"
                                        type="number"
                                        min="1"
                                        :max="property.bedrooms ? property.bedrooms * 2 : 20"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        :class="{ 'border-red-500': bookingForm.errors.number_of_guests }"
                                    />
                                    <div v-if="bookingForm.errors.number_of_guests" class="mt-1 text-sm text-red-600">
                                        {{ bookingForm.errors.number_of_guests }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Booking Source and Type -->
                        <div>
                            <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">Source & Type</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Source <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="bookingForm.source"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        :class="{ 'border-red-500': bookingForm.errors.source }"
                                    >
                                        <option value="direct">Direct</option>
                                        <option value="airbnb">Airbnb</option>
                                        <option value="booking_com">Booking.com</option>
                                        <option value="agoda">Agoda</option>
                                        <option value="owner_blocked">Owner Blocked</option>
                                        <option value="maintenance">Maintenance</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <div v-if="bookingForm.errors.source" class="mt-1 text-sm text-red-600">
                                        {{ bookingForm.errors.source }}
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Booking Type <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="bookingForm.booking_type"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        :class="{ 'border-red-500': bookingForm.errors.booking_type }"
                                    >
                                        <option value="booking">Booking</option>
                                        <option value="inquiry">Inquiry</option>
                                        <option value="blocked">Blocked</option>
                                        <option value="maintenance">Maintenance</option>
                                    </select>
                                    <div v-if="bookingForm.errors.booking_type" class="mt-1 text-sm text-red-600">
                                        {{ bookingForm.errors.booking_type }}
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Total Price <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model.number="bookingForm.total_price"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        :class="{ 'border-red-500': bookingForm.errors.total_price }"
                                    />
                                    <div v-if="bookingForm.errors.total_price" class="mt-1 text-sm text-red-600">
                                        {{ bookingForm.errors.total_price }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- External ID (for platform bookings) -->
                        <div v-if="bookingForm.source !== 'direct'">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                External Booking ID
                            </label>
                            <input
                                v-model="bookingForm.external_booking_id"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                :class="{ 'border-red-500': bookingForm.errors.external_booking_id }"
                                placeholder="Platform booking reference number"
                            />
                            <div v-if="bookingForm.errors.external_booking_id" class="mt-1 text-sm text-red-600">
                                {{ bookingForm.errors.external_booking_id }}
                            </div>
                        </div>

                        <!-- Special Requests -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Special Requests
                            </label>
                            <textarea
                                v-model="bookingForm.special_requests"
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                :class="{ 'border-red-500': bookingForm.errors.special_requests }"
                                placeholder="Any special requests or notes from the guest..."
                            ></textarea>
                            <div v-if="bookingForm.errors.special_requests" class="mt-1 text-sm text-red-600">
                                {{ bookingForm.errors.special_requests }}
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                            <button
                                type="button"
                                @click="closeBookingForm"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="bookingForm.processing || !isFormValid"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span v-if="bookingForm.processing">Creating...</span>
                                <span v-else>Create Booking</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </div>
</template>