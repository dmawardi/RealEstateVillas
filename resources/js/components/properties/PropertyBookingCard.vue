<script setup lang="ts">
import { ref, computed } from 'vue';
import type { Availability, Property, PropertyPricing } from '@/types';
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import { formatPrice, formatDate } from '@/utils/formatters'; // Importing formatPrice utility

interface Props {
    property: Property;
    availability: Availability;
    current_pricing: PropertyPricing;
}

const { property, availability, current_pricing } = defineProps<Props>();

// Reactive state - VueDatePicker can return null when cleared
const dateRange = ref<[Date, Date] | null>(null);

// Get today's date as Date object
const today = new Date();

// Computed properties
const areDatesValid = computed(() => {
    return dateRange.value && dateRange.value[0] && dateRange.value[1];
});

const getNights = computed(() => {
    if (!areDatesValid.value) return 0;
    const checkIn = dateRange.value![0];
    const checkOut = dateRange.value![1];
    return Math.ceil((checkOut.getTime() - checkIn.getTime()) / (1000 * 60 * 60 * 24));
});

const getTotalPrice = computed(() => {
    if (!areDatesValid.value) {
        return 0;
    }
    
    // Check if current_pricing exists and has nightly_rate
    if (!current_pricing || !current_pricing.nightly_rate) {
        return 0;
    }
    
    const nights = getNights.value;
    const pricePerNight = current_pricing.nightly_rate;
    const totalPrice = Math.round(pricePerNight * nights);
    
    return totalPrice;
});
// Get disabled dates from unavailable periods
const disabledDates = computed(() => {
    const disabled: Date[] = [];
    
    availability.unavailable_periods.forEach(period => {
        const start = new Date(period.start);
        const end = new Date(period.end);
        
        for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
            disabled.push(new Date(d));
        }
    });
    
    return disabled;
});

// Clear button handler
const clearDates = () => {
    dateRange.value = null;
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">
            Book This Property
        </h3>

        <!-- Date Picker -->
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Select Dates
                </label>
                <VueDatePicker
                    v-model="dateRange"
                    :min-date="today"
                    :disabled-dates="disabledDates"
                    :enable-time-picker="false"
                    :inline="false"
                    :multi-calendars="false"
                    :auto-apply="false"
                    range
                    placeholder="Select check-in and check-out dates"
                    format="MMM dd, yyyy"
                    menu-class-name="dp-custom-menu"
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
                    {{ formatDate(dateRange![0]) }} - {{ formatDate(dateRange![1]) }}
                </div>
            </div>

            <!-- Availability Legend -->
            <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-400">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-green-100 rounded-full mr-2"></div>
                        <span>Available</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-red-100 rounded-full mr-2"></div>
                        <span>Unavailable</span>
                    </div>
                </div>
                <button 
                    v-if="areDatesValid"
                    @click="clearDates"
                    class="text-blue-600 dark:text-blue-400 hover:underline"
                >
                    Clear dates
                </button>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-2">
                <button 
                    :disabled="!areDatesValid"
                    class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed font-medium"
                >
                    {{ areDatesValid ? 'Continue to Book' : 'Select Dates' }}
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