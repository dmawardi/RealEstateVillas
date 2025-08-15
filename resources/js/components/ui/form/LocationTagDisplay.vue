<script setup lang="ts">
import { Location } from '@/types';

interface Props {
    locations: Location[];
}

interface Emits {
    (e: 'remove', location: Location): void;
    (e: 'clear-all'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const removeLocation = (location: Location) => {
    emit('remove', location);
};

const clearAll = () => {
    emit('clear-all');
};
</script>

<template>
    <div v-if="locations.length > 0" class="mb-2 p-2">
            <!-- Container for selected location tags -->
            <div class="flex flex-wrap gap-1 mt-2">
                <!-- Individual selected location tag -->
                <span 
                    v-for="location in locations" 
                    :key="location.name"
                    class="inline-flex items-center px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-xs rounded-full"
                >
                    <!-- Location name -->
                    {{ location.type }}: {{ location.name }}

                    <!-- Remove button for individual locations -->
                    <button 
                        @click="removeLocation(location)"
                        class="ml-1 text-blue-600 dark:text-blue-300 hover:text-blue-800 dark:hover:text-blue-100 focus:outline-none"
                        type="button"
                    >
                        ×
                    </button>
                </span>
            </div>
            
            <!-- Clear all selections button -->
            <button 
                @click="clearAll"
                class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 underline"
                type="button"
            >
                Clear all ({{ locations.length }})
            </button>
        </div>
</template>