<script setup lang="ts">
import type { DetailedPricing, Property } from '@/types';
import { computed, defineProps } from 'vue';
import { formatPrice } from '@/utils/formatters'; // Importing formatPrice utility
import { MapPin } from 'lucide-vue-next';
import { calculateRates } from '@/utils';

interface Props {
    property: Property;
}

const { property } = defineProps<Props>();

// Helper function to format property type
const formatPropertyType = (type: string): string => {
    return type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
};

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
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                    {{ property.title }}
                </h1>
                 <div class="flex text-lg text-gray-600 dark:text-gray-400 mb-3 h-6 align-middle">
                    <component v-if="MapPin" :is="MapPin" class="h-4 w-4 mr-2 flex-shrink-0 my-auto" />
                    <p class="text-gray-600 dark:text-gray-400 truncate">
                        {{ property.village }}, {{ property.district }}
                    </p>
                </div>

            </div>
            <div class="text-right">
                <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                    <!-- Show pricing period name and additional rates for rentals -->
                    <div v-if="detailedPricing" class="mt-1 space-y-1">
                        <div class="text-lg text-gray-600 dark:text-gray-300">
                            <p class="truncate">
                                Nightly: {{ detailedPricing.nightly.display }}
                            </p>
                            <p v-if="detailedPricing.weekly.hasDiscount && (!detailedPricing.monthly.hasDiscount || detailedPricing.weekly.discount >= detailedPricing.monthly.discount)" class="truncate">
                                Weekly: {{ detailedPricing.weekly.display }}
                                <span class="text-green-600">({{ detailedPricing.weekly.discount }}% off)</span>
                            </p>
                            <p v-else-if="detailedPricing.monthly.hasDiscount && (!detailedPricing.weekly.hasDiscount || detailedPricing.monthly.discount > detailedPricing.weekly.discount)" class="truncate">
                                Monthly: {{ detailedPricing.monthly.display }}
                                <span class="text-green-600">({{ detailedPricing.monthly.discount }}% off)</span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="text-sm text-gray-500 capitalize">
                    {{ formatPropertyType(property.listing_type) }}
                </div>
            </div>
        </div>
    </div>
</template>