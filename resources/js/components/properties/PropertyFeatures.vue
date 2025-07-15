<script setup lang="ts">
import { Property } from '@/types';

interface Props {
    property: Property;
}
const props = defineProps<Props>();
const property = props.property;
</script>

<template>
    <div v-if="property.features && property.features.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">
            Features
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div 
                v-for="feature in property.features" 
                :key="feature.id"
                class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
            >
                <div class="flex-1">
                    <div class="font-medium text-gray-900 dark:text-gray-100">
                        {{ feature.name }}
                        <span v-if="feature.pivot.quantity > 1" class="text-sm text-gray-500">
                            ({{ feature.pivot.quantity }})
                        </span>
                    </div>
                    <div v-if="feature.pivot.notes" class="text-sm text-gray-600 dark:text-gray-400">
                        {{ feature.pivot.notes }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>