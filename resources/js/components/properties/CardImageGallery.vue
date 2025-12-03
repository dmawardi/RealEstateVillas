<script setup lang="ts">
import { DetailedPricing, Property } from '@/types';
import { ref } from 'vue';

interface Props {
    property: Property
    detailedPricing?: DetailedPricing | null;
}
const { property, detailedPricing } = defineProps<Props>();

// Image carousel state
const currentImageIndex = ref(0);
const isHovered = ref(false);

// Image navigation methods
const nextImage = () => {
    if (property.attachments && property.attachments.length > 0) {
        currentImageIndex.value = (currentImageIndex.value + 1) % property.attachments.length;
    }
};

const previousImage = () => {
    if (property.attachments && property.attachments.length > 0) {
        currentImageIndex.value = currentImageIndex.value === 0 
            ? property.attachments.length - 1 
            : currentImageIndex.value - 1;
    }
};

const toggleHover = (hovered: boolean) => {
    isHovered.value = hovered;
};
</script>

<template>
    <div class="aspect-video bg-gray-200 dark:bg-gray-700 relative" @mouseenter="toggleHover(true)"
        @mouseleave="toggleHover(false)">

                <div v-if="property.attachments && property.attachments.length > 0" class="w-full h-full relative">
                <!-- Main Image -->
                <img 
                    :src="property.attachments[currentImageIndex].path" 
                    :alt="property.title"
                    class="w-full h-full object-cover transition-opacity duration-300"
                />
                
                <!-- Navigation Buttons (show on hover if more than 1 image) -->
                <nav v-if="property.attachments.length > 1">
                <!-- Previous Button -->
                <button
                    @click.prevent.stop="previousImage"
                    :class="[
                        'absolute left-2 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-70 hover:bg-opacity-90 text-white p-2 rounded-full transition-all duration-200 z-40',
                        isHovered ? 'opacity-100' : 'opacity-0'
                    ]"
                    aria-label="Previous image"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                
                <!-- Next Button -->
                <button
                    @click.prevent.stop="nextImage"
                    :class="[
                        'absolute right-2 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-70 hover:bg-opacity-90 text-white p-2 rounded-full transition-all duration-200 z-40',
                        isHovered ? 'opacity-100' : 'opacity-0'
                    ]"
                    aria-label="Next image"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
                <!-- Image Dots -->
                <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2">
                    <button v-for="(_, index) in property.attachments" 
                            :key="index"
                            @click.prevent.stop="currentImageIndex = index"
                            :class="[
                                'w-2 h-2 rounded-full transition-colors',
                                index === currentImageIndex 
                                    ? 'bg-white' 
                                    : 'bg-white/50 hover:bg-white/75'
                            ]"
                            :aria-label="`Go to image ${index + 1}`">
                    </button>
                </div>
            </nav>
                </div>
                <div v-else class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400">
                    <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
                    </svg>
                </div>
            <!-- Discount Badge -->
            <div v-if="detailedPricing?.monthly.hasDiscount && detailedPricing.monthly.discount > 15" 
                 class="absolute bottom-3 left-3">
                <span class="bg-red-500 text-white px-2 py-1 rounded text-xs font-medium">
                    {{ detailedPricing.monthly.discount }}% Monthly Discount
                </span>
            </div>
    </div>
</template>