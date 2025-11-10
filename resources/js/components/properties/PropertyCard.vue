<script setup lang="ts">
import { defineProps, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import type { DetailedPricing, Property } from '@/types';
import { formatPrice } from '@/utils/formatters';
import CardImageGallery from '@/components/properties/CardImageGallery.vue';
import { MapPin, BedSingleIcon, BathIcon, LandPlotIcon } from 'lucide-vue-next';

interface Props {
    property: Property;
}
const { property } = defineProps<Props>();

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
const detailedPricing = computed((): DetailedPricing | null => {
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
</script>

<template>
    <Link 
        :href="route('properties.show', { slug: property.slug })"
    >
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden transition-shadow hover:shadow-lg shadow-primary">
        <!-- Property Image -->
        <CardImageGallery :property="property" :detailedPricing="detailedPricing" />

        <!-- Property Content -->
        <div class="p-4">
            <!-- Title -->
            <h3 class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                
                    {{ property.title }}
            </h3>

            <!-- Location -->
            <div class="flex text-sm text-gray-600 dark:text-gray-400 mb-4 align-middle my-2">
                <component v-if="MapPin" :is="MapPin" class="h-5 w-5 mr-2" />
                <p class="text-gray-600 dark:text-gray-400 mb-3">
                    {{ property.village }}, {{ property.district }}
                </p>
            </div>

            <!-- Price -->
            <div class="mb-3">
                <div class="text-xl font-bold text-gray-600 dark:text-gray-400">
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

            <!-- Property Stats -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-4"></div>
            <div class="grid grid-cols-3 gap-4 mb-2">
                <div v-if="property.bedrooms" class="text-center">
                    <component v-if="BedSingleIcon" :is="BedSingleIcon" class="h-5 w-5 mx-auto" />
                    <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ property.bedrooms }}</div>
                </div>
                <div v-if="property.bathrooms" class="text-center">
                    <component v-if="BathIcon" :is="BathIcon" class="h-5 w-5 mx-auto" />
                    <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ property.bathrooms }}</div>
                </div>
                <div v-if="property.land_size" class="text-center">
                    <component v-if="LandPlotIcon" :is="LandPlotIcon" class="h-5 w-5 mx-auto" />
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
        </div>
    </div>
</Link>

</template>