<script setup lang="ts">
import BaseLayout from '@/layouts/BaseLayout.vue';
import type { PaginatedProperties, PropertyFilters, SEO } from '@/types';
// import PropertyFilterCard from '@/components/properties/PropertyFilterCard.vue';
import { Link } from '@inertiajs/vue3';
import PropertyCard from '@/components/properties/PropertyCard.vue';
import CompactSearchHeader from '@/components/ui/navigation/CompactSearchHeader.vue';
import SEOHead from '@/components/SEOHead.vue';
import Footer from '@/components/Footer.vue';

interface Props {
    properties: PaginatedProperties;
    filters?: PropertyFilters;
    seoData?: SEO;
    businessPhone?: string;
    businessEmail?: string;
}

const { properties, filters, seoData, businessPhone, businessEmail } = defineProps<Props>();

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
    <SEOHead v-if="seoData" :seoData="seoData" />
    
    <BaseLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">
                            Properties
                        </h1>
                        <p class="text-lg text-gray-600 mt-2">
                            Discover your dream property in Bali
                        </p>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-medium text-gray-900">
                            {{ properties.total }} Properties Found
                        </div>
                        <div class="text-sm text-gray-500">
                            Showing {{ properties.from }}-{{ properties.to }} of {{ properties.total }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Section -->
            <CompactSearchHeader :initialFilters="filters" routeURL="/properties" :useTextSearch="false" />

            <!-- Properties Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8 mt-6">
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
                        'px-4 py-2 font-body font-medium rounded-lg border transition-all duration-300 transform hover:-translate-y-0.5',
                        link.active 
                            ? 'bg-accent text-white border-accent shadow-md' 
                            : 'bg-white text-primary border-secondary/30 hover:bg-secondary/10 hover:border-accent/50 shadow-sm hover:shadow-md',
                        !link.url ? 'opacity-50 cursor-not-allowed pointer-events-none transform-none' : ''
                    ]" 
                    :preserve-scroll="true"
                    >
                    {{ getCleanLabel(link.label) }}
                </Link>
            </div>
        </div>
        <Footer :businessPhone="businessPhone" :businessEmail="businessEmail" />
    </BaseLayout>
</template>