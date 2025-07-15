<script setup lang="ts">
import { Property } from '@/types';

interface Props {
    property: Property;
}
const props = defineProps<Props>();
const property = props.property;

// Helper function to format property type
const formatPropertyType = (type: string): string => {
    return type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
};
</script>


<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">
            Property Information
        </h3>
        <div class="space-y-3 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Type:</span>
                <span class="font-medium text-gray-900 dark:text-gray-100 capitalize">
                    {{ formatPropertyType(property.property_type) }}
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Status:</span>
                <span class="font-medium text-gray-900 dark:text-gray-100 capitalize">
                    {{ property.status }}
                </span>
            </div>
            <div v-if="property.year_built" class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Year Built:</span>
                <span class="font-medium text-gray-900 dark:text-gray-100">{{ property.year_built }}</span>
            </div>
            <div v-if="property.floor_area" class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Floor Area:</span>
                <span class="font-medium text-gray-900 dark:text-gray-100">{{ property.floor_area }} m²</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Views:</span>
                <span class="font-medium text-gray-900 dark:text-gray-100">{{ property.view_count }}</span>
            </div>
        </div>
    </div>
</template>