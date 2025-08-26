<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import StaticMap from '@/components/ui/map/StaticMap.vue';
import { Property, PropertyPricing, User, PropertyAttachment, Feature } from '@/types';
import { formatPrice, formatDate } from '@/utils';
import { ref } from 'vue';

interface Booking {
    id: number;
    check_in_date: string;
    check_out_date: string;
    status: string;
    total_amount: number;
    created_at: string;
}

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
const getStatusBadgeClass = (status: string) => {
    const classes = {
        'active': 'bg-green-100 text-green-800',
        'pending': 'bg-yellow-100 text-yellow-800',
        'sold': 'bg-gray-100 text-gray-800',
        'withdrawn': 'bg-red-100 text-red-800'
    };
    return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-800';
};

const getPropertyPrice = () => {
    if (property.listing_type === 'for_rent' && current_pricing) {
        if (current_pricing.monthly_rate) {
            return `${formatPrice(current_pricing.monthly_rate)}/month`;
        }
        if (current_pricing.weekly_rate) {
            return `${formatPrice(current_pricing.weekly_rate)}/week`;
        }
        if (current_pricing.nightly_rate) {
            return `${formatPrice(current_pricing.nightly_rate)}/night`;
        }
    }
    
    if (property.price) {
        return formatPrice(property.price);
    }
    
    return property.price_type === 'poa' ? 'POA' : 'Price not set';
};

const openImageModal = (attachment: PropertyAttachment) => {
    selectedImage.value = attachment;
    showImageModal.value = true;
};

const deleteProperty = () => {
    if (confirm(`Are you sure you want to delete "${property.title}"? This action cannot be undone.`)) {
        router.delete(route('admin.properties.destroy', property.id));
    }
};

const toggleFeatured = () => {
    router.post(route('admin.properties.toggle-featured', property.id), {}, {
        preserveScroll: true
    });
};

const togglePremium = () => {
    router.post(route('admin.properties.toggle-premium', property.id), {}, {
        preserveScroll: true
    });
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
                        <button
                            @click="toggleFeatured"
                            :class="property.is_featured ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-gray-600 hover:bg-gray-700'"
                            class="text-white px-4 py-2 rounded-lg transition-colors duration-200"
                        >
                            {{ property.is_featured ? 'Remove Featured' : 'Mark Featured' }}
                        </button>
                        <button
                            @click="togglePremium"
                            :class="property.is_premium ? 'bg-purple-600 hover:bg-purple-700' : 'bg-gray-600 hover:bg-gray-700'"
                            class="text-white px-4 py-2 rounded-lg transition-colors duration-200"
                        >
                            {{ property.is_premium ? 'Remove Premium' : 'Mark Premium' }}
                        </button>
                        <Link 
                            :href="route('admin.properties.edit', property.id)"
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
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Property Overview</h2>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Basic Info -->
                                <div class="space-y-4">
                                    <div>
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Property Type</span>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ propertyTypes[property.property_type] }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Listing Type</span>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ listingTypes[property.listing_type] }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</span>
                                        <div class="mt-1">
                                            <span :class="getStatusBadgeClass(property.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                                {{ statusOptions[property.status] }}
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Price</span>
                                        <p class="mt-1 text-lg font-semibold text-green-600 dark:text-green-400">{{ getPropertyPrice() }}</p>
                                        <p v-if="property.price_type !== 'fixed'" class="text-xs text-gray-500">{{ priceTypes[property.price_type] }}</p>
                                    </div>
                                </div>

                                <!-- Specifications -->
                                <div class="space-y-4">
                                    <div v-if="property.bedrooms">
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Bedrooms</span>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ property.bedrooms }}</p>
                                    </div>
                                    <div v-if="property.bathrooms">
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Bathrooms</span>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ property.bathrooms }}</p>
                                    </div>
                                    <div v-if="property.car_spaces">
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Car Spaces</span>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ property.car_spaces }}</p>
                                    </div>
                                    <div v-if="property.land_size">
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Land Size</span>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ property.land_size }}m²</p>
                                    </div>
                                    <div v-if="property.floor_area">
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Floor Area</span>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ property.floor_area }}m²</p>
                                    </div>
                                    <div v-if="property.year_built">
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Year Built</span>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ property.year_built }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="mt-6">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</span>
                                <p class="mt-2 text-sm text-gray-900 dark:text-gray-100 leading-relaxed">{{ property.description }}</p>
                            </div>

                            <!-- Special Badges -->
                            <div class="mt-4 flex space-x-2">
                                <span v-if="property.is_featured" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Featured Property
                                </span>
                                <span v-if="property.is_premium" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    Premium Listing
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Address & Location -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Address & Location</h2>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-4">
                                    <div>
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Street Address</span>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                            {{ property.street_number ? `${property.street_number} ` : '' }}{{ property.street_name }}
                                        </p>
                                    </div>
                                    <div v-if="property.village">
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Village</span>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ property.village }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">District</span>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ property.district }}</p>
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <div>
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Regency</span>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ property.regency }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">State</span>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ property.state }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Postcode</span>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ property.postcode }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Map -->
                            <div v-if="property.latitude && property.longitude" class="mt-6">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Location Map</span>
                                <div class="mt-2">
                                    <StaticMap 
                                        :lat="Number(property.latitude)" 
                                        :lng="Number(property.longitude)" 
                                        :apiKey="map_api_key" 
                                        :width="600" 
                                        :height="300" 
                                        :zoom="15"
                                        markerColor="red"
                                        markerLabel="P"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Property Images -->
                    <div v-if="property.attachments.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Property Images</h2>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                <div 
                                    v-for="attachment in property.attachments" 
                                    :key="attachment.id"
                                    class="aspect-square bg-gray-200 dark:bg-gray-700 rounded-lg overflow-hidden cursor-pointer hover:opacity-75 transition-opacity"
                                    @click="openImageModal(attachment)"
                                >
                                    <img 
                                        :src="attachment.path" 
                                        :alt="attachment.title"
                                        class="w-full h-full object-cover"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Features -->
                    <div v-if="property.features && property.features.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Property Features</h2>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div 
                                    v-for="feature in property.features" 
                                    :key="feature.id"
                                    class="flex items-center space-x-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
                                >
                                    <div class="flex-shrink-0">
                                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ feature.name }}</p>
                                        <p v-if="feature.pivot.quantity > 1" class="text-xs text-gray-500 dark:text-gray-400">
                                            Quantity: {{ feature.pivot.quantity }}
                                        </p>
                                        <p v-if="feature.pivot.notes" class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ feature.pivot.notes }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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

                    <!-- Recent Bookings -->
                    <div v-if="property.bookings && property.bookings.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recent Bookings</h2>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div 
                                    v-for="booking in property.bookings.slice(0, 5)" 
                                    :key="booking.id"
                                    class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg"
                                >
                                    <div class="mt-2">
                                        <p class="text-xs text-gray-600 dark:text-gray-400">
                                            {{ formatDate(new Date(booking.check_in_date)) }} - {{ formatDate(new Date(booking.check_out_date)) }}
                                        </p>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">
                                            Total: {{ formatPrice(booking.total_amount) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div v-if="property.bookings.length > 5" class="mt-4 text-center">
                                <button class="text-sm text-blue-600 hover:text-blue-800">
                                    View All Bookings ({{ property.bookings.length }})
                                </button>
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