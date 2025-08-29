<script setup lang="ts">
// filepath: /Users/d/Web Development/projects/RealEstate/resources/js/components/properties/admin/forms/Location.vue
import { computed } from 'vue';

interface LocationFormData {
    street_number: string | null;
    street_name: string;
    village: string | null;
    district: string;
    regency: string;
    state: string | null;
    postcode: string;
    country: string | null;
    latitude: number | null;
    longitude: number | null;
}

interface Props {
    modelValue: LocationFormData;
    errors?: Record<string, string | undefined>;
}

interface Emits {
    (e: 'update:modelValue', value: LocationFormData): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

// Single computed property for two-way binding
const formData = computed({
    get: () => props.modelValue,
    set: (value: LocationFormData) => emit('update:modelValue', value)
});

// Helper functions for error handling
const getFieldError = (field: string) => {
    return props.errors?.[`location.${field}`] || props.errors?.[field];
};

const hasFieldError = (field: string) => {
    return !!getFieldError(field);
};

// Simple helper function to update individual fields
const updateField = (field: keyof LocationFormData, value: any) => {
    formData.value = { ...formData.value, [field]: value };
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Location & Address</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Specify the exact location and address details of this property
            </p>
        </div>
        
        <div class="p-6 space-y-6">
            <!-- Street Address -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <label for="street_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Street Number
                    </label>
                    <input
                        id="street_number"
                        :value="formData.street_number || ''"
                        @input="updateField('street_number', ($event.target as HTMLInputElement).value || null)"
                        type="text"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100"
                        placeholder="123"
                    />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        House or building number
                    </p>
                </div>

                <div class="md:col-span-3">
                    <label for="street_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Street Name *
                    </label>
                    <input
                        id="street_name"
                        :value="formData.street_name"
                        @input="updateField('street_name', ($event.target as HTMLInputElement).value)"
                        type="text"
                        :class="[
                            'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100',
                            hasFieldError('street_name') 
                                ? 'border-red-300 focus:border-red-500 focus:ring-red-500' 
                                : 'border-gray-300 dark:border-gray-600'
                        ]"
                        placeholder="Jalan Raya Ubud"
                        required
                    />
                    <p v-if="getFieldError('street_name')" class="mt-1 text-sm text-red-600 dark:text-red-400">
                        {{ getFieldError('street_name') }}
                    </p>
                    <p v-else class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Main street or road name where the property is located
                    </p>
                </div>
            </div>

            <!-- Bali-specific Address Structure -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="village" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Village (Desa/Kelurahan)
                    </label>
                    <input
                        id="village"
                        :value="formData.village || ''"
                        @input="updateField('village', ($event.target as HTMLInputElement).value || null)"
                        type="text"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100"
                        placeholder="Ubud"
                    />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Local village or administrative area
                    </p>
                </div>

                <div>
                    <label for="district" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        District (Kecamatan) *
                    </label>
                    <input
                        id="district"
                        :value="formData.district"
                        @input="updateField('district', ($event.target as HTMLInputElement).value)"
                        type="text"
                        :class="[
                            'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100',
                            hasFieldError('district') 
                                ? 'border-red-300 focus:border-red-500 focus:ring-red-500' 
                                : 'border-gray-300 dark:border-gray-600'
                        ]"
                        placeholder="Ubud"
                        required
                    />
                    <p v-if="getFieldError('district')" class="mt-1 text-sm text-red-600 dark:text-red-400">
                        {{ getFieldError('district') }}
                    </p>
                    <p v-else class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Sub-district administrative division
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="regency" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Regency (Kabupaten) *
                    </label>
                    <input
                        id="regency"
                        :value="formData.regency"
                        @input="updateField('regency', ($event.target as HTMLInputElement).value)"
                        type="text"
                        :class="[
                            'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100',
                            hasFieldError('regency') 
                                ? 'border-red-300 focus:border-red-500 focus:ring-red-500' 
                                : 'border-gray-300 dark:border-gray-600'
                        ]"
                        placeholder="Gianyar"
                        required
                    />
                    <p v-if="getFieldError('regency')" class="mt-1 text-sm text-red-600 dark:text-red-400">
                        {{ getFieldError('regency') }}
                    </p>
                    <p v-else class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Regency or city administrative area
                    </p>
                </div>

                <div>
                    <label for="state" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        State (Provinsi)
                    </label>
                    <input
                        id="state"
                        :value="formData.state || ''"
                        @input="updateField('state', ($event.target as HTMLInputElement).value || null)"
                        type="text"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100"
                        placeholder="Bali"
                    />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Province or state
                    </p>
                </div>

                <div>
                    <label for="postcode" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Postcode *
                    </label>
                    <input
                        id="postcode"
                        :value="formData.postcode"
                        @input="updateField('postcode', ($event.target as HTMLInputElement).value)"
                        type="text"
                        :class="[
                            'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100',
                            hasFieldError('postcode') 
                                ? 'border-red-300 focus:border-red-500 focus:ring-red-500' 
                                : 'border-gray-300 dark:border-gray-600'
                        ]"
                        placeholder="80571"
                        required
                    />
                    <p v-if="getFieldError('postcode')" class="mt-1 text-sm text-red-600 dark:text-red-400">
                        {{ getFieldError('postcode') }}
                    </p>
                    <p v-else class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Postal or ZIP code
                    </p>
                </div>
            </div>

            <!-- Country (hidden but settable) -->
            <div class="hidden">
                <input
                    :value="formData.country || 'Indonesia'"
                    @input="updateField('country', ($event.target as HTMLInputElement).value)"
                    type="hidden"
                />
            </div>

            <!-- GPS Coordinates -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">GPS Coordinates</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="latitude" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Latitude
                        </label>
                        <input
                            id="latitude"
                            :value="formData.latitude || ''"
                            @input="updateField('latitude', ($event.target as HTMLInputElement).value ? parseFloat(($event.target as HTMLInputElement).value) : null)"
                            type="number"
                            step="any"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100"
                            placeholder="-8.5068"
                        />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Decimal degrees (e.g., -8.5068)
                        </p>
                    </div>

                    <div>
                        <label for="longitude" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Longitude
                        </label>
                        <input
                            id="longitude"
                            :value="formData.longitude || ''"
                            @input="updateField('longitude', ($event.target as HTMLInputElement).value ? parseFloat(($event.target as HTMLInputElement).value) : null)"
                            type="number"
                            step="any"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100"
                            placeholder="115.2625"
                        />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Decimal degrees (e.g., 115.2625)
                        </p>
                    </div>
                </div>
            </div>

            <!-- Location Info -->
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-green-800 dark:text-green-300">
                            Address Guidelines
                        </h3>
                        <div class="mt-2 text-sm text-green-700 dark:text-green-400">
                            <ul class="list-disc list-inside space-y-1">
                                <li><strong>Required fields:</strong> Street name, district, regency, and postcode are mandatory</li>
                                <li><strong>GPS coordinates:</strong> Optional but recommended for accurate mapping</li>
                                <li><strong>Indonesian format:</strong> Following the Desa/Kelurahan → Kecamatan → Kabupaten structure</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>