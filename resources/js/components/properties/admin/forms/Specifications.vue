<script setup lang="ts">
// filepath: /Users/d/Web Development/projects/RealEstate/resources/js/components/properties/admin/forms/Specifications.vue
import { computed } from 'vue';

interface AmenitiesData {
    schools_nearby: string[];
    transport: string[];
    shopping: string[];
    parks: string[];
    medical: string[];
}

interface SpecificationsFormData {
    bedrooms: number | null;
    bathrooms: number | null;
    car_spaces: number | null;
    land_size: number | null;
    floor_area: number | null;
    year_built: number | null;
    zoning: string | null;
    amenities: AmenitiesData;
}

interface Props {
    modelValue: SpecificationsFormData;
    errors?: Record<string, string | undefined>;
}

interface Emits {
    (e: 'update:modelValue', value: SpecificationsFormData): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

// Single computed property for two-way binding
const formData = computed({
    get: () => props.modelValue,
    set: (value: SpecificationsFormData) => emit('update:modelValue', value)
});

// Helper functions for error handling
const getFieldError = (field: string) => {
    return props.errors?.[`specifications.${field}`] || props.errors?.[field];
};

const hasFieldError = (field: string) => {
    return !!getFieldError(field);
};

// Simple helper function to update individual fields
const updateField = (field: keyof SpecificationsFormData, value: any) => {
    formData.value = { ...formData.value, [field]: value };
};

// Amenities management functions
const addAmenityItem = (category: keyof AmenitiesData) => {
    const amenities = { ...formData.value.amenities };
    if (!amenities[category]) {
        amenities[category] = [];
    }
    amenities[category].push('');
    updateField('amenities', amenities);
};

const removeAmenityItem = (category: keyof AmenitiesData, index: number) => {
    const amenities = { ...formData.value.amenities };
    amenities[category].splice(index, 1);
    updateField('amenities', amenities);
};

const updateAmenityItem = (category: keyof AmenitiesData, index: number, value: string) => {
    const amenities = { ...formData.value.amenities };
    amenities[category][index] = value;
    updateField('amenities', amenities);
};

// Amenity categories for display
const amenityCategories = {
    schools_nearby: 'Schools',
    transport: 'Transport',
    shopping: 'Shopping',
    parks: 'Parks & Recreation',
    medical: 'Medical Facilities'
} as const;

// Current year for validation
const currentYear = new Date().getFullYear();
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Property Specifications</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Define the physical characteristics and amenities of this property
            </p>
        </div>
        
        <div class="p-6 space-y-6">
            <!-- Basic Room Specifications -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Room Configuration</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="bedrooms" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Bedrooms
                        </label>
                        <input
                            id="bedrooms"
                            :value="formData.bedrooms || ''"
                            @input="updateField('bedrooms', ($event.target as HTMLInputElement).value ? parseInt(($event.target as HTMLInputElement).value) : null)"
                            type="number"
                            min="0"
                            max="20"
                            :class="[
                                'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100',
                                hasFieldError('bedrooms') 
                                    ? 'border-red-300 focus:border-red-500 focus:ring-red-500' 
                                    : 'border-gray-300 dark:border-gray-600'
                            ]"
                            placeholder="3"
                        />
                        <p v-if="getFieldError('bedrooms')" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ getFieldError('bedrooms') }}
                        </p>
                        <p v-else class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Number of bedrooms in the property
                        </p>
                    </div>

                    <div>
                        <label for="bathrooms" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Bathrooms
                        </label>
                        <input
                            id="bathrooms"
                            :value="formData.bathrooms || ''"
                            @input="updateField('bathrooms', ($event.target as HTMLInputElement).value ? parseInt(($event.target as HTMLInputElement).value) : null)"
                            type="number"
                            min="0"
                            max="20"
                            :class="[
                                'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100',
                                hasFieldError('bathrooms') 
                                    ? 'border-red-300 focus:border-red-500 focus:ring-red-500' 
                                    : 'border-gray-300 dark:border-gray-600'
                            ]"
                            placeholder="2"
                        />
                        <p v-if="getFieldError('bathrooms')" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ getFieldError('bathrooms') }}
                        </p>
                        <p v-else class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Number of bathrooms including powder rooms
                        </p>
                    </div>

                    <div>
                        <label for="car_spaces" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Car Spaces
                        </label>
                        <input
                            id="car_spaces"
                            :value="formData.car_spaces || ''"
                            @input="updateField('car_spaces', ($event.target as HTMLInputElement).value ? parseInt(($event.target as HTMLInputElement).value) : null)"
                            type="number"
                            min="0"
                            max="10"
                            :class="[
                                'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100',
                                hasFieldError('car_spaces') 
                                    ? 'border-red-300 focus:border-red-500 focus:ring-red-500' 
                                    : 'border-gray-300 dark:border-gray-600'
                            ]"
                            placeholder="2"
                        />
                        <p v-if="getFieldError('car_spaces')" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ getFieldError('car_spaces') }}
                        </p>
                        <p v-else class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Parking spaces including garage and carport
                        </p>
                    </div>
                </div>
            </div>

            <!-- Size Specifications -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Property Dimensions</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="land_size" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Land Size (m²)
                        </label>
                        <input
                            id="land_size"
                            :value="formData.land_size || ''"
                            @input="updateField('land_size', ($event.target as HTMLInputElement).value ? parseFloat(($event.target as HTMLInputElement).value) : null)"
                            type="number"
                            min="0"
                            step="0.01"
                            :class="[
                                'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100',
                                hasFieldError('land_size') 
                                    ? 'border-red-300 focus:border-red-500 focus:ring-red-500' 
                                    : 'border-gray-300 dark:border-gray-600'
                            ]"
                            placeholder="500"
                        />
                        <p v-if="getFieldError('land_size')" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ getFieldError('land_size') }}
                        </p>
                        <p v-else class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Total land area in square meters
                        </p>
                    </div>

                    <div>
                        <label for="floor_area" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Floor Area (m²)
                        </label>
                        <input
                            id="floor_area"
                            :value="formData.floor_area || ''"
                            @input="updateField('floor_area', ($event.target as HTMLInputElement).value ? parseFloat(($event.target as HTMLInputElement).value) : null)"
                            type="number"
                            min="0"
                            step="0.01"
                            :class="[
                                'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100',
                                hasFieldError('floor_area') 
                                    ? 'border-red-300 focus:border-red-500 focus:ring-red-500' 
                                    : 'border-gray-300 dark:border-gray-600'
                            ]"
                            placeholder="200"
                        />
                        <p v-if="getFieldError('floor_area')" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ getFieldError('floor_area') }}
                        </p>
                        <p v-else class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Total built-up area in square meters
                        </p>
                    </div>
                </div>
            </div>

            <!-- Additional Property Details -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Additional Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="year_built" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Year Built
                        </label>
                        <input
                            id="year_built"
                            :value="formData.year_built || ''"
                            @input="updateField('year_built', ($event.target as HTMLInputElement).value ? parseInt(($event.target as HTMLInputElement).value) : null)"
                            type="number"
                            min="1800"
                            :max="currentYear"
                            :class="[
                                'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100',
                                hasFieldError('year_built') 
                                    ? 'border-red-300 focus:border-red-500 focus:ring-red-500' 
                                    : 'border-gray-300 dark:border-gray-600'
                            ]"
                            :placeholder="currentYear.toString()"
                        />
                        <p v-if="getFieldError('year_built')" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ getFieldError('year_built') }}
                        </p>
                        <p v-else class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Year the property was constructed
                        </p>
                    </div>

                    <div>
                        <label for="zoning" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Zoning
                        </label>
                        <input
                            id="zoning"
                            :value="formData.zoning || ''"
                            @input="updateField('zoning', ($event.target as HTMLInputElement).value || null)"
                            type="text"
                            :class="[
                                'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100',
                                hasFieldError('zoning') 
                                    ? 'border-red-300 focus:border-red-500 focus:ring-red-500' 
                                    : 'border-gray-300 dark:border-gray-600'
                            ]"
                            placeholder="Residential, Commercial, Mixed-use, etc."
                        />
                        <p v-if="getFieldError('zoning')" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ getFieldError('zoning') }}
                        </p>
                        <p v-else class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Legal zoning classification of the property
                        </p>
                    </div>
                </div>
            </div>

            <!-- Amenities Section -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Nearby Amenities</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                    Add nearby facilities and services that enhance the property's value and convenience.
                </p>
                <div class="space-y-6">
                    <div v-for="(categoryLabel, categoryKey) in amenityCategories" :key="categoryKey" class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ categoryLabel }}
                            </label>
                            <button
                                type="button"
                                @click="addAmenityItem(categoryKey)"
                                class="inline-flex items-center px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs rounded-md transition-colors"
                            >
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Add {{ categoryLabel }}
                            </button>
                        </div>
                        
                        <div v-if="formData.amenities[categoryKey] && formData.amenities[categoryKey].length > 0" class="space-y-2">
                            <div 
                                v-for="(item, index) in formData.amenities[categoryKey]" 
                                :key="`${categoryKey}-${index}`" 
                                class="flex gap-2"
                            >
                                <input
                                    :value="item"
                                    @input="updateAmenityItem(categoryKey, index, ($event.target as HTMLInputElement).value)"
                                    type="text"
                                    class="flex-1 rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100"
                                    :placeholder="`Add ${categoryLabel.toLowerCase()} information (e.g., distance, name)`"
                                />
                                <button
                                    type="button"
                                    @click="removeAmenityItem(categoryKey, index)"
                                    class="px-3 py-2 bg-red-500 hover:bg-red-600 text-white rounded-md transition-colors text-sm"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <div v-else class="text-sm text-gray-500 dark:text-gray-400 italic">
                            No {{ categoryLabel.toLowerCase() }} added yet. Click "Add {{ categoryLabel }}" to begin.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Specifications Info -->
            <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 4a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1V8zm8 0a1 1 0 011-1h4a1 1 0 011 1v6a1 1 0 01-1 1h-4a1 1 0 01-1-1V8z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-purple-800 dark:text-purple-300">
                            Specification Guidelines
                        </h3>
                        <div class="mt-2 text-sm text-purple-700 dark:text-purple-400">
                            <ul class="list-disc list-inside space-y-1">
                                <li><strong>Room counts:</strong> Include all bedrooms and bathrooms, including ensuites</li>
                                <li><strong>Areas:</strong> Use square meters (m²) for consistent measurement</li>
                                <li><strong>Amenities:</strong> Be specific with distances and names (e.g., "Green School - 2km")</li>
                                <li><strong>Accuracy:</strong> Verify all measurements and counts for property listings</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>