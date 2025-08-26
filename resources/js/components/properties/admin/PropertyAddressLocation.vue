<script setup lang="ts">
import StaticMap from '@/components/ui/map/StaticMap.vue';
import { Property } from '@/types';

interface Props {
    property: Property;
    mapApiKey: string;
}

const { property, mapApiKey } = defineProps<Props>();
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Address & Location</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Street Address</span>
                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ property.street_number ? `${property.street_number} ` : '' }}{{ property.street_name }}
                        </p>
                    </div>
                    <div v-if="property.village">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Village</span>
                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ property.village }}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">District</span>
                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ property.district }}</p>
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Regency</span>
                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ property.regency }}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">State</span>
                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ property.state }}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Postcode</span>
                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ property.postcode }}</p>
                    </div>
                </div>
            </div>

            <!-- Coordinates Info (Admin only) -->
            <div v-if="property.latitude && property.longitude" class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Latitude</span>
                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ property.latitude }}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Longitude</span>
                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ property.longitude }}</p>
                    </div>
                </div>
            </div>

            <!-- Map -->
            <div v-if="property.latitude && property.longitude" class="mt-6">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Location Map</span>
                <div class="mt-2">
                    <StaticMap 
                        :lat="Number(property.latitude)" 
                        :lng="Number(property.longitude)" 
                        :apiKey="mapApiKey" 
                        :width="600" 
                        :height="300" 
                        :zoom="15"
                        markerColor="red"
                        markerLabel="P"
                    />
                </div>
                
                <!-- Map Actions -->
                <div class="mt-3 flex space-x-3">
                    <a 
                        :href="`https://www.google.com/maps?q=${property.latitude},${property.longitude}&z=15`"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                    >
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        Open in Google Maps
                    </a>
                    <button 
                        @click="copyCoordinates"
                        class="inline-flex items-center text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300"
                    >
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        Copy Coordinates
                    </button>
                </div>
            </div>

            <!-- No Location Message -->
            <div v-else class="mt-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                <div class="flex">
                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                            No GPS coordinates set
                        </h3>
                        <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                            Add latitude and longitude coordinates to display the property location on a map.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
export default {
    methods: {
        copyCoordinates() {
            const coords = `${this.property.latitude}, ${this.property.longitude}`;
            navigator.clipboard.writeText(coords).then(() => {
                // You could add a toast notification here
                alert('Coordinates copied to clipboard!');
            }).catch(err => {
                console.error('Failed to copy coordinates: ', err);
            });
        }
    }
}
</script>