<script setup lang="ts">
import BaseLayout from '@/layouts/BaseLayout.vue';
import type { BreadcrumbItemType, PaginatedProperties, Property, PropertyFilters } from '@/types';
import PropertyFilterCard from '@/components/properties/PropertyFilterCard.vue';
import { Head, Link } from '@inertiajs/vue3';

interface Props {
    properties: PaginatedProperties;
    filters?: PropertyFilters;
}

const { properties, filters } = defineProps<Props>();

// Generate breadcrumbs
const breadcrumbs: BreadcrumbItemType[] = [
    {
        title: 'Properties',
        href: '/properties',
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
const getPriceDisplay = (property: Property) => {
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

// Helper function to truncate description
const truncateDescription = (text: string, length: number = 150): string => {
    if (text.length <= length) return text;
    return text.substring(0, length) + '...';
};
</script>

<template>
    <Head title="Properties - Browse All Listings" />
    
    <BaseLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                            Properties
                        </h1>
                        <p class="text-lg text-gray-600 dark:text-gray-400 mt-2">
                            Discover your dream property in Bali
                        </p>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            {{ properties.total }} Properties Found
                        </div>
                        <div class="text-sm text-gray-500">
                            Showing {{ properties.from }}-{{ properties.to }} of {{ properties.total }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Section -->
            <PropertyFilterCard :filters="filters" />

            <!-- Properties Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <div 
                    v-for="property in properties.data" 
                    :key="property.id"
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow"
                >
                    <!-- Property Image -->
                    <div class="aspect-video bg-gray-200 dark:bg-gray-700 relative">
                        <div v-if="property.attachments && property.attachments.length > 0" class="w-full h-full">
                            <img 
                                :src="property.attachments[0].path" 
                                :alt="property.title"
                                class="w-full h-full object-cover"
                            />
                        </div>
                        <div v-else class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400">
                            <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        
                        <!-- Property Type Badge -->
                        <div class="absolute top-3 left-3">
                            <span class="bg-blue-600 text-white px-2 py-1 rounded text-xs font-medium capitalize">
                                {{ formatPropertyType(property.property_type) }}
                            </span>
                        </div>
                        
                        <!-- Listing Type Badge -->
                        <div class="absolute top-3 right-3">
                            <span class="bg-green-600 text-white px-2 py-1 rounded text-xs font-medium capitalize">
                                {{ formatPropertyType(property.listing_type) }}
                            </span>
                        </div>
                    </div>

                    <!-- Property Content -->
                    <div class="p-6">
                        <!-- Price -->
                        <div class="mb-3">
                            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                {{ getPriceDisplay(property) }}
                            </div>
                        </div>

                        <!-- Title -->
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                            <Link 
                                :href="`/properties/${property.id}`"
                                class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                            >
                                {{ property.title }}
                            </Link>
                        </h3>

                        <!-- Location -->
                        <p class="text-gray-600 dark:text-gray-400 mb-3">
                            {{ property.street_name }}, {{ property.village }}, {{ property.district }}
                        </p>

                        <!-- Description -->
                        <p class="text-gray-700 dark:text-gray-300 text-sm mb-4 leading-relaxed">
                            {{ truncateDescription(property.description) }}
                        </p>

                        <!-- Property Stats -->
                        <div class="grid grid-cols-3 gap-4 mb-4">
                            <div v-if="property.bedrooms" class="text-center">
                                <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ property.bedrooms }}</div>
                                <div class="text-xs text-gray-600 dark:text-gray-400">Bedrooms</div>
                            </div>
                            <div v-if="property.bathrooms" class="text-center">
                                <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ property.bathrooms }}</div>
                                <div class="text-xs text-gray-600 dark:text-gray-400">Bathrooms</div>
                            </div>
                            <div v-if="property.land_size" class="text-center">
                                <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ property.land_size }}</div>
                                <div class="text-xs text-gray-600 dark:text-gray-400">m²</div>
                            </div>
                        </div>

                        <!-- Features Preview -->
                        <div v-if="property.features && property.features.length > 0" class="mb-4">
                            <div class="flex flex-wrap gap-1">
                                <span 
                                    v-for="feature in property.features.slice(0, 3)" 
                                    :key="feature.id"
                                    class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-2 py-1 rounded text-xs"
                                >
                                    {{ feature.name }}
                                </span>
                                <span 
                                    v-if="property.features.length > 3"
                                    class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-2 py-1 rounded text-xs"
                                >
                                    +{{ property.features.length - 3 }} more
                                </span>
                            </div>
                        </div>

                        <!-- Agent Info -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ property.agent_name }}
                                    </div>
                                    <div v-if="property.agency_name" class="text-xs text-gray-600 dark:text-gray-400">
                                        {{ property.agency_name }}
                                    </div>
                                </div>
                                <Link 
                                    :href="`/properties/${property.id}`"
                                    class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700 transition-colors"
                                >
                                    View Details
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="properties.last_page > 1" class="flex items-center justify-center space-x-2">
                <Link 
                    v-for="link in properties.links" 
                    :key="link.label"
                    :href="link.url || '#'"
                    :class="[
                        'px-3 py-2 rounded text-sm',
                        link.active 
                            ? 'bg-blue-600 text-white' 
                            : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700',
                        !link.url ? 'opacity-50 cursor-not-allowed' : ''
                    ]" 
                    :preserve-scroll="true"
                    >
                    {{ link.label }}
                </Link>
            </div>
        </div>
    </BaseLayout>
</template>