<script setup lang="ts">
import type { DetailedPricing } from '@/types';

interface Props {
    pricing: DetailedPricing;
    showLabel?: boolean;
    textClass?: string;
}

const props = withDefaults(defineProps<Props>(), {
    showLabel: true,
    textClass: 'text-lg text-gray-600 dark:text-gray-300'
});

// Determine which discounted rate to show based on getPriceDisplay logic
const showWeeklyRate = () => {
    // Show weekly if it has discount AND weekly discount >= monthly discount
    return props.pricing.weekly.hasDiscount && 
           props.pricing.weekly.discount >= props.pricing.monthly.discount;
};

const showMonthlyRate = () => {
    // Show monthly if it has discount AND monthly discount > weekly discount
    return props.pricing.monthly.hasDiscount && 
           props.pricing.monthly.discount > props.pricing.weekly.discount;
};
</script>

<template>
    <div class="space-y-1">
        <div :class="textClass">
            <p class="truncate">
                <span v-if="showLabel">Nightly: </span>{{ pricing.nightly.display }}
            </p>
            <p v-if="showWeeklyRate()" class="truncate">
                <span v-if="showLabel">Weekly: </span>{{ pricing.weekly.display }}
                <span class="text-green-600">({{ pricing.weekly.discount }}% off)</span>
            </p>
            <p v-else-if="showMonthlyRate()" class="truncate">
                <span v-if="showLabel">Monthly: </span>{{ pricing.monthly.display }}
                <span class="text-green-600">({{ pricing.monthly.discount }}% off)</span>
            </p>
            <!-- Show monthly for context if no significant discounts but rates differ -->
            <p v-else-if="!showWeeklyRate() && !showMonthlyRate() && pricing.monthly.rate !== (pricing.nightly.rate * 30)" class="truncate">
                <span v-if="showLabel">Monthly: </span>{{ pricing.monthly.display }}
            </p>
        </div>
    </div>
</template>