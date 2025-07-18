<script setup lang="ts">
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import type { Property } from '@/types';

interface Props {
    property: Property;
    checkInDate: string;
    checkOutDate: string;
    totalPrice: number;
    nights: number;
}

interface Emits {
    (e: 'close'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

// Form state
const isSubmitting = ref(false);
const form = reactive({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    number_of_guests: 2,
    special_requests: '',
    check_in_date: props.checkInDate,
    check_out_date: props.checkOutDate
});

// Validation errors
const errors = ref<Record<string, string[]>>({});

// Methods
const formatPrice = (price: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(price);
};

const submitBooking = () => {
    if (isSubmitting.value) return;
    
    isSubmitting.value = true;
    errors.value = {};

    try {
        router.post(`/properties/${props.property.id}/bookings`, {
            ...form,
            property_id: props.property.id,
            total_price: props.totalPrice
        }, {
            onSuccess: () => {
                emit('close');
                // Show success message or redirect
                alert('Booking request submitted successfully!');
            },
            onError: (responseErrors) => {
                // Ensure errors are always string arrays
                const formattedErrors: Record<string, string[]> = {};
                for (const key in responseErrors) {
                    formattedErrors[key] = Array.isArray(responseErrors[key])
                        ? responseErrors[key]
                        : [String(responseErrors[key])];
                }
                errors.value = formattedErrors;
            },
            onFinish: () => {
                isSubmitting.value = false;
            }
        });
    } catch (error) {
        console.error('Booking submission error:', error);
        isSubmitting.value = false;
    }
};

const closeModal = () => {
    emit('close');
};
</script>

<template>
    <!-- Modal Overlay -->
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
        @click.self="closeModal"
        @keydown.escape="closeModal">
        <div class="bg-white dark:bg-gray-800 rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <!-- Modal Header -->
            <div class="flex justify-between items-center p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                    Book {{ property.title }}
                </h2>
                <button 
                    @click="closeModal"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
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
                    <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Booking Summary</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Check-in:</span>
                            <span class="text-gray-900 dark:text-gray-100">{{ new Date(checkInDate).toLocaleDateString() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Check-out:</span>
                            <span class="text-gray-900 dark:text-gray-100">{{ new Date(checkOutDate).toLocaleDateString() }}</span>
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
                    <!-- Guest Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                First Name *
                            </label>
                            <input
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

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Last Name *
                            </label>
                            <input
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

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Email *
                        </label>
                        <input
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

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Phone
                        </label>
                        <input
                            v-model="form.phone"
                            type="tel"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            :class="{ 'border-red-500': errors.phone }"
                        />
                        <p v-if="errors.phone" class="text-red-500 text-xs mt-1">
                            {{ errors.phone[0] }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Number of Guests
                        </label>
                        <select
                            v-model="form.number_of_guests"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option v-for="i in 10" :key="i" :value="i">{{ i }} Guest{{ i !== 1 ? 's' : '' }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Special Requests
                        </label>
                        <textarea
                            v-model="form.special_requests"
                            rows="3"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Any special requests or requirements..."
                        ></textarea>
                    </div>

                    <!-- Submit Button -->
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
                            {{ isSubmitting ? 'Submitting...' : 'Submit Booking Request' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>