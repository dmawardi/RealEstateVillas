<script setup lang="ts">
import Footer from '@/components/Footer.vue';
import BookingModal from '@/components/properties/BookingModal.vue';
import PropertyBookingBottomNav from '@/components/properties/PropertyBookingBottomNav.vue';
import PropertyBookingCard from '@/components/properties/PropertyBookingCard.vue';
import PropertyFeatures from '@/components/properties/PropertyFeatures.vue';
import PropertyHeader from '@/components/properties/PropertyHeader.vue';
import PropertyImages from '@/components/properties/PropertyImages.vue';
import PropertyInfoBar from '@/components/properties/PropertyInfoBar.vue';
import PropertyKeyStats from '@/components/properties/PropertyKeyStats.vue';
import SEOHead from '@/components/SEOHead.vue';
import StaticMap from '@/components/ui/map/StaticMap.vue';
import BaseLayout from '@/layouts/BaseLayout.vue';
import type { BreadcrumbItemType, Property, PropertyPricing, SEO } from '@/types';
import { getAmenityIcon, getAmenityLabel } from '@/utils';
import { ref } from 'vue';

interface Props {
    property: Property;
    current_pricing: PropertyPricing;
    map_api_key: string;
    businessPhone: string;
    businessEmail: string;
    seoData?: SEO;
}

const { property, current_pricing, map_api_key, businessPhone, businessEmail, seoData } = defineProps<Props>();

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

const isBookingModalOpen = ref(false);
const selectedDateRange = ref<[Date, Date] | null>(null);
const priceCalculation = ref<any>(null);

const openBookingModal = (dates: [Date, Date] | null, price: any) => {
    selectedDateRange.value = dates;
    priceCalculation.value = price;
    isBookingModalOpen.value = true;
};

const closeBookingModal = () => {
    isBookingModalOpen.value = false;
    selectedDateRange.value = null;
    priceCalculation.value = null;
};

const hasAmenities = () => {
    if (!property.amenities) return false;
    
    return Object.values(property.amenities).some(amenityList => 
        Array.isArray(amenityList) && amenityList.length > 0
    );
};
</script>

<template>
    <SEOHead v-if="seoData" :seoData="seoData" />
    <BaseLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2">
            <!-- Property Header -->
            <PropertyHeader :property="property" class="hidden md:block" />
    
            <!-- Property Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-8">
                    <PropertyImages :attachments="property.attachments ?? []" />
                    <PropertyHeader :property="property" class="block md:hidden" />
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
                            <div class="mt-2 text-sm text-gray-900 dark:text-gray-100 leading-relaxed [&_p]:mb-4 [&_h2]:text-lg [&_h2]:font-semibold" v-html="property.description"></div>
                        </div>
                        <!-- Amenities -->
                        <div v-if="hasAmenities()" class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-4">Nearby Amenities</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <template v-for="(amenityList, amenityType) in property.amenities" :key="amenityType">
                                    <div v-if="Array.isArray(amenityList) && amenityList.length > 0" class="space-y-3">
                                        <div class="flex items-center gap-2">
                                            <component 
                                                :is="getAmenityIcon(String(amenityType))" 
                                                class="h-4 w-4 text-blue-600 dark:text-blue-400" 
                                            />
                                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ getAmenityLabel(String(amenityType)) }}
                                            </h4>
                                        </div>
                                        
                                        <ul class="space-y-1 ml-6">
                                            <li 
                                                v-for="(amenity, index) in amenityList" 
                                                :key="index"
                                                class="text-sm text-gray-600 dark:text-gray-300 flex items-start"
                                            >
                                                <span class="w-1.5 h-1.5 bg-gray-400 rounded-full mt-2 mr-2 flex-shrink-0"></span>
                                                <span>{{ amenity }}</span>
                                            </li>
                                        </ul>
                                    </div>
                                </template>
                            </div>

                            <!-- No amenities message (in case amenities object exists but is empty) -->
                            <div v-if="property.amenities && !hasAmenities()" class="text-center py-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400">No nearby amenities listed</p>
                            </div>
                        </div>

                        <!-- Additional Property Details -->
                        <div v-if="property.zoning || property.available_date || property.inspection_times" class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-4">Additional Details</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div v-if="property.zoning">
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Zoning</span>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ property.zoning }}</p>
                                </div>
                                
                                <div v-if="property.available_date">
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Available Date</span>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                        {{ new Date(property.available_date).toLocaleDateString() }}
                                    </p>
                                </div>
                            </div>
                            
                            <div v-if="property.inspection_times" class="mt-4">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Inspection Times</span>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100 whitespace-pre-line">{{ property.inspection_times }}</p>
                            </div>
                        </div>
                    </div>
    
                    <!-- Features -->
                    <PropertyFeatures :features="property.features?? []" />
                    <StaticMap
                        v-if="property.latitude && property.longitude"
                        :lat="Number(property.latitude)" 
                        :lng="Number(property.longitude)" 
                        :apiKey="map_api_key" 
                        :width="800" 
                        :height="400" 
                        :zoom="16" />
                </div>
    
                <!-- Sidebar -->
                <div class="lg:col-span-1">
                    <div class="space-y-6 lg:sticky lg:top-24 lg:self-start">
                        <PropertyBookingCard 
                            v-if="property.listing_type === 'for_rent'"
                            class="hidden lg:block" 
                            :property="property" 
                            :current_pricing="current_pricing" 
                            :businessPhone="businessPhone"
                            @open-booking="openBookingModal"
                        />
                        <!-- Property Info -->
                        <PropertyInfoBar :property="property" />
                    </div>

                </div>
            </div>
        </div>
        <!-- Sticky Bottom Navigation for Mobile -->
        <PropertyBookingBottomNav 
            v-if="property.listing_type === 'for_rent'"
            :property="property" 
            :current_pricing="current_pricing" 
            :businessPhone="businessPhone"
        />

        <BookingModal
            v-model="isBookingModalOpen"
            :property="property" 
            :check-in-date="selectedDateRange?.[0] || null"
            :check-out-date="selectedDateRange?.[1] || null"
            :total-price="priceCalculation?.total_price || 0"
            :nights="priceCalculation?.nights || 0"
            @booking-success="closeBookingModal"
        />
        
        <Footer :businessEmail="businessEmail" :businessPhone="businessPhone" />
    </BaseLayout>
</template>