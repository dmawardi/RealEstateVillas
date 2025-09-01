<script setup lang="ts">
// filepath: /Users/d/Web Development/projects/RealEstate/resources/js/pages/admin/properties/Edit.vue
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Property, PropertyPricing, PropertyAttachment } from '@/types';
import { ref, computed } from 'vue';
import BasicInformation from '@/components/properties/admin/forms/BasicInformation.vue';
import Location from '@/components/properties/admin/forms/Location.vue';
import Specifications from '@/components/properties/admin/forms/Specifications.vue';
import Pricing from '@/components/properties/admin/forms/Pricing.vue';
import MediaAttachments from '@/components/properties/admin/forms/MediaAttachments.vue';
import Agent from '@/components/properties/admin/forms/Agent.vue';
import { api } from '@/services/api';

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

// Form setup with FLATTENED structure to match controller expectations
const form = useForm({
    // Basic Information - flattened
    title: property.title,
    description: property.description,
    property_type: property.property_type,
    listing_type: property.listing_type,
    status: property.status,
    is_featured: property.is_featured,
    is_premium: property.is_premium,
    
    // Location - flattened
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
    
    // Specifications - flattened
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
    
    // Pricing - flattened
    price: property.price || null,
    price_type: property.price_type || null,
    nightly_rate: currentPricing?.nightly_rate || null,
    weekly_rate: currentPricing?.weekly_rate || null,
    monthly_rate: currentPricing?.monthly_rate || null,
    available_date: property.available_date ?? null,
    inspection_times: property.inspection_times ?? null,
    
    // Media - flattened
    virtual_tour_url: property.virtual_tour_url ?? null,
    video_url: property.video_url ?? null,
    floor_plan: null as File | null,
    images: [] as File[],
    
    // Agent - flattened
    agent_name: property.agent_name ?? null,
    agent_phone: property.agent_phone ?? null,
    agent_email: property.agent_email ?? null,
    agency_name: property.agency_name ?? null,
});

// Create computed properties for component v-models (to maintain nested interface)
const basicInformation = computed({
    get: () => ({
        title: form.title,
        description: form.description,
        property_type: form.property_type,
        listing_type: form.listing_type,
        status: form.status,
        is_featured: form.is_featured,
        is_premium: form.is_premium,
    }),
    set: (value) => {
        form.title = value.title;
        form.description = value.description;
        form.property_type = value.property_type;
        form.listing_type = value.listing_type;
        form.status = value.status;
        form.is_featured = value.is_featured;
        form.is_premium = value.is_premium;
    }
});

const location = computed({
    get: () => ({
        street_number: form.street_number,
        street_name: form.street_name,
        village: form.village,
        district: form.district,
        regency: form.regency,
        state: form.state,
        postcode: form.postcode,
        country: form.country,
        latitude: form.latitude,
        longitude: form.longitude,
    }),
    set: (value) => {
        form.street_number = value.street_number;
        form.street_name = value.street_name;
        form.village = value.village;
        form.district = value.district;
        form.regency = value.regency;
        form.state = value.state;
        form.postcode = value.postcode;
        form.country = value.country;
        form.latitude = value.latitude;
        form.longitude = value.longitude;
    }
});

const specifications = computed({
    get: () => ({
        bedrooms: form.bedrooms,
        bathrooms: form.bathrooms,
        car_spaces: form.car_spaces,
        land_size: form.land_size,
        floor_area: form.floor_area,
        year_built: form.year_built,
        zoning: form.zoning,
        amenities: form.amenities,
    }),
    set: (value) => {
        form.bedrooms = value.bedrooms;
        form.bathrooms = value.bathrooms;
        form.car_spaces = value.car_spaces;
        form.land_size = value.land_size;
        form.floor_area = value.floor_area;
        form.year_built = value.year_built;
        form.zoning = value.zoning;
        form.amenities = value.amenities;
    }
});

const pricing = computed({
    get: () => ({
        price: form.price,
        price_type: form.price_type,
        nightly_rate: form.nightly_rate,
        weekly_rate: form.weekly_rate,
        monthly_rate: form.monthly_rate,
        available_date: form.available_date,
        inspection_times: form.inspection_times,
    }),
    set: (value) => {
        form.price = value.price;
        form.price_type = value.price_type;
        form.nightly_rate = value.nightly_rate;
        form.weekly_rate = value.weekly_rate;
        form.monthly_rate = value.monthly_rate;
        form.available_date = value.available_date;
        form.inspection_times = value.inspection_times;
    }
});

const media = computed({
    get: () => ({
        virtual_tour_url: form.virtual_tour_url,
        video_url: form.video_url,
        floor_plan: form.floor_plan,
        images: form.images,
    }),
    set: (value) => {
        form.virtual_tour_url = value.virtual_tour_url;
        form.video_url = value.video_url;
        form.floor_plan = value.floor_plan;
        form.images = value.images;
    }
});

const agent = computed({
    get: () => ({
        agent_name: form.agent_name,
        agent_phone: form.agent_phone,
        agent_email: form.agent_email,
        agency_name: form.agency_name,
    }),
    set: (value) => {
        form.agent_name = value.agent_name;
        form.agent_phone = value.agent_phone;
        form.agent_email = value.agent_email;
        form.agency_name = value.agency_name;
    }
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

// Submit function using form.put() - the correct Inertia approach
const submit = () => {
    console.log("Form data being sent:", form.data());

    // Use form.put() for web routes - Inertia handles everything
    form.put(route('admin.properties.update', property.id), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            // Reset file fields after successful upload
            form.images = [];
            form.floor_plan = null;
            console.log('Property updated successfully');
        },
        onError: (errors) => {
            console.error('Validation errors:', errors);
            // Errors are automatically handled by Inertia and mapped to form.errors
        },
        onFinish: () => {
            console.log('Request completed');
        }
    });
};

const handleDeleteAttachment = async (attachmentId: number) => {
    try {
        // Use the API service to delete the attachment
        const response = await api.properties.deleteAttachment(attachmentId);

        console.log('Attachment deleted successfully:', response);

        // Option 1: Refresh the current page to get updated data
        router.reload({ only: ['property'] });
        
    } catch (error) {
        console.error('Failed to delete attachment:', error);
        // Handle error appropriately - show error message to user
    }
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
                        <!-- Basic Information Tab -->
                        <div v-show="activeTab === 'basic'">
                            <BasicInformation
                                v-model="basicInformation"
                                :property-types="propertyTypes"
                                :listing-types="listingTypes"
                                :status-options="statusOptions"
                                :errors="form.errors"
                            />
                        </div>

                        <!-- Location Tab -->
                        <div v-show="activeTab === 'location'">
                            <Location 
                                v-model="location" 
                                :errors="form.errors" 
                            />
                        </div>

                        <!-- Specifications Tab -->
                        <div v-show="activeTab === 'specifications'">
                            <Specifications
                                v-model="specifications"
                                :errors="form.errors"
                            />
                        </div>

                        <!-- Pricing Tab -->
                        <div v-show="activeTab === 'pricing'">
                            <Pricing
                                v-model="pricing"
                                :price-types="priceTypes"
                                :listing-type="form.listing_type"
                                :errors="form.errors"
                            />
                        </div>

                        <!-- Media & Images Tab -->
                        <div v-show="activeTab === 'media'">
                            <MediaAttachments
                                v-model="media"
                                :existing-attachments="property.attachments"
                                :existing-floor-plan="property.floor_plan"
                                :errors="form.errors"
                                @delete-attachment="handleDeleteAttachment"
                            />
                        </div>

                        <!-- Agent Information Tab -->
                        <div v-show="activeTab === 'agent'">
                            <Agent
                                v-model="agent"
                                :errors="form.errors"
                            />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>