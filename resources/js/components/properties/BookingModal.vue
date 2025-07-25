<script setup lang="ts">
import { ref, reactive, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import type { Property } from '@/types';
import { formatPrice } from '@/utils/formatters';

interface Props {
    modelValue: boolean;
    property: Property;
    checkInDate: Date | null;
    checkOutDate: Date | null;
    totalPrice: number;
    nights: number;
}

interface Emits {
    (e: 'update:modelValue', value: boolean): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

// Computed for modal visibility using v-model pattern
const isOpen = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
});

// Reactive form state
const form = reactive({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    check_in_date: '',
    check_out_date: '',
    number_of_guests: 2,
    total_price: 0,
    // Optional fields
    number_of_rooms: null as number | null,
    flexible_dates: false,
    special_requests: '',
    source: 'direct', // Default to direct booking
    external_booking_id: null as string | null,
    booking_type: 'booking'
});

// Reactive refs
const isSubmitting = ref(false);
const errors = ref<Record<string, string[]>>({});

// Watch for date changes and update form
watch([() => props.checkInDate, () => props.checkOutDate], ([checkIn, checkOut]) => {
    if (checkIn) {
        form.check_in_date = checkIn.toISOString().split('T')[0];
    }
    if (checkOut) {
        form.check_out_date = checkOut.toISOString().split('T')[0];
    }
    form.total_price = props.totalPrice;
}, { immediate: true });

// Computed properties for display
const formattedCheckInDate = computed(() => {
    return props.checkInDate?.toLocaleDateString('en-US', {
        weekday: 'short',
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    }) ?? '';
});

const formattedCheckOutDate = computed(() => {
    return props.checkOutDate?.toLocaleDateString('en-US', {
        weekday: 'short',
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    }) ?? '';
});

// Methods using composition API
const submitBooking = () => {
    // If already submitting, do nothing
    if (isSubmitting.value) return;
    
    isSubmitting.value = true;
    errors.value = {};

    // Prepare form data - exclude null values and property_id (comes from URL)
    const formData = Object.fromEntries(
        Object.entries(form).filter(([, value]) => value !== null && value !== '')
    );

    // API call to submit booking
    router.post(`/properties/${props.property.id}/bookings`, formData, {
        onSuccess: () => {
            closeModal();
        },
        onError: (responseErrors) => {
            console.log('Booking errors:', responseErrors);
            
            // Format errors properly for TypeScript
            const formattedErrors: Record<string, string[]> = {};
            for (const [key, value] of Object.entries(responseErrors)) {
                formattedErrors[key] = Array.isArray(value) ? value : [String(value)];
            }
            errors.value = formattedErrors;
            console.error('Booking submission errors:', errors.value);
        },
        onFinish: () => {
            isSubmitting.value = false;
        }
    });
};

const closeModal = () => {
    isOpen.value = false;
    // Reset form when closing
    Object.assign(form, {
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        number_of_guests: 2,
        special_requests: '',
        check_in_date: '',
        check_out_date: ''
    });
    errors.value = {};
};

// Handle escape key
const handleKeydown = (event: KeyboardEvent) => {
    if (event.key === 'Escape') {
        closeModal();
    }
};
</script>

<template>
    <Teleport to="body">
        <!-- Modal Overlay with Transitions -->
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div 
                v-if="isOpen"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
                @click.self="closeModal"
                @keydown="handleKeydown"
                tabindex="-1"
            >
                <!-- Modal Content with Transition -->
                <Transition
                    enter-active-class="transition-all duration-300"
                    enter-from-class="opacity-0 scale-95"
                    enter-to-class="opacity-100 scale-100"
                    leave-active-class="transition-all duration-300"
                    leave-from-class="opacity-100 scale-100"
                    leave-to-class="opacity-0 scale-95"
                >
                    <div 
                        v-if="isOpen"
                        class="bg-white dark:bg-gray-800 rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-xl"
                    >
                        <!-- Modal Header -->
                        <div class="flex justify-between items-center p-6 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                                Book {{ property.title }}
                            </h2>
                            <button 
                                @click="closeModal"
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors"
                                aria-label="Close modal"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Modal Content -->
                        <div class="p-6">
                            <!-- Booking Summary -->
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-6">
                                <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-3">Booking Summary</h3>
                                <!-- Errors -->
                                 <p v-if="errors.check_in_date" class="text-red-500 text-xs mt-1">
                                            {{ errors.check_in_date[0] }}
                                </p>
                                <p v-if="errors.check_out_date" class="text-red-500 text-xs mt-1">
                                    {{ errors.check_out_date[0] }}
                                </p>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Check-in:</span>
                                        <span class="text-gray-900 dark:text-gray-100">{{ formattedCheckInDate }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Check-out:</span>
                                        <span class="text-gray-900 dark:text-gray-100">{{ formattedCheckOutDate }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Nights:</span>
                                        <span class="text-gray-900 dark:text-gray-100">{{ nights }}</span>
                                    </div>
                                    <div class="flex justify-between font-medium border-t border-gray-200 dark:border-gray-600 pt-2">
                                        <span class="text-gray-900 dark:text-gray-100">Total:</span>
                                        <span class="text-gray-900 dark:text-gray-100">{{ formatPrice(totalPrice) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Booking Form -->
                            <form @submit.prevent="submitBooking" class="space-y-4">
                                <!-- Guest Information Grid -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- First Name -->
                                    <div>
                                        <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            First Name *
                                        </label>
                                        <input
                                            id="first_name"
                                            v-model="form.first_name"
                                            type="text"
                                            required
                                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            :class="{ 'border-red-500': errors.first_name }"
                                        />
                                        <p v-if="errors.first_name" class="text-red-500 text-xs mt-1">
                                            {{ errors.first_name[0] }}
                                        </p>
                                    </div>

                                    <!-- Last Name -->
                                    <div>
                                        <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Last Name *
                                        </label>
                                        <input
                                            id="last_name"
                                            v-model="form.last_name"
                                            type="text"
                                            required
                                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            :class="{ 'border-red-500': errors.last_name }"
                                        />
                                        <p v-if="errors.last_name" class="text-red-500 text-xs mt-1">
                                            {{ errors.last_name[0] }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Email -->
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Email *
                                    </label>
                                    <input
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        required
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        :class="{ 'border-red-500': errors.email }"
                                    />
                                    <p v-if="errors.email" class="text-red-500 text-xs mt-1">
                                        {{ errors.email[0] }}
                                    </p>
                                </div>

                                <!-- Phone -->
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Phone
                                    </label>
                                    <input
                                        id="phone"
                                        v-model="form.phone"
                                        type="tel"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        :class="{ 'border-red-500': errors.phone }"
                                    />
                                    <p v-if="errors.phone" class="text-red-500 text-xs mt-1">
                                        {{ errors.phone[0] }}
                                    </p>
                                </div>

                                <!-- Number of Guests -->
                                <div>
                                    <label for="guests" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Number of Guests
                                    </label>
                                    <select
                                        id="guests"
                                        v-model="form.number_of_guests"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option v-for="i in 10" :key="i" :value="i">
                                            {{ i }} Guest{{ i !== 1 ? 's' : '' }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Special Requests -->
                                <div>
                                    <label for="requests" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Special Requests
                                    </label>
                                    <textarea
                                        id="requests"
                                        v-model="form.special_requests"
                                        rows="3"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Any special requests or requirements..."
                                    ></textarea>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex justify-end space-x-3 pt-4">
                                    <button
                                        type="button"
                                        @click="closeModal"
                                        class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-600 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        :disabled="isSubmitting"
                                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <span v-if="isSubmitting">Submitting...</span>
                                        <span v-else>Submit Booking Request</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>