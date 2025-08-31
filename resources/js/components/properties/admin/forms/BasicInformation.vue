<script setup lang="ts">
import { computed } from 'vue';

interface BasicFormData {
    title: string;
    description: string;
    property_type: string;
    listing_type: string;
    status: string;
    is_featured: boolean;
    is_premium: boolean;
}

interface Props {
    modelValue: BasicFormData;
    propertyTypes: Record<string, string>;
    listingTypes: Record<string, string>;
    statusOptions: Record<string, string>;
    errors?: Record<string, string | undefined>;
}

interface Emits {
    (e: 'update:modelValue', value: BasicFormData): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

// Computed property for two-way binding
const formData = computed({
    get: () => props.modelValue,
    set: (value: BasicFormData) => emit('update:modelValue', value)
});

// Helper functions for error handling
const getFieldError = (field: string) => {
    return props.errors?.[field];
};

const hasFieldError = (field: string) => {
    return !!props.errors?.[field];
};

// Individual field computeds for better reactivity
const title = computed({
    get: () => formData.value.title,
    set: (value: string) => formData.value = { ...formData.value, title: value }
});

const description = computed({
    get: () => formData.value.description,
    set: (value: string) => formData.value = { ...formData.value, description: value }
});

const propertyType = computed({
    get: () => formData.value.property_type,
    set: (value: string) => formData.value = { ...formData.value, property_type: value }
});

const listingType = computed({
    get: () => formData.value.listing_type,
    set: (value: string) => formData.value = { ...formData.value, listing_type: value }
});

const status = computed({
    get: () => formData.value.status,
    set: (value: string) => formData.value = { ...formData.value, status: value }
});

const isFeatured = computed({
    get: () => formData.value.is_featured,
    set: (value: boolean) => formData.value = { ...formData.value, is_featured: value }
});

const isPremium = computed({
    get: () => formData.value.is_premium,
    set: (value: boolean) => formData.value = { ...formData.value, is_premium: value }
});
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Basic Information</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Enter the essential details about this property
            </p>
        </div>
        
        <div class="p-6 space-y-6">
            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Property Title *
                </label>
                <input
                    id="title"
                    v-model="title"
                    type="text"
                    :class="[
                        'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100',
                        hasFieldError('title') 
                            ? 'border-red-300 focus:border-red-500 focus:ring-red-500' 
                            : 'border-gray-300 dark:border-gray-600'
                    ]"
                    placeholder="Enter property title (e.g., Luxury Villa in Ubud with Pool)"
                    required
                />
                <p v-if="getFieldError('title')" class="mt-1 text-sm text-red-600 dark:text-red-400">
                    {{ getFieldError('title') }}
                </p>
                <p v-else class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Choose a descriptive title that highlights the property's key features
                </p>
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Description *
                </label>
                <textarea
                    id="description"
                    v-model="description"
                    rows="6"
                    :class="[
                        'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100',
                        hasFieldError('description') 
                            ? 'border-red-300 focus:border-red-500 focus:ring-red-500' 
                            : 'border-gray-300 dark:border-gray-600'
                    ]"
                    placeholder="Provide a detailed description of the property including features, location benefits, and unique selling points..."
                    required
                ></textarea>
                <p v-if="getFieldError('description')" class="mt-1 text-sm text-red-600 dark:text-red-400">
                    {{ getFieldError('description') }}
                </p>
                <div v-else class="mt-1 flex justify-between items-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Include amenities, nearby attractions, and what makes this property special
                    </p>
                    <span class="text-xs text-gray-400">
                        {{ description.length }} characters
                    </span>
                </div>
            </div>

            <!-- Property Type and Listing Type -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="property_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Property Type *
                    </label>
                    <select
                        id="property_type"
                        v-model="propertyType"
                        :class="[
                            'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100',
                            hasFieldError('property_type') 
                                ? 'border-red-300 focus:border-red-500 focus:ring-red-500' 
                                : 'border-gray-300 dark:border-gray-600'
                        ]"
                        required
                    >
                        <option value="">Select Property Type</option>
                        <option v-for="(label, value) in propertyTypes" :key="value" :value="value">
                            {{ label }}
                        </option>
                    </select>
                    <p v-if="getFieldError('property_type')" class="mt-1 text-sm text-red-600 dark:text-red-400">
                        {{ getFieldError('property_type') }}
                    </p>
                </div>

                <div>
                    <label for="listing_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Listing Type *
                    </label>
                    <select
                        id="listing_type"
                        v-model="listingType"
                        :class="[
                            'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100',
                            hasFieldError('listing_type') 
                                ? 'border-red-300 focus:border-red-500 focus:ring-red-500' 
                                : 'border-gray-300 dark:border-gray-600'
                        ]"
                        required
                    >
                        <option value="">Select Listing Type</option>
                        <option v-for="(label, value) in listingTypes" :key="value" :value="value">
                            {{ label }}
                        </option>
                    </select>
                    <p v-if="getFieldError('listing_type')" class="mt-1 text-sm text-red-600 dark:text-red-400">
                        {{ getFieldError('listing_type') }}
                    </p>
                </div>
            </div>

            <!-- Status and Property Flags -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Status *
                    </label>
                    <select
                        id="status"
                        v-model="status"
                        :class="[
                            'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100',
                            hasFieldError('status') 
                                ? 'border-red-300 focus:border-red-500 focus:ring-red-500' 
                                : 'border-gray-300 dark:border-gray-600'
                        ]"
                        required
                    >
                        <option v-for="(label, value) in statusOptions" :key="value" :value="value">
                            {{ label }}
                        </option>
                    </select>
                    <p v-if="getFieldError('status')" class="mt-1 text-sm text-red-600 dark:text-red-400">
                        {{ getFieldError('status') }}
                    </p>
                </div>

                <!-- Featured Property Flag -->
                <div class="pt-6">
                    <div class="flex items-center">
                        <input
                            id="is_featured"
                            v-model="isFeatured"
                            type="checkbox"
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
                        />
                        <label for="is_featured" class="ml-3 text-sm">
                            <span class="font-medium text-gray-700 dark:text-gray-300">Featured Property</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400">
                                Display prominently on homepage
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Premium Listing Flag -->
                <div class="pt-6">
                    <div class="flex items-center">
                        <input
                            id="is_premium"
                            v-model="isPremium"
                            type="checkbox"
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
                        />
                        <label for="is_premium" class="ml-3 text-sm">
                            <span class="font-medium text-gray-700 dark:text-gray-300">Premium Listing</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400">
                                Enhanced visibility and priority
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Property Flags Info -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">
                            Property Visibility Options
                        </h3>
                        <div class="mt-2 text-sm text-blue-700 dark:text-blue-400">
                            <ul class="list-disc list-inside space-y-1">
                                <li><strong>Featured:</strong> Appears in featured sections and gets priority placement</li>
                                <li><strong>Premium:</strong> Enhanced listing with better visibility and additional marketing features</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>