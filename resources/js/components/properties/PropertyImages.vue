<script setup lang="ts">
import { Property } from '@/types';

interface Props {
    property: Property;
}
const props = defineProps<Props>();
const property = props.property;
</script>

<template>
    <!-- Property Images -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <div class="aspect-video bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
            <div v-if="property.attachments && property.attachments.length > 0" class="w-full h-full">
                <img 
                    :src="property.attachments[0].path" 
                    :alt="property.title"
                    class="w-full h-full object-cover"
                />
            </div>
            <div v-else class="text-gray-500 dark:text-gray-400">
                <svg class="w-16 h-16 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
                </svg>
                <p>No images available</p>
            </div>
        </div>
    </div>
</template>