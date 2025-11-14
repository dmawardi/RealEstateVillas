<script setup lang="ts">
import { Property } from '@/types';

interface Props {
    property: Property;
}
const props = defineProps<Props>();
const property = props.property;
console.log("Property in key stats:", property);
</script>

<template>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div v-if="property.bedrooms" class="text-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ property.bedrooms }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">Bedrooms</div>
        </div>
        <div v-if="property.bathrooms" class="text-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ property.bathrooms }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">Bathrooms</div>
        </div>
        <div v-if="property.car_spaces" class="text-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ property.car_spaces }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">Car Spaces</div>
        </div>
        <div v-if="property.land_size" class="text-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ property.land_size }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">Land Size (m²)</div>
        </div>
    </div>
</template>