<script setup lang="ts">
import PropertyBookingCard from '@/components/properties/PropertyBookingCard.vue';
import PropertyFeatures from '@/components/properties/PropertyFeatures.vue';
import PropertyHeader from '@/components/properties/PropertyHeader.vue';
import PropertyImages from '@/components/properties/PropertyImages.vue';
import PropertyInfoBar from '@/components/properties/PropertyInfoBar.vue';
import PropertyKeyStats from '@/components/properties/PropertyKeyStats.vue';
import StaticMap from '@/components/ui/map/StaticMap.vue';
import BaseLayout from '@/layouts/BaseLayout.vue';
import type { BreadcrumbItemType, Property, PropertyPricing } from '@/types';
import { Head } from '@inertiajs/vue3';
import { onMounted } from 'vue';

interface Props {
    property: Property;
    current_pricing: PropertyPricing;
    map_api_key: string;
}

const { property, current_pricing, map_api_key } = defineProps<Props>();

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
onMounted(() => {
    console.log('Property data:', property);
});
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
                    <PropertyImages :attachments="property.attachments ?? []" />
                    <!-- Property Details -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">
                            Property Details
                        </h2>
                        
                        <!-- Key Stats -->
                        <PropertyKeyStats :property="property" />

                        <!-- Description -->
                        <div>
                            <h3 class="text-lg font-medium mb-3 text-gray-900 dark:text-gray-100">Description</h3>
                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                                {{ property.description }}
                            </p>
                        </div>
                    </div>
    
                    <!-- Features -->
                    <PropertyFeatures :property="property" />
                </div>
    
                <!-- Sidebar -->
                <div class="space-y-6">
                    <PropertyBookingCard :property="property" :current_pricing="current_pricing" />
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
                    <PropertyInfoBar :property="property" />
                </div>
                <StaticMap 
                    :lat="Number(property.latitude)" 
                    :lng="Number(property.longitude)" 
                    :apiKey="map_api_key" 
                    :width="600" 
                    :height="400" 
                    :zoom="16" />
            </div>
        </div>
    </BaseLayout>
</template>