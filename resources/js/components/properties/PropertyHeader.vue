<script setup lang="ts">
import type { Property } from '@/types';

interface Props {
    property: Property;
}

const { property } = defineProps<Props>();

// Helper function to format price
const formatPrice = (price: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(price);
};

// Helper function to format property type
const formatPropertyType = (type: string): string => {
    return type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
};

// Helper function to get price display
const getPriceDisplay = () => {
    if (property.listing_type === 'for_rent') {
        if (property.rental_price_monthly) {
            return `${formatPrice(property.rental_price_monthly)}/month`;
        }
        if (property.rental_price_weekly) {
            return `${formatPrice(property.rental_price_weekly)}/week`;
        }
    }
    if (property.price) {
        return formatPrice(property.price);
    }
    return 'Price on Application';
};
</script>

<template>
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                    {{ property.title }}
                </h1>
                <p class="text-lg text-gray-600 dark:text-gray-400 mt-2">
                    {{ property.street_name }}, {{ property.village }}, {{ property.district }}
                </p>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                    {{ getPriceDisplay() }}
                </div>
                <div class="text-sm text-gray-500 capitalize">
                    {{ formatPropertyType(property.listing_type) }}
                </div>
            </div>
        </div>
    </div>
</template>