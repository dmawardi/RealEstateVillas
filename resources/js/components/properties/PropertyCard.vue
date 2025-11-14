<script setup lang="ts">
import { defineProps, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import type { DetailedPricing, Property } from '@/types';
import { formatPrice } from '@/utils/formatters';
import CardImageGallery from '@/components/properties/CardImageGallery.vue';
import { MapPin, BedSingleIcon, BathIcon, LandPlotIcon } from 'lucide-vue-next';
import { calculateRates, getPriceDisplay } from '@/utils';

interface Props {
    property: Property;
}
const { property } = defineProps<Props>();

// Computed property to show detailed pricing info
const detailedPricing = computed((): DetailedPricing | null => {
    if (property.listing_type !== 'for_rent') return null;
    
    const currentPricing = property.pricing && property.pricing.length > 0 
        ? property.pricing[0] 
        : null;
        
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
        periodName: currentPricing.name ?? undefined
    };
});
</script>

<template>
    <Link :href="route('properties.show', { slug: property.slug })">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden transition-shadow hover:shadow-lg shadow-primary h-full flex flex-col">
            <!-- Property Image - Fixed aspect ratio -->
            <div class="flex-shrink-0">
                <CardImageGallery :property="property" :detailedPricing="detailedPricing" />
            </div>

            <!-- Property Content - Flexible height -->
            <div class="p-4 flex flex-col flex-grow">
                <!-- Title - Fixed height with line clamping -->
                <div class="mb-3 h-16 flex items-start">
                    <h3 class="text-xl font-bold text-blue-600 dark:text-blue-400 line-clamp-2 leading-tight">
                        {{ property.title }}
                    </h3>
                </div>

                <!-- Location - Fixed height -->
                <div class="flex text-sm text-gray-600 dark:text-gray-400 mb-3 h-6">
                    <component v-if="MapPin" :is="MapPin" class="h-4 w-4 mr-2 flex-shrink-0" />
                    <p class="text-gray-600 dark:text-gray-400 truncate">
                        {{ property.village }}, {{ property.district }}
                    </p>
                </div>

                <!-- Price - Minimum height container -->
                <div class="mb-4 min-h-[60px] flex flex-col justify-start">
                    <div class="text-lg font-bold text-gray-900 dark:text-gray-100" v-html="getPriceDisplay(property)">
                    </div>
                    
                    <!-- Show pricing period name and additional rates for rentals -->
                    <div v-if="detailedPricing" class="mt-1 space-y-1">
                        <div class="text-xs text-gray-600 dark:text-gray-300">
                            <p v-if="detailedPricing.weekly.hasDiscount" class="truncate">
                                Weekly: {{ detailedPricing.weekly.display }} 
                                <span class="text-green-600">({{ detailedPricing.weekly.discount }}% off)</span>
                            </p>
                            <p v-if="detailedPricing.monthly.hasDiscount" class="truncate">
                                Monthly: {{ detailedPricing.monthly.display }}
                                <span class="text-green-600">({{ detailedPricing.monthly.discount }}% off)</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Features Preview - Flexible but contained -->
                <div v-if="property.features && property.features.length > 0" class="mb-4 min-h-[32px]">
                    <div class="flex flex-wrap gap-1">
                        <span 
                            v-for="feature in property.features.slice(0, 3)" 
                            :key="feature.id"
                            class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-2 py-1 rounded text-xs whitespace-nowrap"
                        >
                            {{ feature.name }}
                        </span>
                        <span 
                            v-if="property.features.length > 3"
                            class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-2 py-1 rounded text-xs whitespace-nowrap"
                        >
                            +{{ property.features.length - 3 }} more
                        </span>
                    </div>
                </div>

                <!-- Spacer to push stats to bottom -->
                <div class="flex-grow"></div>

                <!-- Property Stats - Fixed at bottom -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-auto">
                    <div class="grid grid-cols-3 gap-4">
                        <div v-if="property.land_size" class="text-center">
                            <component v-if="LandPlotIcon" :is="LandPlotIcon" class="h-5 w-5 mx-auto mb-1 text-gray-500" />
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ property.land_size }}m²</div>
                        </div>
                        <div v-if="property.bedrooms" class="text-center">
                            <component v-if="BedSingleIcon" :is="BedSingleIcon" class="h-5 w-5 mx-auto mb-1 text-gray-500" />
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ property.bedrooms }} bed</div>
                        </div>
                        <div v-if="property.bathrooms" class="text-center">
                            <component v-if="BathIcon" :is="BathIcon" class="h-5 w-5 mx-auto mb-1 text-gray-500" />
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ property.bathrooms }} bath</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Link>
</template>