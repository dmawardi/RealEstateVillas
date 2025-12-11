<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import type { Property, PropertyPricing } from '@/types';
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import { formatPrice, formatDate } from '@/utils/formatters';
import BookingModal from '@/components/properties/BookingModal.vue';
import { api } from '@/services/api';
import { usePage } from '@inertiajs/vue3';

// ================================================================
// INTERFACES & TYPES
// ================================================================

interface Props {
    property: Property;
    current_pricing: PropertyPricing;
    businessPhone: string;
}

interface UnavailablePeriod {
    start: string;
    end: string;
}

interface AvailabilityResponse {
    period_start: string;
    period_end: string;
    unavailable_periods: UnavailablePeriod[];
}

interface PriceCalculation {
    total_price: number;
    original_price: number;
    savings: number;
    discount_percentage: number;
    nights: number;
    rate_used: 'nightly' | 'weekly' | 'monthly';
    rate_per_night: number;
    original_rate_per_night: number;
    currency: string;
    check_in_date: string;
    check_out_date: string;
}

// ================================================================
// COMPONENT SETUP
// ================================================================

const props = defineProps<Props>();
const isAuthenticated = usePage().props.auth?.user !== null;    

// ================================================================
// REACTIVE STATE
// ================================================================

// Date selection
const dateRange = ref<[Date, Date] | null>(null);
const today = new Date();

// Availability data
const unavailablePeriods = ref<UnavailablePeriod[]>([]);
const isLoadingAvailability = ref(false);
const availabilityLoaded = ref(false);
const availabilityError = ref<string | null>(null);
const pricingIssues = ref(false);

// Price calculation
const priceCalculation = ref<PriceCalculation | null>(null);
const isLoadingPrice = ref(false);
const priceError = ref<string | null>(null);

// Modal state
const isBookingModalOpen = ref(false);

// Debounce timer
let priceTimer: number | null = null;

// ================================================================
// COMPUTED PROPERTIES
// ================================================================

const areDatesValid = computed(() => {
    return dateRange.value && dateRange.value[0] && dateRange.value[1];
});

const disabledDates = computed(() => {
    const disabled: Date[] = [];
    
    unavailablePeriods.value.forEach(period => {
        const start = new Date(period.start);
        const end = new Date(period.end);
        
        for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
            disabled.push(new Date(d));
        }
    });
    
    return disabled;
});

const hasDiscount = computed(() => {
    return priceCalculation.value && priceCalculation.value.savings > 0;
});

const canProceedToBooking = computed(() => {
    return areDatesValid.value && 
           priceCalculation.value && 
           !isLoadingPrice.value && 
           !isLoadingAvailability.value;
});

// ================================================================
// API METHODS
// ================================================================

const loadAvailability = async () => {
    if (availabilityLoaded.value || isLoadingAvailability.value) return;

    isLoadingAvailability.value = true;
    availabilityError.value = null;

    try {
        const startDate = new Date();
        const endDate = new Date();
        endDate.setMonth(endDate.getMonth() + 6);

        await api.properties.getAvailability(
            props.property.id,
            {
                start: startDate.toISOString().split('T')[0],
                end: endDate.toISOString().split('T')[0]
            },
            {
                onSuccess: (response: AvailabilityResponse) => {
                    unavailablePeriods.value = response.unavailable_periods;
                    availabilityLoaded.value = true;
                },
                onError: (errors: any) => {
                    console.error('Failed to load availability:', errors);
                    availabilityError.value = 'Failed to load availability';
                    unavailablePeriods.value = [];
                    pricingIssues.value = true;
                }
            }
        );
    } catch (error) {
        console.error('Error loading availability:', error);
        availabilityError.value = 'Failed to load availability';
        unavailablePeriods.value = [];
    } finally {
        isLoadingAvailability.value = false;
    }
};

const calculatePrice = async () => {
    if (!areDatesValid.value) {
        priceCalculation.value = null;
        return;
    }

    isLoadingPrice.value = true;
    priceError.value = null;

    try {
        await api.properties.calculatePrice(
            props.property.id,
            {
                check_in_date: dateRange.value![0].toISOString().split('T')[0],
                check_out_date: dateRange.value![1].toISOString().split('T')[0]
            },
            {
                onSuccess: (response: PriceCalculation) => {
                    priceCalculation.value = response;
                },
                onError: (errors: any) => {
                    console.error('Failed to calculate price:', errors);
                    priceError.value = 'Failed to calculate price';
                    priceCalculation.value = null;
                }
            }
        );
    } catch (error) {
        console.error('Error calculating price:', error);
        priceError.value = 'Failed to calculate price';
        priceCalculation.value = null;
    } finally {
        isLoadingPrice.value = false;
    }
};

// ================================================================
// PURE JS DEBOUNCE
// ================================================================

const debouncedCalculatePrice = () => {
    // Clear existing timer
    if (priceTimer) {
        clearTimeout(priceTimer);
    }
    
    // Set new timer
    priceTimer = setTimeout(() => {
        calculatePrice();
    }, 500);
};

// ================================================================
// EVENT HANDLERS
// ================================================================

const handleDatePickerOpen = () => {
    loadAvailability();
};

const clearDates = () => {
    dateRange.value = null;
    priceCalculation.value = null;
    
    // Clear any pending price calculation
    if (priceTimer) {
        clearTimeout(priceTimer);
        priceTimer = null;
    }
};

const openBookingModal = () => {
    if (canProceedToBooking.value) {
        isBookingModalOpen.value = true;
    }
};

// ================================================================
// WATCHERS
// ================================================================

watch(dateRange, () => {
    if (areDatesValid.value) {
        debouncedCalculatePrice();
    } else {
        priceCalculation.value = null;
    }
}, { deep: true });
</script>

<template>
    <div>
        <!-- If authenticated -->
        <div v-if="isAuthenticated && current_pricing" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">
                Book This Property
            </h3>
    
            <div class="space-y-4">
                <!-- Date Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Select Dates
                    </label>
                    
                    <!-- Status Messages -->
                    <div v-if="isLoadingAvailability" class="text-sm text-blue-600 dark:text-blue-400 mb-2">
                        Loading availability...
                    </div>
                    
                    <div v-if="availabilityError" class="text-sm text-red-600 dark:text-red-400 mb-2">
                        {{ availabilityError }}
                    </div>
    
                    <!-- Date Picker -->
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
                        @focus="handleDatePickerOpen"
                        @open="handleDatePickerOpen"
                    />
                </div>
    
                <!-- Price Summary -->
                <div v-if="areDatesValid" class="bg-blue-50 dark:bg-blue-900/30 rounded-lg p-4">
                    <!-- Loading Price -->
                    <div v-if="isLoadingPrice" class="text-center text-blue-600 dark:text-blue-400">
                        Calculating price...
                    </div>
                    
                    <!-- Price Error -->
                    <div v-else-if="priceError" class="text-center text-red-600 dark:text-red-400">
                        {{ priceError }}
                    </div>
                    
                    <!-- Price Display -->
                    <div v-else-if="priceCalculation" class="space-y-3">
                        <!-- Date Range -->
                        <div class="text-center text-sm text-gray-600 dark:text-gray-400">
                            {{ formatDate(dateRange![0]) }} - {{ formatDate(dateRange![1]) }}
                        </div>
                        
                        <!-- Nights -->
                        <div class="text-center text-lg font-medium text-gray-900 dark:text-gray-100">
                            {{ priceCalculation.nights }} night{{ priceCalculation.nights !== 1 ? 's' : '' }}
                        </div>
                        
                        <!-- Pricing -->
                        <div class="space-y-2">
                            <!-- Show discount if applicable -->
                            <div v-if="hasDiscount" class="text-center">
                                <div class="text-sm text-gray-500 dark:text-gray-400 line-through">
                                    {{ formatPrice(priceCalculation.original_price) }}
                                </div>
                                <div class="text-xl font-bold text-green-600 dark:text-green-400">
                                    {{ formatPrice(priceCalculation.total_price) }}
                                </div>
                                <div class="text-sm text-green-600 dark:text-green-400">
                                    Save {{ formatPrice(priceCalculation.savings) }} ({{ priceCalculation.discount_percentage }}% off)
                                </div>
                            </div>
                            
                            <!-- No discount -->
                            <div v-else class="text-center">
                                <div class="text-xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ formatPrice(priceCalculation.total_price) }}
                                </div>
                            </div>
                            
                            <!-- Rate Information -->
                            <div class="text-center text-sm text-gray-600 dark:text-gray-400">
                                <span class="capitalize">{{ priceCalculation.rate_used }}</span> rate: 
                                {{ formatPrice(priceCalculation.rate_per_night) }}/night
                                <span v-if="hasDiscount" class="ml-2">
                                    (was {{ formatPrice(priceCalculation.original_rate_per_night) }}/night)
                                </span>
                            </div>
                        </div>
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
    
                <!-- Action Button -->
                <button 
                    @click="openBookingModal"
                    :disabled="!canProceedToBooking"
                    class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed font-medium"
                >
                    <span v-if="isLoadingPrice || isLoadingAvailability">
                        Loading...
                    </span>
                    <span v-else-if="!areDatesValid">
                        Select Dates
                    </span>
                    <span v-else>
                        Continue to Book
                    </span>
                </button>
    
                <!-- Starting Rates Info -->
                <div v-if="current_pricing" class="border-t border-gray-200 dark:border-gray-700 pt-4">
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                        Starting rates
                    </div>
                    <div class="space-y-1">
                        <div v-if="current_pricing.nightly_rate" class="flex justify-between text-sm">
                            <span>Nightly:</span>
                            <span class="font-medium">{{ formatPrice(current_pricing.nightly_rate) }}</span>
                        </div>
                        <div v-if="current_pricing.weekly_rate" class="flex justify-between text-sm">
                            <span>Weekly (7+ nights):</span>
                            <span class="font-medium">{{ formatPrice(current_pricing.weekly_rate) }}/night</span>
                        </div>
                        <div v-if="current_pricing.monthly_rate" class="flex justify-between text-sm">
                            <span>Monthly (30+ nights):</span>
                            <span class="font-medium">{{ formatPrice(current_pricing.monthly_rate) }}/night</span>
                        </div>
                    </div>
                </div>
            </div>
    
            <!-- Booking Modal -->
            <BookingModal 
                v-model="isBookingModalOpen"
                :property="property" 
                :check-in-date="dateRange?.[0] || null"
                :check-out-date="dateRange?.[1] || null"
                :total-price="priceCalculation?.total_price || 0"
                :nights="priceCalculation?.nights || 0"
            />
        </div>
        <div v-else-if="!current_pricing" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 text-center text-gray-900 dark:text-gray-100">
            <p>We're sorry, but there are issues retrieving pricing information for this property at the moment.</p>
            <p>Alternatively, you can contact us via <a :href="'https://wa.me/' + businessPhone + '?text=' + encodeURIComponent('Hi, I am interested in property #' + property.property_id)" class="text-blue-600 dark:text-blue-400 hover:underline">WhatsApp</a>.</p>
        </div>
        <!-- If not authenticated -->
        <div v-else class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 text-center text-gray-900 dark:text-gray-100">
            <p>Please <a :href="route('login')" class="text-blue-600 dark:text-blue-400 hover:underline">log in</a> to book this property.</p>
            <p>If you don't have an account, you can <a :href="route('register')" class="text-blue-600 dark:text-blue-400 hover:underline">register here</a>.</p>
            <!-- Whatsapp -->
            <p>Alternatively, you can contact us via <a :href="'https://wa.me/' + businessPhone + '?text=' + encodeURIComponent('Hi, I am interested in property #' + property.property_id)" class="text-blue-600 dark:text-blue-400 hover:underline">WhatsApp</a>.</p>
        </div>
    </div>
</template>