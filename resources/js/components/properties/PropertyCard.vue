<script setup lang="ts">
import { defineProps, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import type { Property } from '@/types';
import { formatPrice } from '@/utils/formatters';

interface Props {
    property: Property;
}
const { property } = defineProps<Props>();

// Helper function to format property type
const formatPropertyType = (type: string): string => {
    return type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
};

// Helper to get current active pricing
const getCurrentActivePricing = (property: Property) => {
    if (!property.pricing || property.pricing.length === 0) {
        return null;
    }
    
    const today = new Date().toISOString().split('T')[0];
    return property.pricing.find(p => 
        p.start_date && p.end_date && p.start_date <= today && p.end_date >= today
    ) || null;
};

// Helper function to calculate rates based on nightly rate and discounts
const calculateRates = (pricing: any) => {
    if (!pricing?.nightly_rate) return null;
    
    const nightlyRate = pricing.nightly_rate;
    
    // Calculate weekly rate
    let weeklyRate = pricing.weekly_rate;
    let weeklyDiscount = pricing.weekly_discount_percent;
    
    if (!weeklyRate && pricing.min_days_for_weekly && weeklyDiscount) {
        // Calculate weekly rate from nightly rate with discount
        const daysForWeekly = pricing.min_days_for_weekly || 7;
        weeklyRate = Math.round(nightlyRate * daysForWeekly * (1 - weeklyDiscount / 100));
    } else if (!weeklyRate) {
        // Default weekly rate without discount (7 days)
        weeklyRate = nightlyRate * 7;
        weeklyDiscount = 0;
    }
    
    // Calculate monthly rate
    let monthlyRate = pricing.monthly_rate;
    let monthlyDiscount = pricing.monthly_discount_percent;
    
    if (!monthlyRate && pricing.min_days_for_monthly && monthlyDiscount) {
        // Calculate monthly rate from nightly rate with discount
        const daysForMonthly = pricing.min_days_for_monthly || 30;
        monthlyRate = Math.round(nightlyRate * daysForMonthly * (1 - monthlyDiscount / 100));
    } else if (!monthlyRate) {
        // Default monthly rate without discount (30 days)
        monthlyRate = nightlyRate * 30;
        monthlyDiscount = 0;
    }
    
    return {
        nightly: nightlyRate,
        weekly: weeklyRate,
        monthly: monthlyRate,
        weeklyDiscount,
        monthlyDiscount
    };
};

// Helper function to get price display
const getPriceDisplay = (property: Property) => {
    if (property.listing_type === 'for_rent') {
        const currentPricing = getCurrentActivePricing(property);
        
        if (currentPricing) {
            const rates = calculateRates(currentPricing);
            
            if (rates) {
                // Primary display: nightly rate
                const nightlyDisplay = `${formatPrice(rates.nightly)}/night`;
                
                // Add weekly rate with discount info if significant discount
                if (rates.weeklyDiscount && rates.weeklyDiscount > 0) {
                    const weeklyDisplay = `${formatPrice(rates.weekly)}/week`;
                    const discountText = rates.weeklyDiscount > 5 ? ` (${rates.weeklyDiscount}% off)` : '';
                    return `${nightlyDisplay} • ${weeklyDisplay}${discountText}`;
                }
                
                // Add monthly rate with discount info if significant discount
                if (rates.monthlyDiscount && rates.monthlyDiscount > 10) {
                    const monthlyDisplay = `${formatPrice(rates.monthly)}/month`;
                    const discountText = ` (${rates.monthlyDiscount}% off)`;
                    return `${nightlyDisplay} • ${monthlyDisplay}${discountText}`;
                }
                
                // Show just nightly + monthly for context if no significant discounts
                if (rates.monthly && rates.monthly !== rates.nightly * 30) {
                    return `${nightlyDisplay} • ${formatPrice(rates.monthly)}/month`;
                }
                
                return nightlyDisplay;
            }
            
            // Fallback to stored rates if calculation fails
            if (currentPricing.nightly_rate) {
                return `${formatPrice(currentPricing.nightly_rate)}/night`;
            }
            if (currentPricing.weekly_rate) {
                return `${formatPrice(currentPricing.weekly_rate)}/week`;
            }
            if (currentPricing.monthly_rate) {
                return `${formatPrice(currentPricing.monthly_rate)}/month`;
            }
        }
        // No active pricing period found
        return 'Rental Rate on Application';
    }
    
    // For sale properties
    if (property.price) {
        return formatPrice(property.price);
    }
    
    return 'Price on Application';
};

// Computed property to show detailed pricing info
const detailedPricing = computed(() => {
    if (property.listing_type !== 'for_rent') return null;
    
    const currentPricing = getCurrentActivePricing(property);
    if (!currentPricing) return null;
    
    const rates = calculateRates(currentPricing);
    if (!rates) return null;
    
    return {
        nightly: {
            rate: rates.nightly,
            display: formatPrice(rates.nightly)
        },
        weekly: {
            rate: rates.weekly,
            display: formatPrice(rates.weekly),
            discount: rates.weeklyDiscount,
            hasDiscount: rates.weeklyDiscount > 0
        },
        monthly: {
            rate: rates.monthly,
            display: formatPrice(rates.monthly),
            discount: rates.monthlyDiscount,
            hasDiscount: rates.monthlyDiscount > 0
        },
        periodName: currentPricing.name
    };
});

// Helper function to truncate description
const truncateDescription = (text: string, length: number = 150): string => {
    if (text.length <= length) return text;
    return text.substring(0, length) + '...';
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow">
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

            <!-- Discount Badge -->
            <div v-if="detailedPricing?.monthly.hasDiscount && detailedPricing.monthly.discount > 15" 
                 class="absolute bottom-3 left-3">
                <span class="bg-red-500 text-white px-2 py-1 rounded text-xs font-medium">
                    {{ detailedPricing.monthly.discount }}% Monthly Discount
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
                
                <!-- Show pricing period name and additional rates for rentals -->
                <div v-if="detailedPricing" class="mt-2 space-y-1">
                    <div v-if="detailedPricing.periodName" class="text-sm text-gray-500 dark:text-gray-400">
                        {{ detailedPricing.periodName }}
                    </div>
                    
                    <!-- Show additional rate info on hover or as subtitle -->
                    <div class="text-sm text-gray-600 dark:text-gray-300">
                        <span v-if="detailedPricing.weekly.hasDiscount">
                            Weekly: {{ detailedPricing.weekly.display }} 
                            <span class="text-green-600">({{ detailedPricing.weekly.discount }}% off)</span>
                        </span>
                        <span v-if="detailedPricing.monthly.hasDiscount" 
                              :class="detailedPricing.weekly.hasDiscount ? 'ml-3' : ''">
                            Monthly: {{ detailedPricing.monthly.display }}
                            <span class="text-green-600">({{ detailedPricing.monthly.discount }}% off)</span>
                        </span>
                    </div>
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

            <!-- Rest of the template remains the same... -->
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
                        :href="`/properties/${property.slug}`"
                        class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700 transition-colors"
                    >
                        View Details
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>