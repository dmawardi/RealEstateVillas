<script setup lang="ts">
import { ref } from 'vue';
import { PropertyAttachment } from '@/types';

interface Props {
    attachments: PropertyAttachment[];
}

const { attachments } = defineProps<Props>();

// Image modal state
const showImageModal = ref(false);
const selectedImage = ref<PropertyAttachment | null>(null);
const currentImageIndex = ref(0);

// Image modal functions
const openImageModal = (attachment: PropertyAttachment, index: number) => {
    selectedImage.value = attachment;
    currentImageIndex.value = index;
    showImageModal.value = true;
};

const closeImageModal = () => {
    showImageModal.value = false;
    selectedImage.value = null;
};

const nextImage = () => {
    if (currentImageIndex.value < attachments.length - 1) {
        currentImageIndex.value++;
        selectedImage.value = attachments[currentImageIndex.value];
    }
};

const previousImage = () => {
    if (currentImageIndex.value > 0) {
        currentImageIndex.value--;
        selectedImage.value = attachments[currentImageIndex.value];
    }
};

// Keyboard navigation
const handleKeydown = (event: KeyboardEvent) => {
    if (!showImageModal.value) return;
    
    switch (event.key) {
        case 'Escape':
            closeImageModal();
            break;
        case 'ArrowRight':
            nextImage();
            break;
        case 'ArrowLeft':
            previousImage();
            break;
    }
};

// Add keyboard event listener
if (typeof window !== 'undefined') {
    window.addEventListener('keydown', handleKeydown);
}
</script>

<template>
    <div v-if="attachments.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    Property Images
                    <span class="text-sm font-normal text-gray-500 ml-2">({{ attachments.length }})</span>
                </h2>
                <div class="flex space-x-2">
                    <button class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <div class="p-6">
            <!-- Main Image Display -->
            <div v-if="attachments.length > 0" class="mb-6">
                <div 
                    class="aspect-video bg-gray-200 dark:bg-gray-700 rounded-lg overflow-hidden cursor-pointer hover:opacity-90 transition-opacity"
                    @click="openImageModal(attachments[0], 0)"
                >
                    <img 
                        :src="attachments[0].path" 
                        :alt="attachments[0].title"
                        class="w-full h-full object-cover"
                    />
                </div>
                <div class="mt-2 text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ attachments[0].title || 'Main Property Image' }}
                    </p>
                </div>
            </div>

            <!-- Thumbnail Grid -->
            <div v-if="attachments.length > 1" class="grid grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-3">
                <div 
                    v-for="(attachment, index) in attachments.slice(1)" 
                    :key="attachment.id"
                    class="aspect-square bg-gray-200 dark:bg-gray-700 rounded-lg overflow-hidden cursor-pointer hover:opacity-75 transition-opacity relative group"
                    @click="openImageModal(attachment, index + 1)"
                >
                    <img 
                        :src="attachment.path" 
                        :alt="attachment.title"
                        class="w-full h-full object-cover"
                    />
                    <!-- Image overlay on hover -->
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-200 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- No Images Message -->
            <div v-else class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="mt-2 text-sm text-gray-500">No images available</p>
            </div>
        </div>

        <!-- Enhanced Image Modal -->
        <Teleport to="body">
            <div 
                v-if="showImageModal" 
                class="fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50"
                @click="closeImageModal"
            >
                <div class="relative max-w-full max-h-full p-4" @click.stop>
                    <!-- Main Image -->
                    <img 
                        v-if="selectedImage"
                        :src="selectedImage.path" 
                        :alt="selectedImage.title"
                        class="max-w-full max-h-full object-contain rounded-lg"
                    />
                    
                    <!-- Image Info -->
                    <div class="absolute bottom-4 left-4 right-4 text-center">
                        <div class="bg-black bg-opacity-50 text-white px-4 py-2 rounded-lg">
                            <p class="text-sm font-medium">{{ selectedImage?.title || 'Property Image' }}</p>
                            <p class="text-xs opacity-75">{{ currentImageIndex + 1 }} of {{ attachments.length }}</p>
                        </div>
                    </div>

                    <!-- Navigation Arrows -->
                    <button 
                        v-if="currentImageIndex > 0"
                        @click="previousImage"
                        class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white p-3 rounded-full hover:bg-opacity-75 transition-all"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    
                    <button 
                        v-if="currentImageIndex < attachments.length - 1"
                        @click="nextImage"
                        class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white p-3 rounded-full hover:bg-opacity-75 transition-all"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>

                    <!-- Close Button -->
                    <button 
                        @click="closeImageModal"
                        class="absolute top-4 right-4 bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-75 transition-all"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <!-- Thumbnail Strip -->
                    <div class="absolute bottom-20 left-1/2 transform -translate-x-1/2">
                        <div class="flex space-x-2 bg-black bg-opacity-50 p-2 rounded-lg max-w-md overflow-x-auto">
                            <button
                                v-for="(attachment, index) in attachments"
                                :key="attachment.id"
                                @click="selectedImage = attachment; currentImageIndex = index"
                                :class="[
                                    'flex-shrink-0 w-12 h-12 rounded overflow-hidden border-2 transition-all',
                                    currentImageIndex === index 
                                        ? 'border-white' 
                                        : 'border-transparent opacity-60 hover:opacity-80'
                                ]"
                            >
                                <img 
                                    :src="attachment.path" 
                                    :alt="attachment.title"
                                    class="w-full h-full object-cover"
                                />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>