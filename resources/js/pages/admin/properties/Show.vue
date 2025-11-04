<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Property, PropertyPricing, User, PropertyAttachment, Feature } from '@/types';
import { formatPrice, formatDate } from '@/utils';
import { ref } from 'vue';
import PropertyOverview from '@/components/properties/admin/PropertyOverview.vue';
import PropertyAddressLocation from '@/components/properties/admin/PropertyAddressLocation.vue';
import PropertyFeatures from '@/components/properties/PropertyFeatures.vue';
import PropertyBookings from '@/components/properties/admin/PropertyBookings.vue';
import { Booking } from '@/types';
import PropertyAttachments from '@/components/properties/admin/PropertyAttachments.vue';
import AdminPropertyPricing from '@/components/properties/admin/pricing/AdminPropertyPricing.vue';


interface Props {
    property: Property & {
        user: User;
        pricing: PropertyPricing[];
        attachments: PropertyAttachment[];
        features: Feature[];
        bookings: Booking[];
    };
    current_pricing?: PropertyPricing;
    map_api_key: string;
    propertyTypes: Record<string, string>;
    listingTypes: Record<string, string>;
    statusOptions: Record<string, string>;
    priceTypes: Record<string, string>;
}

const { property, current_pricing, map_api_key, propertyTypes, listingTypes, statusOptions, priceTypes } = defineProps<Props>();

const showImageModal = ref(false);
const selectedImage = ref<PropertyAttachment | null>(null);

// Helper functions
const deleteProperty = () => {
    if (confirm(`Are you sure you want to delete "${property.title}"? This action cannot be undone.`)) {
        router.delete(route('admin.properties.destroy', property.slug));
    }
};
</script>

<template>
    <Head :title="`${property.title} - Admin Property Details`" />

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
                                        <span class="ml-4 text-sm font-medium text-gray-500">{{ property.title }}</span>
                                    </div>
                                </li>
                            </ol>
                        </nav>
                        <h1 class="mt-2 text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate">
                            {{ property.title }}
                        </h1>
                        <div class="mt-1 flex flex-col sm:flex-row sm:flex-wrap sm:mt-0 sm:space-x-6">
                            <div class="mt-2 flex items-center text-sm text-gray-500">
                                <span class="font-medium">Property ID:</span>
                                <span class="ml-1">{{ property.property_id }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex space-x-3">
                        <Link 
                            :href="route('admin.properties.edit', property.slug)"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200"
                        >
                            Edit Property
                        </Link>
                        <button
                            @click="deleteProperty"
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors duration-200"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content - 2 columns -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Property Overview -->
                    <PropertyOverview
                        :property="property"
                        :current_pricing="current_pricing"
                        :property-types="propertyTypes"
                        :listing-types="listingTypes"
                        :status-options="statusOptions"
                        :price-types="priceTypes"
                    />

                    <!-- Address & Location -->
                    <PropertyAddressLocation
                        :property="property"
                        :map-api-key="map_api_key"
                    />
                    <!-- Attachments -->
                    <PropertyAttachments
                        :property="property"
                    />

                    <!-- Features -->
                    <PropertyFeatures
                        :property="property"
                        :editable="true"
                    />

                    <PropertyBookings 
                        :property="property"
                    />
                    <AdminPropertyPricing
                        :property="property"
                        :pricing="property.pricing"
                    />
                </div>

                <!-- Sidebar - 1 column -->
                <div class="space-y-8">
                    <!-- Agent Information -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Agent Information</h2>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Agent Name</span>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ property.agent_name }}</p>
                            </div>
                            <div v-if="property.agency_name">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Agency</span>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ property.agency_name }}</p>
                            </div>
                            <div v-if="property.agent_phone">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone</span>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    <a :href="`tel:${property.agent_phone}`" class="text-blue-600 hover:text-blue-800">
                                        {{ property.agent_phone }}
                                    </a>
                                </p>
                            </div>
                            <div v-if="property.agent_email">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</span>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    <a :href="`mailto:${property.agent_email}`" class="text-blue-600 hover:text-blue-800">
                                        {{ property.agent_email }}
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing Details -->
                    <div v-if="current_pricing" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Rental Pricing</h2>
                        </div>
                        <div class="p-6 space-y-4">
                            <div v-if="current_pricing.nightly_rate">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Nightly Rate</span>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ formatPrice(current_pricing.nightly_rate) }}</p>
                            </div>
                            <div v-if="current_pricing.weekly_rate">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Weekly Rate</span>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ formatPrice(current_pricing.weekly_rate) }}</p>
                            </div>
                            <div v-if="current_pricing.monthly_rate">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Monthly Rate</span>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ formatPrice(current_pricing.monthly_rate) }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Currency</span>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ current_pricing.currency }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Property Statistics -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Statistics</h2>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Views</span>
                                <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ property.view_count }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Days on Market</span>
                                <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ property.days_on_market }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Listed Date</span>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ formatDate(new Date(property.listed_at || property.created_at)) }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated</span>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ formatDate(new Date(property.updated_at)) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Image Modal -->
        <div v-if="showImageModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50" @click="showImageModal = false">
            <div class="max-w-4xl max-h-full p-4">
                <img 
                    v-if="selectedImage"
                    :src="selectedImage.path" 
                    :alt="selectedImage.title"
                    class="max-w-full max-h-full object-contain"
                />
                <button 
                    @click="showImageModal = false"
                    class="absolute top-4 right-4 text-white hover:text-gray-300"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </AppLayout>
</template>