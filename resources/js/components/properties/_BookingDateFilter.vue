<script setup lang="ts">
import { ref, watch } from 'vue';

interface Props {
    checkIn?: string;
    checkOut?: string;
}

interface Emits {
    (e: 'update:checkIn', value: string): void;
    (e: 'update:checkOut', value: string): void;
    (e: 'datesChanged', dates: { checkIn: string; checkOut: string }): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

// Local reactive refs for date inputs
const checkInDate = ref(props.checkIn || '');
const checkOutDate = ref(props.checkOut || '');

// Get today's date for min attribute
const today = new Date().toISOString().split('T')[0];

// Watch for changes and emit updates
watch(checkInDate, (newDate) => {
    emit('update:checkIn', newDate);
    emitDatesChanged();
    
    // If check-in is after check-out, clear check-out
    if (newDate && checkOutDate.value && newDate >= checkOutDate.value) {
        checkOutDate.value = '';
        emit('update:checkOut', '');
    }
});

watch(checkOutDate, (newDate) => {
    emit('update:checkOut', newDate);
    emitDatesChanged();
});

// Helper function to emit combined dates
const emitDatesChanged = () => {
    if (checkInDate.value && checkOutDate.value) {
        emit('datesChanged', {
            checkIn: checkInDate.value,
            checkOut: checkOutDate.value
        });
    }
};

// Clear both dates
const clearDates = () => {
    checkInDate.value = '';
    checkOutDate.value = '';
    emit('update:checkIn', '');
    emit('update:checkOut', '');
};

// Check if dates are valid
const areDatesValid = () => {
    return checkInDate.value && checkOutDate.value && checkInDate.value < checkOutDate.value;
};

// Get minimum checkout date (day after check-in)
const getMinCheckOutDate = () => {
    if (!checkInDate.value) return today;
    
    const checkIn = new Date(checkInDate.value);
    checkIn.setDate(checkIn.getDate() + 1);
    return checkIn.toISOString().split('T')[0];
};

// Calculate number of nights
const getNights = () => {
    if (!areDatesValid()) return 0;
    
    const checkIn = new Date(checkInDate.value);
    const checkOut = new Date(checkOutDate.value);
    return Math.ceil((checkOut.getTime() - checkIn.getTime()) / (1000 * 60 * 60 * 24));
};
</script>

<template>
    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                Booking Dates
            </h3>
            <button 
                v-if="checkInDate || checkOutDate"
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
                    :min="getMinCheckOutDate()"
                    :disabled="!checkInDate"
                    class="w-full text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    placeholder="Select check-out date"
                />
            </div>
        </div>

        <!-- Date Summary -->
        <div v-if="areDatesValid()" class="text-xs text-gray-600 dark:text-gray-400 bg-blue-50 dark:bg-blue-900/30 rounded p-2">
            <div class="flex items-center justify-between">
                <span>{{ getNights() }} night{{ getNights() !== 1 ? 's' : '' }}</span>
                <span>{{ new Date(checkInDate).toLocaleDateString() }} - {{ new Date(checkOutDate).toLocaleDateString() }}</span>
            </div>
        </div>

        <!-- Validation Message -->
        <div v-else-if="checkInDate && checkOutDate && checkInDate >= checkOutDate" class="text-xs text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30 rounded p-2">
            Check-out date must be after check-in date
        </div>
    </div>
</template>