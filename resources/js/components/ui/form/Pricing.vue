<script setup lang="ts">
// filepath: /Users/d/Web Development/projects/RealEstate/resources/js/components/properties/admin/forms/Pricing.vue
import { computed } from 'vue';

interface PricingFormData {
    price: number | null;
    price_type: string | null;
    nightly_rate: number | null;
    weekly_rate: number | null;
    monthly_rate: number | null;
    available_date: string | null;
    inspection_times: string | null;
}

interface Props {
    modelValue: PricingFormData;
    priceTypes: Record<string, string>;
    listingType: string; // To determine if it's rental or sale
    errors?: Record<string, string | undefined>;
}

interface Emits {
    (e: 'update:modelValue', value: PricingFormData): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

// Single computed property for two-way binding
const formData = computed({
    get: () => props.modelValue,
    set: (value: PricingFormData) => emit('update:modelValue', value)
});

// Helper functions for error handling
const getFieldError = (field: string) => {
    return props.errors?.[`pricing.${field}`] || props.errors?.[field];
};

const hasFieldError = (field: string) => {
    return !!getFieldError(field);
};

// Simple helper function to update individual fields
const updateField = (field: keyof PricingFormData, value: any) => {
    formData.value = { ...formData.value, [field]: value };
};

// Computed properties
const isRentalProperty = computed(() => props.listingType === 'for_rent');
const showRentalPricing = computed(() => isRentalProperty.value);
const showSalePrice = computed(() => !isRentalProperty.value);

// Format currency for display
const formatCurrency = (amount: number | null): string => {
    if (!amount) return '';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

// Calculate weekly discount if both nightly and weekly rates exist
const weeklyDiscount = computed(() => {
    if (formData.value.nightly_rate && formData.value.weekly_rate) {
        const nightlyTotal = formData.value.nightly_rate * 7;
        const discount = ((nightlyTotal - formData.value.weekly_rate) / nightlyTotal) * 100;
        return discount > 0 ? Math.round(discount) : 0;
    }
    return 0;
});

// Calculate monthly discount if both nightly and monthly rates exist
const monthlyDiscount = computed(() => {
    if (formData.value.nightly_rate && formData.value.monthly_rate) {
        const nightlyTotal = formData.value.nightly_rate * 30;
        const discount = ((nightlyTotal - formData.value.monthly_rate) / nightlyTotal) * 100;
        return discount > 0 ? Math.round(discount) : 0;
    }
    return 0;
});
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Pricing Information</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Set competitive pricing for {{ isRentalProperty ? 'rental rates' : 'property sale' }}
            </p>
        </div>
        
        <div class="p-6 space-y-6">
            <!-- Sale Price Section (for sale properties) -->
            <div v-if="showSalePrice">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Sale Price</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Price (IDR) *
                        </label>
                        <input
                            id="price"
                            :value="formData.price || ''"
                            @input="updateField('price', ($event.target as HTMLInputElement).value ? parseFloat(($event.target as HTMLInputElement).value) : null)"
                            type="number"
                            min="0"
                            step="1000000"
                            :class="[
                                'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100',
                                hasFieldError('price') 
                                    ? 'border-red-300 focus:border-red-500 focus:ring-red-500' 
                                    : 'border-gray-300 dark:border-gray-600'
                            ]"
                            placeholder="500000000"
                        />
                        <p v-if="getFieldError('price')" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ getFieldError('price') }}
                        </p>
                        <p v-else-if="formData.price" class="mt-1 text-xs text-green-600 dark:text-green-400">
                            {{ formatCurrency(formData.price) }}
                        </p>
                        <p v-else class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Enter the asking price in Indonesian Rupiah
                        </p>
                    </div>

                    <div>
                        <label for="price_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Price Type *
                        </label>
                        <select
                            id="price_type"
                            :value="formData.price_type || ''"
                            @change="updateField('price_type', ($event.target as HTMLInputElement).value || null)"
                            :class="[
                                'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100',
                                hasFieldError('price_type') 
                                    ? 'border-red-300 focus:border-red-500 focus:ring-red-500' 
                                    : 'border-gray-300 dark:border-gray-600'
                            ]"
                            required
                        >
                            <option value="">Select Price Type</option>
                            <option v-for="(label, value) in priceTypes" :key="value" :value="value">
                                {{ label }}
                            </option>
                        </select>
                        <p v-if="getFieldError('price_type')" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ getFieldError('price_type') }}
                        </p>
                        <p v-else class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Indicates pricing flexibility (fixed, negotiable, etc.)
                        </p>
                    </div>
                </div>
            </div>

            <!-- Rental Pricing Section (for rental properties) -->
            <div v-if="showRentalPricing">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Rental Rates</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                    Set different rates for various rental periods. Weekly and monthly rates typically offer discounts over nightly rates.
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="nightly_rate" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Nightly Rate (IDR)
                        </label>
                        <input
                            id="nightly_rate"
                            :value="formData.nightly_rate || ''"
                            @input="updateField('nightly_rate', ($event.target as HTMLInputElement).value ? parseFloat(($event.target as HTMLInputElement).value) : null)"
                            type="number"
                            min="0"
                            step="50000"
                            :class="[
                                'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100',
                                hasFieldError('nightly_rate') 
                                    ? 'border-red-300 focus:border-red-500 focus:ring-red-500' 
                                    : 'border-gray-300 dark:border-gray-600'
                            ]"
                            placeholder="500000"
                        />
                        <p v-if="getFieldError('nightly_rate')" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ getFieldError('nightly_rate') }}
                        </p>
                        <p v-else-if="formData.nightly_rate" class="mt-1 text-xs text-green-600 dark:text-green-400">
                            {{ formatCurrency(formData.nightly_rate) }} per night
                        </p>
                        <p v-else class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Rate for short-term stays
                        </p>
                    </div>

                    <div>
                        <label for="weekly_rate" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Weekly Rate (IDR)
                        </label>
                        <input
                            id="weekly_rate"
                            :value="formData.weekly_rate || ''"
                            @input="updateField('weekly_rate', ($event.target as HTMLInputElement).value ? parseFloat(($event.target as HTMLInputElement).value) : null)"
                            type="number"
                            min="0"
                            step="100000"
                            :class="[
                                'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100',
                                hasFieldError('weekly_rate') 
                                    ? 'border-red-300 focus:border-red-500 focus:ring-red-500' 
                                    : 'border-gray-300 dark:border-gray-600'
                            ]"
                            placeholder="3000000"
                        />
                        <p v-if="getFieldError('weekly_rate')" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ getFieldError('weekly_rate') }}
                        </p>
                        <p v-else-if="formData.weekly_rate" class="mt-1 text-xs text-green-600 dark:text-green-400">
                            {{ formatCurrency(formData.weekly_rate) }} per week
                            <span v-if="weeklyDiscount > 0" class="text-blue-600 dark:text-blue-400">
                                ({{ weeklyDiscount }}% discount)
                            </span>
                        </p>
                        <p v-else class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Rate for 7-day stays
                        </p>
                    </div>

                    <div>
                        <label for="monthly_rate" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Monthly Rate (IDR)
                        </label>
                        <input
                            id="monthly_rate"
                            :value="formData.monthly_rate || ''"
                            @input="updateField('monthly_rate', ($event.target as HTMLInputElement).value ? parseFloat(($event.target as HTMLInputElement).value) : null)"
                            type="number"
                            min="0"
                            step="500000"
                            :class="[
                                'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100',
                                hasFieldError('monthly_rate') 
                                    ? 'border-red-300 focus:border-red-500 focus:ring-red-500' 
                                    : 'border-gray-300 dark:border-gray-600'
                            ]"
                            placeholder="10000000"
                        />
                        <p v-if="getFieldError('monthly_rate')" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ getFieldError('monthly_rate') }}
                        </p>
                        <p v-else-if="formData.monthly_rate" class="mt-1 text-xs text-green-600 dark:text-green-400">
                            {{ formatCurrency(formData.monthly_rate) }} per month
                            <span v-if="monthlyDiscount > 0" class="text-blue-600 dark:text-blue-400">
                                ({{ monthlyDiscount }}% discount)
                            </span>
                        </p>
                        <p v-else class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Rate for 30+ day stays
                        </p>
                    </div>
                </div>

                <!-- Rental Rate Summary -->
                <div v-if="formData.nightly_rate || formData.weekly_rate || formData.monthly_rate" class="mt-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-blue-800 dark:text-blue-300 mb-2">Rate Summary</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div v-if="formData.nightly_rate" class="text-blue-700 dark:text-blue-400">
                            <span class="font-medium">Nightly:</span> {{ formatCurrency(formData.nightly_rate) }}
                        </div>
                        <div v-if="formData.weekly_rate" class="text-blue-700 dark:text-blue-400">
                            <span class="font-medium">Weekly:</span> {{ formatCurrency(formData.weekly_rate) }}
                            <span v-if="weeklyDiscount > 0" class="text-green-600 dark:text-green-400">
                                ({{ weeklyDiscount }}% off)
                            </span>
                        </div>
                        <div v-if="formData.monthly_rate" class="text-blue-700 dark:text-blue-400">
                            <span class="font-medium">Monthly:</span> {{ formatCurrency(formData.monthly_rate) }}
                            <span v-if="monthlyDiscount > 0" class="text-green-600 dark:text-green-400">
                                ({{ monthlyDiscount }}% off)
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Pricing Information -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Availability & Viewing</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="available_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Available Date
                        </label>
                        <input
                            id="available_date"
                            :value="formData.available_date || ''"
                            @input="updateField('available_date', ($event.target as HTMLInputElement).value || null)"
                            type="date"
                            :class="[
                                'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100',
                                hasFieldError('available_date') 
                                    ? 'border-red-300 focus:border-red-500 focus:ring-red-500' 
                                    : 'border-gray-300 dark:border-gray-600'
                            ]"
                        />
                        <p v-if="getFieldError('available_date')" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ getFieldError('available_date') }}
                        </p>
                        <p v-else class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            When will this property be available for {{ isRentalProperty ? 'rent' : 'purchase' }}?
                        </p>
                    </div>

                    <div>
                        <label for="inspection_times" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Inspection Times
                        </label>
                        <textarea
                            id="inspection_times"
                            :value="formData.inspection_times || ''"
                            @input="updateField('inspection_times', ($event.target as HTMLInputElement).value || null)"
                            rows="3"
                            :class="[
                                'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100',
                                hasFieldError('inspection_times') 
                                    ? 'border-red-300 focus:border-red-500 focus:ring-red-500' 
                                    : 'border-gray-300 dark:border-gray-600'
                            ]"
                            placeholder="Saturday 10am-11am, Sunday 2pm-3pm&#10;Or by appointment"
                        ></textarea>
                        <p v-if="getFieldError('inspection_times')" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ getFieldError('inspection_times') }}
                        </p>
                        <p v-else class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Specify when potential {{ isRentalProperty ? 'tenants' : 'buyers' }} can view the property
                        </p>
                    </div>
                </div>
            </div>

            <!-- Pricing Guidelines -->
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-300">
                            Pricing Guidelines
                        </h3>
                        <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-400">
                            <ul class="list-disc list-inside space-y-1">
                                <li v-if="isRentalProperty"><strong>Rental rates:</strong> Consider offering discounts for longer stays to attract extended bookings</li>
                                <li v-else><strong>Sale price:</strong> Research comparable properties in the area to ensure competitive pricing</li>
                                <li><strong>Market research:</strong> Check similar properties in the area for competitive rates</li>
                                <li><strong>Seasonal pricing:</strong> Consider adjusting rates based on peak and off-peak seasons</li>
                                <li><strong>Transparency:</strong> All prices should be inclusive of taxes where applicable</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>