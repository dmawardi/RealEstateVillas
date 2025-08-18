<script setup lang="ts">
import BaseLayout from '@/layouts/BaseLayout.vue';
import type { BreadcrumbItemType, PaginatedProperties, PropertyFilters } from '@/types';
// import PropertyFilterCard from '@/components/properties/PropertyFilterCard.vue';
import { Head, Link } from '@inertiajs/vue3';
import PropertyCard from '@/components/properties/PropertyCard.vue';
import CompactSearchHeader from '@/components/ui/navigation/CompactSearchHeader.vue';

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

// Pagination Link Rendering Functions
// Function to decode HTML entities
const decodeHtmlEntities = (text: string): string => {
    const textarea = document.createElement('textarea');
    textarea.innerHTML = text;
    return textarea.value;
};

// Function to clean pagination labels
const getCleanLabel = (label: string): string => {
    // First decode HTML entities, then clean up
    const decoded = decodeHtmlEntities(label);
    
    // Replace common pagination text
    return decoded
        .replace(/« Previous/g, '← Previous')
        .replace(/Next »/g, 'Next →')
        .replace(/«/g, '←')
        .replace(/»/g, '→')
        .replace(/…/g, '...');
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
            <CompactSearchHeader :initialFilters="filters" />
            <!-- <PropertyFilterCard :filters="filters" /> -->

            <!-- Properties Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <PropertyCard 
                    v-for="property in properties.data" 
                    :key="property.id" 
                    :property="property"
                />
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
                    {{ getCleanLabel(link.label) }}
                </Link>
            </div>
        </div>
    </BaseLayout>
</template>