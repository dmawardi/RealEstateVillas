<script setup lang="ts">
import { Property, PropertyPricing } from '@/types';
import { formatPrice, getAmenityIcon, getAmenityLabel } from '@/utils';

interface Props {
    property: Property;
    current_pricing?: PropertyPricing;
    propertyTypes: Record<string, string>;
    listingTypes: Record<string, string>;
    statusOptions: Record<string, string>;
    priceTypes: Record<string, string>;
}

const { property, current_pricing, propertyTypes, listingTypes, statusOptions, priceTypes } = defineProps<Props>();

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
        if (current_pricing.nightly_rate) {
            // Check for monthly discount first (better value)
            if (current_pricing.monthly_discount_active && current_pricing.monthly_discount_percent && current_pricing.monthly_discount_percent > 0) {
                const daysForMonthly = current_pricing.min_days_for_monthly || 30;
                const monthlyRate = current_pricing.nightly_rate * daysForMonthly * (1 - current_pricing.monthly_discount_percent / 100);
                return `${formatPrice(monthlyRate)}/month`;
            }
            // Check for weekly discount
            if (current_pricing.weekly_discount_active && current_pricing.weekly_discount_percent && current_pricing.weekly_discount_percent > 0) {
                const daysForWeekly = current_pricing.min_days_for_weekly || 7;
                const weeklyRate = current_pricing.nightly_rate * daysForWeekly * (1 - current_pricing.weekly_discount_percent / 100);
                return `${formatPrice(weeklyRate)}/week`;
            }
            // Default to nightly rate
            return `${formatPrice(current_pricing.nightly_rate)}/night`;
        }
    }
    
    if (property.price) {
        return formatPrice(property.price);
    }
    
    return property.price_type === 'poa' ? 'POA' : 'Price not set';
};

const hasAmenities = () => {
    if (!property.amenities) return false;
    
    return Object.values(property.amenities).some(amenityList => 
        Array.isArray(amenityList) && amenityList.length > 0
    );
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Property Overview</h2>
            <div class="mt-1 flex flex-col sm:flex-row sm:flex-wrap sm:mt-0 sm:space-x-6">
                <div class="mt-2 flex items-center text-sm text-gray-500">
                    <span class="font-medium">Property Slug:</span>
                    <span class="ml-1">{{ property.slug }}</span>
                </div>
            </div>
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
                <div class="mt-2 text-sm text-gray-900 dark:text-gray-100 leading-relaxed [&_p]:mb-4 [&_h2]:text-lg [&_h2]:font-semibold" v-html="property.description"></div>
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
    </div>
</template>