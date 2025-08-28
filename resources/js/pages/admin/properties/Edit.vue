<script setup lang="ts">
// filepath: /Users/d/Web Development/projects/RealEstate/resources/js/pages/admin/properties/Edit.vue
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Property, PropertyPricing, PropertyAttachment } from '@/types';
import { ref, computed } from 'vue';
// Update the path below to match the actual location and casing of your BasicInformation.vue file
import BasicInformation from '@/components/properties/admin/forms/BasicInformation.vue';

interface Props {
    property: Property & {
        pricing: PropertyPricing[];
        attachments: PropertyAttachment[];
    };
    propertyTypes: Record<string, string>;
    listingTypes: Record<string, string>;
    priceTypes: Record<string, string>;
    statusOptions: Record<string, string>;
}

const { property, propertyTypes, listingTypes, priceTypes, statusOptions } = defineProps<Props>();

// Get current pricing for form initialization
const currentPricing = property.pricing?.[0];

// Form setup with nested structure
const form = useForm({
    // Basic Information - as a nested object
    basic_information: {
        title: property.title,
        description: property.description,
        property_type: property.property_type,
        listing_type: property.listing_type,
        status: property.status,
        is_featured: property.is_featured,
        is_premium: property.is_premium,
    },
    
    // Location Information - as a nested object
    location: {
        street_number: property.street_number,
        street_name: property.street_name,
        village: property.village,
        district: property.district,
        regency: property.regency,
        state: property.state,
        postcode: property.postcode,
        country: property.country,
        latitude: property.latitude,
        longitude: property.longitude,
    },
    
    // Property Specifications - as a nested object
    specifications: {
        bedrooms: property.bedrooms,
        bathrooms: property.bathrooms,
        car_spaces: property.car_spaces,
        land_size: property.land_size,
        floor_area: property.floor_area,
        year_built: property.year_built,
        zoning: property.zoning,
        amenities: property.amenities || {
            schools_nearby: [],
            transport: [],
            shopping: [],
            parks: [],
            medical: []
        },
    },
    
    // Pricing Information - as a nested object
    pricing: {
        price: property.price,
        price_type: property.price_type,
        nightly_rate: currentPricing?.nightly_rate || null,
        weekly_rate: currentPricing?.weekly_rate || null,
        monthly_rate: currentPricing?.monthly_rate || null,
        available_date: property.available_date,
        inspection_times: property.inspection_times,
    },
    
    // Media Information - as a nested object
    media: {
        virtual_tour_url: property.virtual_tour_url,
        video_url: property.video_url,
        floor_plan: null as File | null,
        images: [] as File[],
    },
    
    // Agent Information - as a nested object
    agent: {
        agent_name: property.agent_name,
        agent_phone: property.agent_phone,
        agent_email: property.agent_email,
        agency_name: property.agency_name,
    },
});

// Form sections state
const activeTab = ref('basic');
const tabs = [
    { id: 'basic', name: 'Basic Info', icon: '🏠' },
    { id: 'location', name: 'Location', icon: '📍' },
    { id: 'specifications', name: 'Specifications', icon: '📐' },
    { id: 'pricing', name: 'Pricing', icon: '💰' },
    { id: 'media', name: 'Media & Images', icon: '📸' },
    { id: 'agent', name: 'Agent Info', icon: '👤' },
];

// Computed properties
const isRentalProperty = computed(() => form.basic_information.listing_type === 'for_rent');
const showRentalPricing = computed(() => isRentalProperty.value);

// Helper functions
const handleImageUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        form.media.images = Array.from(target.files);
    }
};

const handleFloorPlanUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.media.floor_plan = target.files[0];
    }
};

const addAmenityItem = (category: string) => {
    if (!form.specifications.amenities[category]) {
        form.specifications.amenities[category] = [];
    }
    form.specifications.amenities[category].push('');
};

const removeAmenityItem = (category: string, index: number) => {
    form.specifications.amenities[category].splice(index, 1);
};

const submit = () => {
    form.put(route('admin.properties.update', property.id), {
        preserveScroll: true,
        onSuccess: () => {
            // Reset image files after successful upload
            form.media.images = [];
            form.media.floor_plan = null;
        }
    });
};

// Validation helpers
const getFieldError = (field: string) => {
    return form.errors[field as keyof typeof form.errors];
};

const hasFieldError = (field: string) => {
    return !!form.errors[field as keyof typeof form.errors];
};
</script>

<template>
    <Head title="Edit Property" />

    <AppLayout>
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div>
                        <nav class="flex" aria-label="Breadcrumb">
                            <ol class="flex items-center space-x-4">
                                <li>
                                    <Link :href="route('admin.properties.index')" class="text-gray-400 hover:text-gray-500">
                                        Properties
                                    </Link>
                                </li>
                                <li>
                                    <div class="flex items-center">
                                        <svg class="flex-shrink-0 h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        <Link :href="route('admin.properties.show', property.id)" class="ml-4 text-gray-400 hover:text-gray-500">
                                            {{ property.title }}
                                        </Link>
                                    </div>
                                </li>
                                <li>
                                    <div class="flex items-center">
                                        <svg class="flex-shrink-0 h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="ml-4 text-sm font-medium text-gray-500">Edit</span>
                                    </div>
                                </li>
                            </ol>
                        </nav>
                        <h1 class="mt-2 text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate">
                            Edit Property
                        </h1>
                        <p class="mt-1 text-sm text-gray-500">
                            Property ID: {{ property.property_id }}
                        </p>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex space-x-3">
                        <Link 
                            :href="route('admin.properties.show', property.id)"
                            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors duration-200"
                        >
                            Cancel
                        </Link>
                        <button
                            @click="submit"
                            :disabled="form.processing"
                            class="bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white px-4 py-2 rounded-lg transition-colors duration-200"
                        >
                            {{ form.processing ? 'Saving...' : 'Save Changes' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <!-- Navigation Sidebar -->
                <div class="lg:col-span-1">
                    <nav class="space-y-1 sticky top-8">
                        <button
                            v-for="tab in tabs"
                            :key="tab.id"
                            @click="activeTab = tab.id"
                            :class="[
                                'w-full text-left px-4 py-3 rounded-lg transition-colors flex items-center space-x-3',
                                activeTab === tab.id 
                                    ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300 border border-blue-200 dark:border-blue-700' 
                                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                            ]"
                        >
                            <span class="text-lg">{{ tab.icon }}</span>
                            <span class="font-medium">{{ tab.name }}</span>
                        </button>
                    </nav>
                </div>

                <!-- Form Content -->
                <div class="lg:col-span-3">
                    <form @submit.prevent="submit" class="space-y-8">
                        <!-- Basic Information Tab - Using Component -->
                        <div v-show="activeTab === 'basic'">
                            <BasicInformation
                                v-model="form.basic_information"
                                :property-types="propertyTypes"
                                :listing-types="listingTypes"
                                :status-options="statusOptions"
                                :errors="form.errors"
                            />
                        </div>

                        <!-- Location Tab -->
                        <div v-show="activeTab === 'location'" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Location & Address</h2>
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
                                            v-model="form.location.street_number"
                                            type="text"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            placeholder="123"
                                        />
                                    </div>

                                    <div class="md:col-span-3">
                                        <label for="street_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Street Name *
                                        </label>
                                        <input
                                            id="street_name"
                                            v-model="form.location.street_name"
                                            type="text"
                                            :class="[
                                                'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500',
                                                hasFieldError('location.street_name') ? 'border-red-300' : ''
                                            ]"
                                            placeholder="Jalan Raya Ubud"
                                        />
                                        <p v-if="getFieldError('location.street_name')" class="mt-1 text-sm text-red-600">{{ getFieldError('location.street_name') }}</p>
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
                                            v-model="form.location.village"
                                            type="text"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            placeholder="Ubud"
                                        />
                                    </div>

                                    <div>
                                        <label for="district" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            District (Kecamatan) *
                                        </label>
                                        <input
                                            id="district"
                                            v-model="form.location.district"
                                            type="text"
                                            :class="[
                                                'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500',
                                                hasFieldError('location.district') ? 'border-red-300' : ''
                                            ]"
                                            placeholder="Ubud"
                                        />
                                        <p v-if="getFieldError('location.district')" class="mt-1 text-sm text-red-600">{{ getFieldError('location.district') }}</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label for="regency" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Regency (Kabupaten) *
                                        </label>
                                        <input
                                            id="regency"
                                            v-model="form.location.regency"
                                            type="text"
                                            :class="[
                                                'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500',
                                                hasFieldError('location.regency') ? 'border-red-300' : ''
                                            ]"
                                            placeholder="Gianyar"
                                        />
                                        <p v-if="getFieldError('location.regency')" class="mt-1 text-sm text-red-600">{{ getFieldError('location.regency') }}</p>
                                    </div>

                                    <div>
                                        <label for="state" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            State (Provinsi)
                                        </label>
                                        <input
                                            id="state"
                                            v-model="form.location.state"
                                            type="text"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            placeholder="Bali"
                                        />
                                    </div>

                                    <div>
                                        <label for="postcode" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Postcode *
                                        </label>
                                        <input
                                            id="postcode"
                                            v-model="form.location.postcode"
                                            type="text"
                                            :class="[
                                                'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500',
                                                hasFieldError('location.postcode') ? 'border-red-300' : ''
                                            ]"
                                            placeholder="80571"
                                        />
                                        <p v-if="getFieldError('location.postcode')" class="mt-1 text-sm text-red-600">{{ getFieldError('location.postcode') }}</p>
                                    </div>
                                </div>

                                <!-- GPS Coordinates -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="latitude" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Latitude
                                        </label>
                                        <input
                                            id="latitude"
                                            v-model="form.location.latitude"
                                            type="number"
                                            step="any"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            placeholder="-8.5068"
                                        />
                                    </div>

                                    <div>
                                        <label for="longitude" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Longitude
                                        </label>
                                        <input
                                            id="longitude"
                                            v-model="form.location.longitude"
                                            type="number"
                                            step="any"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            placeholder="115.2625"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Specifications Tab -->
                        <div v-show="activeTab === 'specifications'" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Property Specifications</h2>
                            </div>
                            <div class="p-6 space-y-6">
                                <!-- Basic Specs -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label for="bedrooms" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Bedrooms
                                        </label>
                                        <input
                                            id="bedrooms"
                                            v-model.number="form.specifications.bedrooms"
                                            type="number"
                                            min="0"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        />
                                    </div>

                                    <div>
                                        <label for="bathrooms" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Bathrooms
                                        </label>
                                        <input
                                            id="bathrooms"
                                            v-model.number="form.specifications.bathrooms"
                                            type="number"
                                            min="0"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        />
                                    </div>

                                    <div>
                                        <label for="car_spaces" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Car Spaces
                                        </label>
                                        <input
                                            id="car_spaces"
                                            v-model.number="form.specifications.car_spaces"
                                            type="number"
                                            min="0"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        />
                                    </div>
                                </div>

                                <!-- Size Specifications -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="land_size" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Land Size (m²)
                                        </label>
                                        <input
                                            id="land_size"
                                            v-model.number="form.specifications.land_size"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        />
                                    </div>

                                    <div>
                                        <label for="floor_area" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Floor Area (m²)
                                        </label>
                                        <input
                                            id="floor_area"
                                            v-model.number="form.specifications.floor_area"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        />
                                    </div>
                                </div>

                                <!-- Additional Details -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="year_built" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Year Built
                                        </label>
                                        <input
                                            id="year_built"
                                            v-model.number="form.specifications.year_built"
                                            type="number"
                                            min="1800"
                                            :max="new Date().getFullYear()"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        />
                                    </div>

                                    <div>
                                        <label for="zoning" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Zoning
                                        </label>
                                        <input
                                            id="zoning"
                                            v-model="form.specifications.zoning"
                                            type="text"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            placeholder="Residential, Commercial, etc."
                                        />
                                    </div>
                                </div>

                                <!-- Amenities Section -->
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Nearby Amenities</h3>
                                    <div class="space-y-4">
                                        <div v-for="(category, categoryKey) in { 
                                            schools_nearby: 'Schools', 
                                            transport: 'Transport', 
                                            shopping: 'Shopping', 
                                            parks: 'Parks & Recreation', 
                                            medical: 'Medical Facilities' 
                                        }" :key="categoryKey">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                {{ category }}
                                            </label>
                                            <div class="space-y-2">
                                                <div v-for="(item, index) in form.specifications.amenities[categoryKey]" :key="index" class="flex gap-2">
                                                    <input
                                                        v-model="form.specifications.amenities[categoryKey][index]"
                                                        type="text"
                                                        class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                        :placeholder="`Add ${category.toLowerCase()} information`"
                                                    />
                                                    <button
                                                        type="button"
                                                        @click="removeAmenityItem(categoryKey, index)"
                                                        class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600"
                                                    >
                                                        Remove
                                                    </button>
                                                </div>
                                                <button
                                                    type="button"
                                                    @click="addAmenityItem(categoryKey)"
                                                    class="px-3 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 text-sm"
                                                >
                                                    Add {{ category }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pricing Tab -->
                        <div v-show="activeTab === 'pricing'" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Pricing Information</h2>
                            </div>
                            <div class="p-6 space-y-6">
                                <!-- Sale Price (for sale properties) -->
                                <div v-if="!isRentalProperty" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Price (IDR)
                                        </label>
                                        <input
                                            id="price"
                                            v-model.number="form.pricing.price"
                                            type="number"
                                            min="0"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            placeholder="500000000"
                                        />
                                    </div>

                                    <div>
                                        <label for="price_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Price Type
                                        </label>
                                        <select
                                            id="price_type"
                                            v-model="form.pricing.price_type"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        >
                                            <option v-for="(label, value) in priceTypes" :key="value" :value="value">
                                                {{ label }}
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Rental Pricing (for rental properties) -->
                                <div v-if="showRentalPricing">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Rental Rates</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        <div>
                                            <label for="nightly_rate" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Nightly Rate (IDR)
                                            </label>
                                            <input
                                                id="nightly_rate"
                                                v-model.number="form.pricing.nightly_rate"
                                                type="number"
                                                min="0"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                placeholder="500000"
                                            />
                                        </div>

                                        <div>
                                            <label for="weekly_rate" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Weekly Rate (IDR)
                                            </label>
                                            <input
                                                id="weekly_rate"
                                                v-model.number="form.pricing.weekly_rate"
                                                type="number"
                                                min="0"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                placeholder="3000000"
                                            />
                                        </div>

                                        <div>
                                            <label for="monthly_rate" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Monthly Rate (IDR)
                                            </label>
                                            <input
                                                id="monthly_rate"
                                                v-model.number="form.pricing.monthly_rate"
                                                type="number"
                                                min="0"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                placeholder="10000000"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <!-- Additional Pricing Info -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="available_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Available Date
                                        </label>
                                        <input
                                            id="available_date"
                                            v-model="form.pricing.available_date"
                                            type="date"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        />
                                    </div>

                                    <div>
                                        <label for="inspection_times" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Inspection Times
                                        </label>
                                        <textarea
                                            id="inspection_times"
                                            v-model="form.pricing.inspection_times"
                                            rows="3"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            placeholder="Saturday 10am-11am, Sunday 2pm-3pm"
                                        ></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Media & Images Tab -->
                        <div v-show="activeTab === 'media'" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Media & Images</h2>
                            </div>
                            <div class="p-6 space-y-6">
                                <!-- Existing Images -->
                                <div v-if="property.attachments && property.attachments.length > 0">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Existing Images</h3>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                                        <div 
                                            v-for="attachment in property.attachments" 
                                            :key="attachment.id"
                                            class="relative aspect-square bg-gray-200 dark:bg-gray-700 rounded-lg overflow-hidden"
                                        >
                                            <img 
                                                :src="attachment.path" 
                                                :alt="attachment.title"
                                                class="w-full h-full object-cover"
                                            />
                                            <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-50 transition-all duration-200 flex items-center justify-center">
                                                <button
                                                    type="button"
                                                    class="opacity-0 hover:opacity-100 bg-red-500 text-white p-2 rounded-full transition-opacity"
                                                    title="Delete Image"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Add New Images -->
                                <div>
                                    <label for="images" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Add New Images
                                    </label>
                                    <input
                                        id="images"
                                        type="file"
                                        multiple
                                        accept="image/*"
                                        @change="handleImageUpload"
                                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                    />
                                    <p class="mt-1 text-sm text-gray-500">Upload up to 20 images (max 5MB each)</p>
                                </div>

                                <!-- Floor Plan -->
                                <div>
                                    <label for="floor_plan" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Floor Plan
                                    </label>
                                    <input
                                        id="floor_plan"
                                        type="file"
                                        accept=".pdf,.jpg,.jpeg,.png"
                                        @change="handleFloorPlanUpload"
                                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                    />
                                    <p v-if="property.floor_plan" class="mt-1 text-sm text-gray-500">
                                        Current: {{ property.floor_plan.split('/').pop() }}
                                    </p>
                                </div>

                                <!-- Virtual Tour and Video URLs -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="virtual_tour_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Virtual Tour URL
                                        </label>
                                        <input
                                            id="virtual_tour_url"
                                            v-model="form.media.virtual_tour_url"
                                            type="url"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            placeholder="https://..."
                                        />
                                    </div>

                                    <div>
                                        <label for="video_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Video URL
                                        </label>
                                        <input
                                            id="video_url"
                                            v-model="form.media.video_url"
                                            type="url"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            placeholder="https://youtube.com/..."
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Agent Information Tab -->
                        <div v-show="activeTab === 'agent'" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Agent Information</h2>
                            </div>
                            <div class="p-6 space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="agent_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Agent Name *
                                        </label>
                                        <input
                                            id="agent_name"
                                            v-model="form.agent.agent_name"
                                            type="text"
                                            :class="[
                                                'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500',
                                                hasFieldError('agent.agent_name') ? 'border-red-300' : ''
                                            ]"
                                            placeholder="John Smith"
                                        />
                                        <p v-if="getFieldError('agent.agent_name')" class="mt-1 text-sm text-red-600">{{ getFieldError('agent.agent_name') }}</p>
                                    </div>

                                    <div>
                                        <label for="agency_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Agency Name
                                        </label>
                                        <input
                                            id="agency_name"
                                            v-model="form.agent.agency_name"
                                            type="text"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            placeholder="Bali Real Estate Co."
                                        />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="agent_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Agent Phone
                                        </label>
                                        <input
                                            id="agent_phone"
                                            v-model="form.agent.agent_phone"
                                            type="tel"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            placeholder="+62 812 3456 7890"
                                        />
                                    </div>

                                    <div>
                                        <label for="agent_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Agent Email
                                        </label>
                                        <input
                                            id="agent_email"
                                            v-model="form.agent.agent_email"
                                            type="email"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            placeholder="agent@example.com"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>