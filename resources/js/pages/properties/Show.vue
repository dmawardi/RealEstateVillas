<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import BaseLayout from '@/layouts/BaseLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import { Head } from '@inertiajs/vue3';

// Define the props interface for the property data
interface Property {
    id: number;
    title: string;
    description: string;
    property_type: string;
    listing_type: string;
    status: string;
    price?: number;
    rental_price_monthly?: number;
    rental_price_weekly?: number;
    street_number?: string;
    street_name: string;
    village?: string;
    district: string;
    regency: string;
    state: string;
    postcode: string;
    country: string;
    bedrooms?: number;
    bathrooms?: number;
    car_spaces?: number;
    land_size?: number;
    floor_area?: number;
    year_built?: number;
    attachments?: 
    {
        id: number;
        path: string;
        original_filename: string;
        file_type: string;
        file_size: number;
        type: string; // e.g., 'image', 'document'
        caption?: string;
        is_visible_to_customer: boolean;
        is_active: boolean;
    }[];
    amenities?: Record<string, any>;
    features?: Feature[];
    user: {
        id: number;
        name: string;
        email: string;
    };
    agent_name: string;
    agent_phone?: string;
    agent_email?: string;
    agency_name?: string;
    view_count: number;
    created_at: string;
    updated_at: string;
}

interface Feature {
    id: number;
    name: string;
    slug: string;
    category: string;
    pivot: {
        quantity: number;
        notes?: string;
    };
}

interface Props {
    property: Property;
}

const { property } = defineProps<Props>();

// Generate breadcrumbs with actual property data
const breadcrumbs: BreadcrumbItemType[] = [
    {
        title: 'Properties',
        href: '/properties',
    },
    {
        title: property.title,
        href: `/properties/${property.id}`,
    },
];

// Helper function to format price
const formatPrice = (price: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(price);
};

// Helper function to format property type
const formatPropertyType = (type: string): string => {
    return type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
};

// Helper function to get price display
const getPriceDisplay = () => {
    if (property.listing_type === 'for_rent') {
        if (property.rental_price_monthly) {
            return `${formatPrice(property.rental_price_monthly)}/month`;
        }
        if (property.rental_price_weekly) {
            return `${formatPrice(property.rental_price_weekly)}/week`;
        }
    }
    if (property.price) {
        return formatPrice(property.price);
    }
    return 'Price on Application';
};
</script>

<template>
    <Head :title="`${property.title} - Property Details`" />
    <BaseLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Property Header -->
            <div class="mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                            {{ property.title }}
                        </h1>
                        <p class="text-lg text-gray-600 dark:text-gray-400 mt-2">
                            {{ property.street_name }}, {{ property.village }}, {{ property.district }}
                        </p>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                            {{ getPriceDisplay() }}
                        </div>
                        <div class="text-sm text-gray-500 capitalize">
                            {{ formatPropertyType(property.listing_type) }}
                        </div>
                    </div>
                </div>
            </div>
    
            <!-- Property Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Property Images -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                        <div class="aspect-video bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                            <div v-if="property.attachments && property.attachments.length > 0" class="w-full h-full">
                                <img 
                                    :src="property.attachments[0].path" 
                                    :alt="property.title"
                                    class="w-full h-full object-cover"
                                />
                            </div>
                            <div v-else class="text-gray-500 dark:text-gray-400">
                                <svg class="w-16 h-16 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
                                </svg>
                                <p>No images available</p>
                            </div>
                        </div>
                    </div>
    
                    <!-- Property Details -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">
                            Property Details
                        </h2>
                        
                        <!-- Key Stats -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                            <div v-if="property.bedrooms" class="text-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ property.bedrooms }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Bedrooms</div>
                            </div>
                            <div v-if="property.bathrooms" class="text-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ property.bathrooms }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Bathrooms</div>
                            </div>
                            <div v-if="property.car_spaces" class="text-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ property.car_spaces }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Car Spaces</div>
                            </div>
                            <div v-if="property.land_size" class="text-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ property.land_size }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Land Size (m²)</div>
                            </div>
                        </div>
    
                        <!-- Description -->
                        <div>
                            <h3 class="text-lg font-medium mb-3 text-gray-900 dark:text-gray-100">Description</h3>
                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                                {{ property.description }}
                            </p>
                        </div>
                    </div>
    
                    <!-- Features -->
                    <div v-if="property.features && property.features.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">
                            Features
                        </h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div 
                                v-for="feature in property.features" 
                                :key="feature.id"
                                class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
                            >
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ feature.name }}
                                        <span v-if="feature.pivot.quantity > 1" class="text-sm text-gray-500">
                                            ({{ feature.pivot.quantity }})
                                        </span>
                                    </div>
                                    <div v-if="feature.pivot.notes" class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ feature.pivot.notes }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
    
                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Agent Contact -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">
                            Contact Agent
                        </h3>
                        <div class="space-y-3">
                            <div>
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ property.agent_name }}</div>
                                <div v-if="property.agency_name" class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ property.agency_name }}
                                </div>
                            </div>
                            <div v-if="property.agent_phone">
                                <a :href="`tel:${property.agent_phone}`" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                    {{ property.agent_phone }}
                                </a>
                            </div>
                            <div v-if="property.agent_email">
                                <a :href="`mailto:${property.agent_email}`" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                    {{ property.agent_email }}
                                </a>
                            </div>
                            <button class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                                Send Enquiry
                            </button>
                        </div>
                    </div>
    
                    <!-- Property Info -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">
                            Property Information
                        </h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Type:</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100 capitalize">
                                    {{ formatPropertyType(property.property_type) }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Status:</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100 capitalize">
                                    {{ property.status }}
                                </span>
                            </div>
                            <div v-if="property.year_built" class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Year Built:</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ property.year_built }}</span>
                            </div>
                            <div v-if="property.floor_area" class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Floor Area:</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ property.floor_area }} m²</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Views:</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ property.view_count }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>