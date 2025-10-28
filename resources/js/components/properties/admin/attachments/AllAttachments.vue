<script setup lang="ts">
import { defineProps, defineEmits, defineModel } from 'vue';
import { PropertyAttachment } from '@/types';

interface Props {
    attachments: PropertyAttachment[];
    editingAttachment: number | null;
    savingAttachment: number | null;
    attachmentTypes: Array<{ value: string; label: string }>;
}

defineProps<Props>();
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
const getFileIcon = (attachment: PropertyAttachment) => {
    if (attachment.type === 'image') return '🖼️';
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

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <div class="overflow-x-auto shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
        <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-600">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Preview
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Details
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Type & Status
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        File Info
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Dates
                    </th>
                    <th scope="col" class="relative px-6 py-3">
                        <span class="sr-only">Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <template v-for="attachment in attachments" :key="`all-${attachment.id}`">
                    <!-- Display Row -->
                    <tr v-if="editingAttachment !== attachment.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <!-- Preview Column -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-16 w-16 flex-shrink-0">
                                    <img 
                                        v-if="attachment.type === 'image'"
                                        :src="attachment.url" 
                                        :alt="attachment.title"
                                        class="h-16 w-16 rounded-lg object-cover cursor-pointer border border-gray-200 dark:border-gray-600"
                                        @click="emit('open-image-modal', attachment)"
                                    />
                                    <div 
                                        v-else
                                        class="h-16 w-16 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center border border-gray-200 dark:border-gray-600"
                                    >
                                        <span class="text-2xl">{{ getFileIcon(attachment) }}</span>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Details Column -->
                        <td class="px-6 py-4">
                            <div class="text-sm">
                                <div class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ attachment.title || 'Untitled' }}
                                </div>
                                <div v-if="attachment.caption" class="text-gray-500 dark:text-gray-400 mt-1">
                                    {{ attachment.caption }}
                                </div>
                                <div class="text-gray-400 dark:text-gray-500 text-xs mt-1">
                                    Order: {{ attachment.order }}
                                </div>
                            </div>
                        </td>

                        <!-- Type & Status Column -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col space-y-1">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                      :class="{
                                          'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': attachment.type === 'image',
                                          'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': attachment.type === 'document',
                                          'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200': attachment.type === 'floor_plan'
                                      }">
                                    {{ attachment.type?.replace('_', ' ') || 'Document' }}
                                </span>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                      :class="attachment.is_visible_to_customer 
                                          ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' 
                                          : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'">
                                    {{ attachment.is_visible_to_customer ? '👁️ Visible' : '🚫 Hidden' }}
                                </span>
                            </div>
                        </td>

                        <!-- File Info Column -->
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                            <div class="space-y-1">
                                <div class="truncate max-w-xs" :title="attachment.original_filename">
                                    {{ attachment.original_filename }}
                                </div>
                                <div>{{ formatFileSize(attachment.file_size) }}</div>
                                <div class="text-xs">{{ attachment.file_type }}</div>
                            </div>
                        </td>

                        <!-- Dates Column -->
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                            <div class="space-y-1">
                                <div class="text-xs">
                                    <div class="font-medium">Created:</div>
                                    <div>{{ formatDate(attachment.created_at) }}</div>
                                </div>
                                <div v-if="attachment.updated_at !== attachment.created_at" class="text-xs">
                                    <div class="font-medium">Updated:</div>
                                    <div>{{ formatDate(attachment.updated_at) }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- Actions Column -->
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                <a 
                                    v-if="attachment.type !== 'image'"
                                    :href="attachment.url" 
                                    target="_blank"
                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                    title="Download/View"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </a>
                                <button 
                                    @click="emit('start-editing', attachment)" 
                                    class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                    title="Edit"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button 
                                    @click="emit('delete-attachment', attachment.id)" 
                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                    title="Delete"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Edit Row -->
                    <tr v-else class="bg-yellow-50 dark:bg-yellow-900/20">
                        <td colspan="6" class="px-6 py-4">
                            <div class="space-y-4">
                                <div class="flex items-center space-x-2 mb-4">
                                    <span class="text-2xl">{{ getFileIcon(attachment) }}</span>
                                    <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                        Edit Attachment
                                    </h4>
                                    <span class="text-sm text-gray-500">({{ attachment.original_filename }})</span>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Title -->
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

                                    <!-- Type -->
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

                                    <!-- Order -->
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

                                    <!-- Visibility -->
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

                                <!-- Caption (full width) -->
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

                                <!-- Action Buttons -->
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
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</template>