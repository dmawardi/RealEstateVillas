<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue';
import type { Property } from '@/types';
import { setOptions, importLibrary } from "@googlemaps/js-api-loader"
import { MarkerClusterer } from "@googlemaps/markerclusterer";
import { formatListingType } from '@/utils';

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
const infoWindow = ref<google.maps.InfoWindow | null>(null);

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

        // Load the Maps and Marker libraries.
        const { Map } = (await importLibrary('maps')) as google.maps.MapsLibrary;

        // Set map options.
        const mapOptions = {
            center: center,
            zoom: 10,
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: false,
            zoomControl: true,
            mapId: '', // Remove mapId since we're using regular markers
            // Add options to reduce WebGL warnings
            gestureHandling: 'cooperative',
            backgroundColor: '#f5f5f5',
            clickableIcons: false,
            // Additional options to minimize rendering issues
            disableDoubleClickZoom: false,
            keyboardShortcuts: false,
            // Disable some advanced rendering features that may cause WebGL issues
            tilt: 0,
            rotateControl: false,
            mapTypeId: 'roadmap',
            restriction: {
                latLngBounds: {
                    north: 6.0,
                    south: -11.0,
                    east: 141.0,
                    west: 95.0
                }
            }
        };

        // Create the map and assign it to the ref
        map.value = new Map(mapElement.value, mapOptions);

        // Initialize info window
        infoWindow.value = new google.maps.InfoWindow({
            content: "",
            disableAutoPan: true,
        });
        // Create an array of alphabetical characters used to label the markers.
        const labels = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

        // Filter properties with valid coordinates
        const validProperties = props.properties.filter(property => property.latitude && property.longitude);

        // Add some markers to the map.
        const markers = validProperties
            .map((property, i) => {
            const label = labels[i % labels.length];
            
            // Debug: Log the coordinates being parsed
            const lat = parseFloat(property.latitude!.toString());
            const lng = parseFloat(property.longitude!.toString());
            
            // Skip if invalid coordinates
            if (isNaN(lat) || isNaN(lng)) {
                console.warn(`Skipping property ${property.title}: invalid coordinates`);
                return null;
            }
            
            // Use regular google.maps.Marker
            const marker = new google.maps.Marker({
                position: { lat, lng },
                map: map.value,
                title: property.title,
                label: {
                    text: label,
                    color: 'white',
                    fontSize: '12px',
                    fontWeight: 'bold'
                },
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 20,
                    fillColor: '#3B82F6',
                    fillOpacity: 1,
                    strokeColor: '#1E40AF',
                    strokeWeight: 2
                }
            });

            // markers can only be keyboard focusable when they have click listeners
            // open info window when marker is clicked
            marker.addListener("click", () => {

            // If pricing string found
            // Init empty pricingHtml
            let pricingHtml = '';

            if (property.pricing_string !== undefined) {
                // Split pricing by | to better format
                const pricingParts = property.pricing_string.split('|');
                const nightlyRate = pricingParts[0].trim();
                const additionalRate = pricingParts[1]?.trim() || '';

                // Highlight discount rate within parentheses by making it green
                const discountRate = additionalRate.split('(');
                const formattedDiscountRate = `<span style="color: green;">(${discountRate[1]}</span>`;

                if (additionalRate != '') {
                    pricingHtml = `${nightlyRate}<br>${discountRate[0]} ${formattedDiscountRate}`;
                } else {
                    pricingHtml = `${nightlyRate}`;
                }
            }

            const pricing = pricingHtml == '' ? 'Price not available' : pricingHtml;
            // Set content and open info window
            infoWindow.value!.setContent(`${property.attachments?.[0] ? `<img src="${property.attachments[0].url || property.attachments[0].path}" style="width: 200px; height: 120px; object-fit: cover; border-radius: 4px; margin-bottom: 8px;" alt="${property.title}"><br>` : ''}${property.title}<br><strong>${formatListingType(property.listing_type)}</strong><br>${pricing}<br><a style="color: blue; text-decoration: underline;" href="/properties/${property.slug}" target="_blank">View Details</a>`);
            infoWindow.value!.open(map.value, marker);
            });
            return marker;
        })
        // Filter out any null markers due to invalid coordinates
        .filter((marker): marker is google.maps.Marker => marker !== null);

        // Add a marker clusterer to manage the markers.
        new MarkerClusterer({ markers, map: map.value });

        // Fit map bounds to show all markers
        if (markers.length > 0) {
            const bounds = new google.maps.LatLngBounds();
            validProperties.forEach(property => {
                const lat = parseFloat(property.latitude!.toString());
                const lng = parseFloat(property.longitude!.toString());
                bounds.extend({ lat, lng });
            });
            
            map.value.fitBounds(bounds);
            
            // Ensure minimum zoom level
            google.maps.event.addListenerOnce(map.value, 'bounds_changed', () => {
                if (map.value!.getZoom()! > 15) {
                    map.value!.setZoom(15);
                }
            });
        }
        
        console.log(`Created ${markers.length} markers on the map`);
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
    </div>
</template>

<style>
/* Map container will inherit height from parent */
</style>