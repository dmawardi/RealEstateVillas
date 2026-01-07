<!-- resources/js/components/properties/PropertyBookingBottomNav.vue -->
<script setup lang="ts">
import { ref, computed, watch, nextTick } from 'vue';
import type { Property, PropertyPricing } from '@/types';
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import { formatPrice } from '@/utils/formatters';
import BookingModal from '@/components/properties/BookingModal.vue';
import { api } from '@/services/api';
import { usePage } from '@inertiajs/vue3';

interface Props {
    property: Property;
    current_pricing: PropertyPricing;
    businessPhone: string;
}

interface UnavailablePeriod {
    start: string;
    end: string;
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

const props = defineProps<Props>();
const isAuthenticated = usePage().props.auth?.user !== null;

// State
const dateRange = ref<[Date, Date] | null>(null);
const showDatePicker = ref(false);
const priceCalculation = ref<PriceCalculation | null>(null);
const isLoadingPrice = ref(false);
const isBookingModalOpen = ref(false);
const unavailablePeriods = ref<UnavailablePeriod[]>([]);
const isLoadingAvailability = ref(false);

// Computed
const areDatesValid = computed(() => {
    return dateRange.value && dateRange.value[0] && dateRange.value[1];
});

const canProceedToBooking = computed(() => {
    return areDatesValid.value && priceCalculation.value && !isLoadingPrice.value;
});

const displayPrice = computed(() => {
    if (priceCalculation.value) {
        return formatPrice(priceCalculation.value.total_price);
    }
    if (props.current_pricing?.nightly_rate) {
        return formatPrice(props.current_pricing.nightly_rate) + '/night';
    }
    return 'Select dates';
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

// Methods
let priceTimer: number | null = null;

const debouncedCalculatePrice = () => {
    if (priceTimer) clearTimeout(priceTimer);
    priceTimer = setTimeout(() => calculatePrice(), 500);
};

const calculatePrice = async () => {
    if (!areDatesValid.value) {
        priceCalculation.value = null;
        return;
    }

    isLoadingPrice.value = true;
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
                    priceCalculation.value = null;
                }
            }
        );
    } catch (error) {
        console.error('Error calculating price:', error);
        priceCalculation.value = null;
    } finally {
        isLoadingPrice.value = false;
    }
};

const loadAvailability = async () => {
    if (isLoadingAvailability.value) return;

    isLoadingAvailability.value = true;
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
                onSuccess: (response: any) => {
                    unavailablePeriods.value = response.unavailable_periods;
                },
                onError: (errors: any) => {
                    console.error('Failed to load availability:', errors);
                    unavailablePeriods.value = [];
                }
            }
        );
    } catch (error) {
        console.error('Error loading availability:', error);
        unavailablePeriods.value = [];
    } finally {
        isLoadingAvailability.value = false;
    }
};

const toggleDatePicker = (event?: Event) => {
    event?.stopPropagation();
    if (!showDatePicker.value) {
        loadAvailability();
    }
    showDatePicker.value = !showDatePicker.value;
};

const openBookingModal = () => {
    if (canProceedToBooking.value) {
        isBookingModalOpen.value = true;
    }
};

const closeBottomNav = () => {
    showDatePicker.value = false;
};

// Watchers
watch(dateRange, () => {
    if (areDatesValid.value) {
        debouncedCalculatePrice();
    } else {
        priceCalculation.value = null;
    }
}, { deep: true });

// Close date picker when clicking outside
const handleOutsideClick = (event: Event) => {
    const target = event.target as HTMLElement;
    if (!target.closest('.date-picker-container') && showDatePicker.value) {
        showDatePicker.value = false;
    }
};

// Add event listener for outside clicks
watch(showDatePicker, (isOpen) => {
    if (isOpen) {
        // Delay adding the listener to prevent immediate closure
        nextTick(() => {
            document.addEventListener('click', handleOutsideClick);
        });
    } else {
        document.removeEventListener('click', handleOutsideClick);
    }
});
</script>

<template>
    <!-- Backdrop overlay when date picker is open -->
    <div 
        v-if="showDatePicker"
        class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"
        @click="closeBottomNav"
    ></div>

    <!-- Sticky Bottom Navigation -->
    <div class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 z-50 lg:hidden">
        <!-- Date Picker Overlay -->
        <div 
            v-if="showDatePicker" 
            class="date-picker-container absolute bottom-full left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 p-4 max-h-[80vh] overflow-y-auto"
        >
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    Select Dates
                </h3>
                <button 
                    @click="closeBottomNav"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <VueDatePicker
                v-model="dateRange"
                :min-date="new Date()"
                :disabled-dates="disabledDates"
                :enable-time-picker="false"
                :inline="true"
                range
                auto-apply
                :teleport="false"
                menu-class-name="dp-custom-menu"
            />

            <!-- Price Summary in Date Picker -->
            <div v-if="priceCalculation" class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
                <div class="text-center">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        {{ priceCalculation.nights }} night{{ priceCalculation.nights !== 1 ? 's' : '' }}
                    </div>
                    <div class="text-xl font-bold text-gray-900 dark:text-gray-100">
                        {{ formatPrice(priceCalculation.total_price) }}
                    </div>
                    <div v-if="priceCalculation.savings > 0" class="text-sm text-green-600 dark:text-green-400">
                        Save {{ formatPrice(priceCalculation.savings) }} ({{ priceCalculation.discount_percentage }}% off)
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        {{ formatPrice(priceCalculation.rate_per_night) }} per night ({{ priceCalculation.rate_used }} rate)
                    </div>
                </div>
                
                <!-- Book Now Button in Date Picker -->
                <button 
                    @click="openBookingModal"
                    :disabled="!canProceedToBooking"
                    class="w-full mt-3 bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span v-if="isLoadingPrice">Loading...</span>
                    <span v-else>Book Now</span>
                </button>
            </div>
            
            <div v-else-if="isLoadingPrice" class="mt-4 text-center">
                <div class="text-gray-600 dark:text-gray-400">Calculating price...</div>
            </div>
        </div>

        <!-- Main Bottom Bar Content -->
        <div class="p-4">
            <!-- Authenticated Users -->
            <div v-if="isAuthenticated" class="flex items-center space-x-3">
                <!-- Price Display -->
                <div class="flex-1 min-w-0">
                    <div class="text-sm text-gray-600 dark:text-gray-400 truncate">
                        {{ areDatesValid ? 'Total Price' : 'Starting from' }}
                    </div>
                    <div class="text-lg font-bold text-gray-900 dark:text-gray-100 truncate">
                        {{ displayPrice }}
                    </div>
                    <div v-if="priceCalculation?.nights" class="text-xs text-gray-500 dark:text-gray-400">
                        {{ priceCalculation.nights }} night{{ priceCalculation.nights !== 1 ? 's' : '' }}
                    </div>
                </div>

                <!-- Date Selection Button -->
                <button 
                    @click="toggleDatePicker($event)"
                    class="flex-shrink-0 px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg font-medium text-sm"
                    :class="{ 'bg-blue-100 dark:bg-blue-900 text-blue-900 dark:text-blue-100': showDatePicker }"
                >
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    {{ areDatesValid ? 'Dates' : 'Select' }}
                </button>

                <!-- Book Button -->
                <button 
                    @click="openBookingModal"
                    :disabled="!canProceedToBooking"
                    class="flex-shrink-0 px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                >
                    <span v-if="isLoadingPrice">...</span>
                    <span v-else-if="!areDatesValid">Book</span>
                    <span v-else>Book</span>
                </button>
            </div>

            <!-- Not Authenticated -->
            <div v-else class="space-y-3">
                <div class="text-center">
                    <div class="text-sm text-gray-600 dark:text-gray-400">Starting from</div>
                    <div class="text-lg font-bold text-gray-900 dark:text-gray-100">
                        {{ current_pricing?.nightly_rate ? formatPrice(current_pricing.nightly_rate) + '/night' : 'Contact for price' }}
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <a 
                        :href="route('login')" 
                        class="bg-blue-600 text-white py-2 px-4 rounded-lg text-center font-medium hover:bg-blue-700 transition-colors text-sm"
                    >
                        Login to Book
                    </a>
                    <a 
                        :href="'https://wa.me/' + props.businessPhone + '?text=' + encodeURIComponent('Hi, I am interested in property #' + props.property.property_id)"
                        target="_blank"
                        class="bg-green-600 text-white py-2 px-4 rounded-lg text-center font-medium hover:bg-green-700 transition-colors text-sm"
                    >
                        WhatsApp
                    </a>
                </div>
            </div>
        </div>

        <!-- Booking Modal -->
        <BookingModal 
            v-if="isBookingModalOpen"
            v-model="isBookingModalOpen"
            :property="property" 
            :check-in-date="dateRange?.[0] || null"
            :check-out-date="dateRange?.[1] || null"
            :total-price="priceCalculation?.total_price || 0"
            :nights="priceCalculation?.nights || 0"
        />
    </div>
</template>

<style scoped>
/* Custom date picker styling */
:deep(.dp-custom-menu) {
    position: static !important;
    transform: none !important;
    box-shadow: none !important;
    border: none !important;
}

/* Prevent body scroll when date picker is open */
:deep(.dp__outer_menu_wrap) {
    position: static !important;
}

/* Mobile optimized date picker */
:deep(.dp__calendar_wrap) {
    max-width: 100% !important;
}

/* Ensure proper z-index */
.date-picker-container {
    z-index: 51;
}
</style>