<script setup lang="ts">
import { ref, computed } from 'vue';
import { PropertyAttachment } from '@/types';

interface Props {
    attachments: PropertyAttachment[] | null;
}

const props = defineProps<Props>();
const attachments = props.attachments || [];

// Track the currently selected image index
const selectedImageIndex = ref(0);

// Select an image by index
const selectImage = (index: number) => {
    selectedImageIndex.value = index;
};

// Get the currently selected image
const selectedImage = computed(() => {
    return attachments.length > 0 ? attachments[selectedImageIndex.value] : null;
});
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <!-- Main Image Display -->
        <div class="aspect-video bg-gray-200 dark:bg-gray-700 flex items-center justify-center relative">
            <div v-if="selectedImage" class="w-full h-full">
                <img 
                    :src="selectedImage.path" 
                    :alt="selectedImage.title"
                    class="w-full h-full object-cover"
                />
                
                <!-- Image Counter -->
                <div class="absolute top-4 right-4 bg-black bg-opacity-50 text-white px-3 py-1 rounded-full text-sm">
                    {{ selectedImageIndex + 1 }} / {{ attachments.length }}
                </div>
            </div>
            
            <!-- No Images Placeholder -->
            <div v-else class="text-gray-500 dark:text-gray-400">
                <svg class="w-16 h-16 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
                </svg>
                <p>No images available</p>
            </div>
        </div>

        <!-- Image Thumbnails -->
        <div v-if="attachments.length > 1" class="p-4 bg-gray-50 dark:bg-gray-900">
            <div class="flex gap-2 overflow-x-auto scrollbar-hide">
                <button
                    v-for="(attachment, index) in attachments"
                    :key="attachment.id"
                    @click="selectImage(index)"
                    :class="[
                        'flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden border-2 transition-all duration-200 hover:opacity-80',
                        selectedImageIndex === index 
                            ? 'border-blue-500 ring-2 ring-blue-200 dark:ring-blue-800' 
                            : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500'
                    ]"
                >
                    <img 
                        :src="attachment.path" 
                        :alt="attachment.title"
                        class="w-full h-full object-cover"
                        loading="lazy"
                    />
                </button>
            </div>
        </div>

        <!-- Single Image Info -->
        <div v-if="attachments.length === 1" class="p-4 bg-gray-50 dark:bg-gray-900">
            <p class="text-sm text-gray-600 dark:text-gray-400 text-center">
                1 image available
            </p>
        </div>
    </div>
</template>

<style scoped>
/* Hide scrollbar for thumbnail container */
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
</style>