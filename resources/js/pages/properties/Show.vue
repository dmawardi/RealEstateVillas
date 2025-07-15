<script setup lang="ts">
import PropertyHeader from '@/components/properties/PropertyHeader.vue';
import BaseLayout from '@/layouts/BaseLayout.vue';
import type { BreadcrumbItemType, Property } from '@/types';
import { Head } from '@inertiajs/vue3';

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

// Helper function to format property type
const formatPropertyType = (type: string): string => {
    return type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
};
</script>

<template>
    <Head :title="`${property.title} - Property Details`" />
    <BaseLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Property Header -->
            <PropertyHeader :property="property" />
    
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