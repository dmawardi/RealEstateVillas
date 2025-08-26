<script setup lang="ts">
import { computed } from "vue";

interface Props {
  lat: number | undefined;       // latitude
  lng: number | undefined;       // longitude
  zoom?: number;     // optional zoom level (default 12)
  width?: number;    // map image width in px (default 600)
  height?: number;   // map image height in px (default 300)
  apiKey: string;    // Google Maps Static API key
  markerColor?: string;  // marker color (default 'red')
  markerSize?: 'tiny' | 'mid' | 'small';  // marker size (default 'mid')
  markerLabel?: string;  // optional marker label (A-Z, 0-9)
}

const props = defineProps<Props>();

// Default values
const zoom = computed(() => props.zoom ?? 12);
const width = computed(() => props.width ?? 600);
const height = computed(() => props.height ?? 300);
const markerColor = computed(() => props.markerColor ?? 'red');
const markerSize = computed(() => props.markerSize ?? 'mid');

// Build static map URL with marker
const staticMapUrl = computed(() => {
  const baseUrl = `https://maps.googleapis.com/maps/api/staticmap`;
  const center = `center=${props.lat},${props.lng}`;
  const zoomParam = `zoom=${zoom.value}`;
  const size = `size=${width.value}x${height.value}`;
  
  // Build marker parameter
  let marker = `markers=color:${markerColor.value}|size:${markerSize.value}`;
  if (props.markerLabel) {
    marker += `|label:${props.markerLabel}`;
  }
  marker += `|${props.lat},${props.lng}`;
  
  const key = `key=${props.apiKey}`;
  
  return `${baseUrl}?${center}&${zoomParam}&${size}&${marker}&${key}`;
});

// Build Google Maps direct link
const googleMapsUrl = computed(() => {
  return `https://www.google.com/maps?q=${props.lat},${props.lng}&z=${zoom.value}`;
});
</script>

<template>
  <a :href="googleMapsUrl" target="_blank" rel="noopener noreferrer">
    <img
      :src="staticMapUrl"
      alt="Google Map preview"
      :width="width"
      :height="height"
      style="border-radius: 8px; cursor: pointer;"
    />
  </a>
</template>