<script setup lang="ts">
import { defineProps, defineEmits, defineModel } from 'vue';
import { PropertyAttachment } from '@/types';

interface Props {
    images: PropertyAttachment[];
    editingAttachment: number | null;
    savingAttachment: number | null;
    attachmentTypes: Array<{ value: string; label: string }>;
}

const props = defineProps<Props>();
const emit = defineEmits(['start-editing', 'cancel-editing', 'save-edit', 'delete-attachment', 'open-image-modal']);

// Use defineModel to create a two-way binding for the edit form
const editForm = defineModel<{
    title: string;
    caption: string;
    type: string;
    is_visible_to_customer: boolean;
    order: number;
}>('editForm', {
    required: true
});

// Utility functions
const formatFileSize = (bytes: number) => {
    const units = ['B', 'KB', 'MB', 'GB'];
    let size = bytes;
    let unitIndex = 0;
    
    while (size >= 1024 && unitIndex < units.length - 1) {
        size /= 1024;
        unitIndex++;
    }
    
    return `${size.toFixed(1)} ${units[unitIndex]}`;
};
</script>

<template>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <template v-for="attachment in images" :key="`img-${attachment.id}`">
            <!-- Display Mode -->
            <div v-if="editingAttachment !== attachment.id" class="group relative">
                <!-- Image Thumbnail -->
                <div class="aspect-square bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden mb-2 relative">
                    <img 
                        :src="attachment.path" 
                        :alt="attachment.title"
                        class="w-full h-full object-cover cursor-pointer"
                        @click="emit('open-image-modal', attachment)"
                    />
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-200 flex items-center justify-center">
                        <button 
                            @click="emit('open-image-modal', attachment)"
                            class="bg-white bg-opacity-80 text-gray-800 p-2 rounded-full opacity-0 group-hover:opacity-100 transition-all duration-200"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                            </svg>
                        </button>
                    </div>
                    <!-- Status badges -->
                    <div class="absolute top-2 left-2 flex flex-col space-y-1">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                            {{ attachment.type?.replace('_', ' ') || 'Image' }}
                        </span>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                              :class="attachment.is_visible_to_customer 
                                  ? 'bg-green-100 text-green-800' 
                                  : 'bg-red-100 text-red-800'">
                            {{ attachment.is_visible_to_customer ? '👁️' : '🚫' }}
                        </span>
                    </div>
                </div>

                <!-- Image Details Card -->
                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-3">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <h5 class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ attachment.title }}</h5>
                            <p v-if="attachment.caption" class="text-xs text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">{{ attachment.caption }}</p>
                            <div class="flex items-center justify-between mt-2">
                                <span class="text-xs text-gray-500">{{ formatFileSize(attachment.file_size) }}</span>
                                <span class="text-xs text-gray-500">Order: {{ attachment.order }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between mt-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                        <button 
                            @click="emit('start-editing', attachment)" 
                            class="text-blue-600 hover:text-blue-800 p-1"
                            title="Edit"
                        >
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                        <button 
                            @click="emit('delete-attachment', attachment.id)" 
                            class="text-red-600 hover:text-red-800 p-1"
                            title="Delete"
                        >
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Edit Mode - Full width overlay -->
            <div v-else class="col-span-2 md:col-span-3 lg:col-span-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-6 border border-yellow-200 dark:border-yellow-800">
                <div class="space-y-4">
                    <div class="flex items-center space-x-2 mb-4">
                        <span class="text-2xl">🖼️</span>
                        <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            Edit Image
                        </h4>
                        <span class="text-sm text-gray-500">({{ attachment.original_filename }})</span>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Title *
                            </label>
                            <input 
                                v-model="editForm.title"
                                type="text"
                                required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Enter attachment title"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Type *
                            </label>
                            <select 
                                v-model="editForm.type"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            >
                                <option 
                                    v-for="type in attachmentTypes" 
                                    :key="type.value" 
                                    :value="type.value"
                                >
                                    {{ type.label }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Display Order
                            </label>
                            <input 
                                v-model.number="editForm.order"
                                type="number"
                                min="0"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="0"
                            />
                        </div>
                        <div class="flex items-center pt-6">
                            <label class="flex items-center cursor-pointer">
                                <input 
                                    v-model="editForm.is_visible_to_customer"
                                    type="checkbox"
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                                <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Visible to customers
                                </span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Caption / Description
                        </label>
                        <textarea 
                            v-model="editForm.caption"
                            rows="3"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            placeholder="Optional description or caption for this attachment"
                        ></textarea>
                    </div>
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-600">
                        <button 
                            @click="emit('cancel-editing')"
                            :disabled="savingAttachment === attachment.id"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700"
                        >
                            Cancel
                        </button>
                        <button 
                            @click="emit('save-edit', attachment.id)"
                            :disabled="savingAttachment === attachment.id || !editForm.title.trim()"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-2"
                        >
                            <div 
                                v-if="savingAttachment === attachment.id"
                                class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"
                            ></div>
                            <span>{{ savingAttachment === attachment.id ? 'Saving...' : 'Save Changes' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>