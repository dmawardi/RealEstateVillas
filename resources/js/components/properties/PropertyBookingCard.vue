<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import type { Availability, Property } from '@/types';

interface Props {
    property: Property;
    availability: Availability;
}

const { property, availability } = defineProps<Props>();

// Reactive state
const checkInDate = ref('');
const checkOutDate = ref('');
const isLoadingAvailability = ref(false);

// Create reactive copies of availability data that we can update
const availabilityData = ref({
    period_start: availability.period_start,
    period_end: availability.period_end,
    unavailable_periods: [...availability.unavailable_periods]
});

//Get today's date for min attribute
const today = new Date().toISOString().split('T')[0];

// Computed properties
const areDatesValid = computed(() => {
    return checkInDate.value && checkOutDate.value && checkInDate.value < checkOutDate.value;
});
const getNights = computed(() => {
    if (!areDatesValid.value) return 0;
    const checkIn = new Date(checkInDate.value);
    const checkOut = new Date(checkOutDate.value);
    return Math.ceil((checkOut.getTime() - checkIn.getTime()) / (1000 * 60 * 60 * 24));
});
const getTotalPrice = computed(() => {
    if (!areDatesValid.value || !property.rental_price_weekly) return 0;
    const nights = getNights.value;
    const pricePerNight = property.rental_price_weekly / 7; // Convert weekly to nightly
    return Math.round(pricePerNight * nights);
});

const getMinCheckOutDate = () => {
    if (!checkInDate.value) return today;
    const checkIn = new Date(checkInDate.value);
    checkIn.setDate(checkIn.getDate() + 1);
    return checkIn.toISOString().split('T')[0];
};

// Check if a specific date is available (not in any unavailable period)
const isDateAvailable = (date: string): boolean => {
    const checkDate = new Date(date);
    
    return !availabilityData.value.unavailable_periods.some(period => {
        const periodStart = new Date(period.start);
        const periodEnd = new Date(period.end);
        return checkDate >= periodStart && checkDate <= periodEnd;
    });
};

// Check if the selected date range is available
// Called when clicking "Check Availability" or when dates change
const isDateRangeAvailable = computed(() => {
    if (!areDatesValid.value) return null;
    
    const start = new Date(checkInDate.value);
    const end = new Date(checkOutDate.value);
    const currentDate = new Date(start);
    
    // Check each date in the range
    while (currentDate < end) {
        const dateString = currentDate.toISOString().split('T')[0];
        if (!isDateAvailable(dateString)) {
            return false;
        }
        currentDate.setDate(currentDate.getDate() + 1);
    }
    return true;
});

// Check if we need to fetch more availability data for the selected range
// Called when dates change and the selected range exceeds current availability data
const needsMoreAvailabilityData = computed(() => {
    if (!areDatesValid.value) return false;
    
    const start = new Date(checkInDate.value);
    const end = new Date(checkOutDate.value);
    const availabilityStart = new Date(availability.period_start);
    const availabilityEnd = new Date(availability.period_end);
    
    // Need more data if the selected range extends beyond our current availability data
    return start < availabilityStart || end > availabilityEnd;
});

// Methods
const fetchMoreAvailability = async () => {
    if (!areDatesValid.value) return;
    
    isLoadingAvailability.value = true;
    
    try {
        const start = new Date(checkInDate.value);
        const end = new Date(checkOutDate.value);
        
        // Extend the range a bit to avoid frequent requests
        start.setDate(start.getDate() - 7);
        end.setDate(end.getDate() + 7);
        
        const response = await fetch(`/properties/${property.id}/availability?start=${start.toISOString().split('T')[0]}&end=${end.toISOString().split('T')[0]}`);
        const data = await response.json();
        
        // Update our reactive availability data (NOT the prop)
        availabilityData.value = {
            period_start: data.period_start,
            period_end: data.period_end,
            unavailable_periods: data.unavailable_periods
        };
        
    } catch (error) {
        console.error('Error fetching availability:', error);
    } finally {
        isLoadingAvailability.value = false;
    }
};

// Date change handler
const onDateChange = () => {
    // Clear checkout if it's before or same as checkin
    if (checkOutDate.value && checkInDate.value >= checkOutDate.value) {
        checkOutDate.value = '';
    }
    
    // Check if we need to fetch more availability data
    if (needsMoreAvailabilityData.value) {
        fetchMoreAvailability();
    }
};

// Clear button handler
const clearDates = () => {
    checkInDate.value = '';
    checkOutDate.value = '';
};

const formatPrice = (price: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(price);
};

// Initialize component
onMounted(() => {
    console.log('PropertyBookingCard mounted');
    console.log('Property ID:', property.id);
    console.log('Initial availability:', availability); 
});
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">
            Book This Property
        </h3>

        <!-- Date Selection -->
        <div class="space-y-4">
            <!-- Check-in Date -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Check-in Date
                </label>
                <input
                    v-model="checkInDate"
                    type="date"
                    :min="today"
                    @change="onDateChange"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
            </div>

            <!-- Check-out Date -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Check-out Date
                </label>
                <input
                    v-model="checkOutDate"
                    type="date"
                    :min="getMinCheckOutDate()"
                    :disabled="!checkInDate"
                    @change="onDateChange"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                />
            </div>

            <!-- Date Summary -->
            <div v-if="areDatesValid" class="bg-blue-50 dark:bg-blue-900/30 rounded-lg p-3">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-600 dark:text-gray-400">
                        {{ getNights }} night{{ getNights !== 1 ? 's' : '' }}
                    </span>
                    <span class="font-medium text-gray-900 dark:text-gray-100">
                        {{ formatPrice(getTotalPrice) }}
                    </span>
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ new Date(checkInDate).toLocaleDateString() }} - {{ new Date(checkOutDate).toLocaleDateString() }}
                </div>
            </div>

            <!-- Availability Status -->
            <div v-if="areDatesValid" class="text-sm">
                <div v-if="isLoadingAvailability" class="flex items-center text-gray-500 dark:text-gray-400">
                    <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Checking availability...
                </div>
                <div v-else-if="isDateRangeAvailable === true" 
                     class="flex items-center text-green-600 dark:text-green-400">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    Available for selected dates
                </div>
                <div v-else-if="isDateRangeAvailable === false" 
                     class="flex items-center text-red-600 dark:text-red-400">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                    Not available for selected dates
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-2">
                <button 
                    :disabled="!areDatesValid || isLoadingAvailability || isDateRangeAvailable !== true"
                    class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed font-medium"
                >
                    {{ isDateRangeAvailable === true ? 'Continue to Book' : 'Check Availability' }}
                </button>
                
                <button 
                    v-if="checkInDate || checkOutDate"
                    @click="clearDates"
                    class="w-full text-gray-600 dark:text-gray-400 py-2 px-4 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-sm"
                >
                    Clear Dates
                </button>
            </div>

            <!-- Pricing Info -->
            <div v-if="property.rental_price_weekly" class="border-t border-gray-200 dark:border-gray-700 pt-4">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    Starting from
                </div>
                <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ formatPrice(property.rental_price_weekly) }}/week
                </div>
                <div v-if="property.rental_price_monthly" class="text-sm text-gray-500 dark:text-gray-400">
                    {{ formatPrice(property.rental_price_monthly) }}/month
                </div>
            </div>
        </div>
    </div>
</template>