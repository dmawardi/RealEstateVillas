<script setup lang="ts">
import { defineProps, defineEmits, defineModel } from 'vue';
import { PropertyAttachment } from '@/types';

interface Props {
    documents: PropertyAttachment[];
    editingAttachment: number | null;
    savingAttachment: number | null;
    attachmentTypes: Array<{ value: string; label: string }>;
}

defineProps<Props>();
const emit = defineEmits(['start-editing', 'cancel-editing', 'save-edit', 'delete-attachment']);

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

// Utility functions remain the same
const getFileIcon = (attachment: PropertyAttachment) => {
    if (attachment.type === 'floor_plan') return '📐';
    if (attachment.file_type === 'application/pdf') return '📄';
    if (attachment.file_type?.includes('word')) return '📝';
    return '📎';
};

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
    <div class="grid grid-cols-1 gap-4">
        <div 
            v-for="attachment in documents" 
            :key="`doc-${attachment.id}`"
            class="bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700"
        >
            <!-- Display Mode -->
            <div v-if="editingAttachment !== attachment.id" class="p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="text-3xl">{{ getFileIcon(attachment) }}</div>
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ attachment.title }}</h4>
                            <p v-if="attachment.caption" class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ attachment.caption }}</p>
                            <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500">
                                <span>{{ attachment.original_filename }}</span>
                                <span>{{ formatFileSize(attachment.file_size) }}</span>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                      :class="{
                                          'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': attachment.type === 'document',
                                          'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200': attachment.type === 'floor_plan'
                                      }">
                                    {{ attachment.type?.replace('_', ' ') || 'Document' }}
                                </span>
                                <span :class="attachment.is_visible_to_customer ? 'text-green-600' : 'text-red-600'">
                                    {{ attachment.is_visible_to_customer ? '👁️ Visible' : '🚫 Hidden' }}
                                </span>
                                <span>Order: {{ attachment.order }}</span>
                            </div>
                        </div>
                        <a 
                            :href="attachment.url" 
                            target="_blank"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm"
                        >
                            Download
                        </a>
                    </div>
                    <div class="flex items-center space-x-2 ml-4">
                        <button 
                            @click="emit('start-editing', attachment)" 
                            class="text-blue-600 hover:text-blue-800 p-1"
                            title="Edit"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                        <button 
                            @click="emit('delete-attachment', attachment.id)" 
                            class="text-red-600 hover:text-red-800 p-1"
                            title="Delete"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Edit Mode -->
            <div v-else class="p-4 bg-yellow-50 dark:bg-yellow-900/20">
                <div class="space-y-4">
                    <div class="flex items-center space-x-2 mb-4">
                        <span class="text-2xl">{{ getFileIcon(attachment) }}</span>
                        <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            Edit Document
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
        </div>
    </div>
</template>