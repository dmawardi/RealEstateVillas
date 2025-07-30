<script setup lang="ts">
import { DateRange } from '@/types';
import { ref, watch, computed } from 'vue';

interface Props {
    modelValue?: DateRange;
}

interface Emits {
    (e: 'update:modelValue', value: DateRange): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

// Local reactive refs for date inputs
const checkInDate = ref(props.modelValue?.checkIn || '');
const checkOutDate = ref(props.modelValue?.checkOut || '');

// Get today's date for min attribute
const today = new Date().toISOString().split('T')[0];

// Watch for changes and emit single v-model update
watch([checkInDate, checkOutDate], ([newCheckIn, newCheckOut]) => {
    emit('update:modelValue', {
        checkIn: newCheckIn,
        checkOut: newCheckOut
    });
    
    // If check-in is after check-out, clear check-out
    if (newCheckIn && newCheckOut && newCheckIn >= newCheckOut) {
        checkOutDate.value = '';
    }
}, { deep: true });

// Watch for external model value changes
watch(() => props.modelValue, (newValue) => {
    if (newValue) {
        checkInDate.value = newValue.checkIn || '';
        checkOutDate.value = newValue.checkOut || '';
    }
}, { deep: true });

// Clear both dates
const clearDates = () => {
    checkInDate.value = '';
    checkOutDate.value = '';
};

// Check if dates are valid
const areDatesValid = computed(() => {
    return checkInDate.value && checkOutDate.value && checkInDate.value < checkOutDate.value;
});

// Get minimum checkout date (day after check-in)
const getMinCheckOutDate = computed(() => {
    if (!checkInDate.value) return today;
    
    const checkIn = new Date(checkInDate.value);
    checkIn.setDate(checkIn.getDate() + 1);
    return checkIn.toISOString().split('T')[0];
});

// Calculate number of nights
const getNights = computed(() => {
    if (!areDatesValid.value) return 0;
    
    const checkIn = new Date(checkInDate.value);
    const checkOut = new Date(checkOutDate.value);
    return Math.ceil((checkOut.getTime() - checkIn.getTime()) / (1000 * 60 * 60 * 24));
});

// Check if any dates are set
const hasDates = computed(() => {
    return checkInDate.value || checkOutDate.value;
});
</script>

<template>
    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                Booking Dates
            </h3>
            <button 
                v-if="hasDates"
                @click="clearDates"
                class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
            >
                Clear
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Check-in Date -->
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Check-in
                </label>
                <input
                    v-model="checkInDate"
                    type="date"
                    :min="today"
                    class="w-full text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Select check-in date"
                />
            </div>

            <!-- Check-out Date -->
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Check-out
                </label>
                <input
                    v-model="checkOutDate"
                    type="date"
                    :min="getMinCheckOutDate"
                    :disabled="!checkInDate"
                    class="w-full text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    placeholder="Select check-out date"
                />
            </div>
        </div>

        <!-- Date Summary -->
        <div v-if="areDatesValid" class="text-xs text-gray-600 dark:text-gray-400 bg-blue-50 dark:bg-blue-900/30 rounded p-2">
            <div class="flex items-center justify-between">
                <span>{{ getNights }} night{{ getNights !== 1 ? 's' : '' }}</span>
                <span>{{ new Date(checkInDate).toLocaleDateString() }} - {{ new Date(checkOutDate).toLocaleDateString() }}</span>
            </div>
        </div>

        <!-- Validation Message -->
        <div v-else-if="checkInDate && checkOutDate && checkInDate >= checkOutDate" class="text-xs text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30 rounded p-2">
            Check-out date must be after check-in date
        </div>
    </div>
</template>