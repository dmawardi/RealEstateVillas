<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue';
import type { Property } from '@/types';
import { setOptions, importLibrary } from "@googlemaps/js-api-loader"

interface Props {
    properties: Property[];
    filters?: any;
    googleMapsIds?: string;
    googleMapsApiKey?: string;
}

const props = defineProps<Props>();

const map = ref<google.maps.Map | null>(null);
const mapElement = ref<HTMLElement>();
const center = { lat: -8.4095, lng: 115.1889 }; // Centered on Bali

async function initMap(): Promise<void> {
    if (!mapElement.value) {
        console.error('Map container not found');
        return;
    }

    try {
        // Set loader options.
        const apiKey = props.googleMapsApiKey || (window as any).GOOGLE_MAPS_CONFIG?.apiKey || '';
        
        if (!apiKey) {
            console.error('Google Maps API key is missing');
            return;
        }
        
        setOptions({
            key: apiKey,
            v: 'weekly',
        });

        // Load the Maps library.
        const { Map } = (await importLibrary('maps')) as google.maps.MapsLibrary;

        // Set map options.
        const mapOptions = {
            center: center,
            zoom: 10,
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: false,
            zoomControl: true,
        };

        // Create the map and assign it to the ref
        map.value = new Map(mapElement.value, mapOptions);
        
        console.log('Google Maps loaded successfully');
    } catch (error) {
        console.error('Error loading Google Maps:', error);
    }
}

// Initialize map when component mounts
onMounted(async () => {
    await nextTick();
    await initMap();
});
</script>

<template>
    <div class="relative w-full h-full">
        <div ref="mapElement" class="w-full h-full min-h-[400px]"></div>
        
        <!-- Loading state -->
        <div v-if="!map" class="absolute inset-0 flex items-center justify-center bg-gray-100 rounded-lg">
            <div class="text-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-2"></div>
                <p class="text-gray-600">Loading map...</p>
            </div>
        </div>
        
        <!-- No properties message -->
        <div v-if="map && props.properties.length === 0" class="absolute top-4 left-4 bg-white p-3 rounded-lg shadow">
            <p class="text-gray-600">No properties to display on map</p>
        </div>
        
        <!-- Map controls -->
        <div class="absolute bottom-4 right-4 bg-white rounded-lg shadow-lg p-2">
            <div class="flex items-center space-x-2 text-xs text-gray-600">
                <div class="w-3 h-3 bg-blue-600 rounded"></div>
                <span>Property Location</span>
            </div>
        </div>
    </div>
</template>

<style>
/* Map container will inherit height from parent */
</style>