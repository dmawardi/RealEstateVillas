<script setup lang="ts">
import { ref } from 'vue';
import BaseLayout from '@/layouts/BaseLayout.vue';
import type { PaginatedProperties, PropertyFilters, SEO } from '@/types';
// import PropertyFilterCard from '@/components/properties/PropertyFilterCard.vue';
import { Link } from '@inertiajs/vue3';
import PropertyCard from '@/components/properties/PropertyCard.vue';
import PropertyMap from '@/components/properties/PropertyMap.vue';
import CompactSearchHeader from '@/components/ui/navigation/CompactSearchHeader.vue';
import SEOHead from '@/components/SEOHead.vue';
import Footer from '@/components/Footer.vue';

interface Props {
    properties: PaginatedProperties;
    filters?: PropertyFilters;
    seoData?: SEO;
    businessPhone?: string;
    businessEmail?: string;
    googleMapsIds?: string;
    googleMapsApiKey?: string;
}

const { properties, filters, seoData, businessPhone, businessEmail, googleMapsIds, googleMapsApiKey } = defineProps<Props>();

// View mode state
const viewMode = ref<'list' | 'map'>('list');

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
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                        <!-- View Toggle -->
                        <div class="flex bg-gray-100 rounded-lg p-1">
                            <button 
                                @click="viewMode = 'list'"
                                :class="[
                                    'flex items-center px-3 py-2 rounded-md text-sm font-medium transition-all',
                                    viewMode === 'list' 
                                        ? 'bg-white text-gray-900 shadow-sm' 
                                        : 'text-gray-600 hover:text-gray-900'
                                ]"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                                </svg>
                                List
                            </button>
                            <button 
                                @click="viewMode = 'map'"
                                :class="[
                                    'flex items-center px-3 py-2 rounded-md text-sm font-medium transition-all',
                                    viewMode === 'map' 
                                        ? 'bg-white text-gray-900 shadow-sm' 
                                        : 'text-gray-600 hover:text-gray-900'
                                ]"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                                </svg>
                                Map
                            </button>
                        </div>
                        
                        <!-- Property count -->
                        <div class="text-right">
                            <div class="text-lg font-medium text-gray-900">
                                {{ properties.total }} Properties Found
                            </div>
                            <div class="text-sm text-gray-500">
                                <span v-if="viewMode === 'list'">Showing {{ properties.from }}-{{ properties.to }} of {{ properties.total }}</span>
                                <span v-else>Showing all {{ properties.data.length }} on current page</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Section -->
            <CompactSearchHeader :initialFilters="filters" routeURL="/properties" :useTextSearch="false" />

            <!-- List View -->
            <div v-if="viewMode === 'list'" class="space-y-6">
                <!-- Properties Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
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

            <!-- Map View -->
            <div v-else-if="viewMode === 'map'" class="mt-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <PropertyMap 
                        :properties="properties.data" 
                        :filters="filters"
                        :googleMapsIds="googleMapsIds"
                        :googleMapsApiKey="googleMapsApiKey"
                        class="h-[600px]"
                    />
                </div>
                
                <!-- Map View Info -->
                <div class="mt-4 text-center text-sm text-gray-500">
                    <p>Showing {{ properties.data.length }} properties from page {{ properties.current_page }} of {{ properties.last_page }}</p>
                    <p v-if="properties.last_page > 1">
                        Use the pagination below to load different properties on the map
                    </p>
                </div>
                
                <!-- Pagination for Map View -->
                <div v-if="properties.last_page > 1" class="flex items-center justify-center space-x-2 mt-4">
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
        </div>
        <Footer :businessPhone="businessPhone" :businessEmail="businessEmail" />
    </BaseLayout>
</template>