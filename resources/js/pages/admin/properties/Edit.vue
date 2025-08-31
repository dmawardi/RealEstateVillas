<script setup lang="ts">
// filepath: /Users/d/Web Development/projects/RealEstate/resources/js/pages/admin/properties/Edit.vue
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Property, PropertyPricing, PropertyAttachment } from '@/types';
import { ref } from 'vue';
// Update the path below to match the actual location and casing of your BasicInformation.vue file
import BasicInformation from '@/components/properties/admin/forms/BasicInformation.vue';
import Location from '@/components/properties/admin/forms/Location.vue';
import Specifications from '@/components/properties/admin/forms/Specifications.vue';
import Pricing from '@/components/ui/form/Pricing.vue';

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
        street_number: property.street_number ?? null,
        street_name: property.street_name ?? null,
        village: property.village ?? null,
        district: property.district ?? null,
        regency: property.regency ?? null,
        state: property.state ?? null,
        postcode: property.postcode ?? null,
        country: property.country ?? null,
        latitude: property.latitude ?? null,
        longitude: property.longitude ?? null,
    },
    
    // Property Specifications - as a nested object
    specifications: {
        bedrooms: property.bedrooms ?? null,
        bathrooms: property.bathrooms ?? null,
        car_spaces: property.car_spaces ?? null,
        land_size: property.land_size ?? null,
        floor_area: property.floor_area ?? null,
        year_built: property.year_built ?? null,
        zoning: property.zoning ?? null,
        amenities: {
            schools_nearby: property.amenities?.schools_nearby ?? [],
            transport: property.amenities?.transport ?? [],
            shopping: property.amenities?.shopping ?? [],
            parks: property.amenities?.parks ?? [],
            medical: property.amenities?.medical ?? []
        },
    },
    
    // Pricing Information - as a nested object
    pricing: {
        price: property.price || null,
        price_type: property.price_type || null,
        nightly_rate: currentPricing?.nightly_rate || null,
        weekly_rate: currentPricing?.weekly_rate || null,
        monthly_rate: currentPricing?.monthly_rate || null,
        available_date: property.available_date ?? null,
        inspection_times: property.inspection_times ?? null,
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

// Helper functions
function handleImageUpload(event: Event) {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        form.media.images = Array.from(target.files);
    }
}

const handleFloorPlanUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.media.floor_plan = target.files[0];
    }
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
                        <div v-show="activeTab === 'location'">
                            <Location v-model="form.location" :errors="form.errors" />
                        </div>

                        <!-- Specifications Tab -->
                        <div v-show="activeTab === 'specifications'">
                            <Specifications
                                v-model="form.specifications"
                                :errors="form.errors"
                            />
                        </div>

                        <!-- Pricing Tab -->
                        <div v-show="activeTab === 'pricing'">
                            <Pricing
                                v-model="form.pricing"
                                :price-types="priceTypes"
                                :listing-type="form.basic_information.listing_type"
                                :errors="form.errors"
                            />
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