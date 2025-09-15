<script setup lang="ts">
import { ref, computed } from 'vue';
import { PropertyAttachment } from '@/types';
import { api } from '@/services/api';

interface Props {
    propertyId: number;
    attachments: PropertyAttachment[];
}

const props = defineProps<Props>();
const emit = defineEmits(['attachment-updated', 'attachment-deleted', 'attachments-reordered']);

// Component state
const editingAttachment = ref<number | null>(null);
const editForm = ref({
    title: '',
    caption: '',
    type: '',
    is_visible_to_customer: true
});

// Image modal state
const showImageModal = ref(false);
const selectedImage = ref<PropertyAttachment | null>(null);
const currentImageIndex = ref(0);

// Upload state - Enhanced
const uploading = ref(false);
const selectedFiles = ref<FileList | null>(null);
const uploadProgress = ref(0);
const uploadStatus = ref<'idle' | 'uploading' | 'success' | 'error'>('idle');

// Computed categories
const imageAttachments = computed(() => 
    props.attachments
        .filter(att => att.type === 'image')
        .sort((a, b) => a.order - b.order)
);

const documentAttachments = computed(() => 
    props.attachments
        .filter(att => att.type === 'document')
        .sort((a, b) => a.order - b.order)
);

const floorPlanAttachments = computed(() => 
    props.attachments
        .filter(att => att.type === 'floor_plan')
        .sort((a, b) => a.order - b.order)
);

// Computed for upload button text and state
const uploadButtonText = computed(() => {
    if (uploading.value) {
        return `Uploading... ${uploadProgress.value}%`;
    }
    if (selectedFiles.value && selectedFiles.value.length > 0) {
        const count = selectedFiles.value.length;
        return `Upload ${count} File${count > 1 ? 's' : ''}`;
    }
    return 'Add Files';
});

const uploadButtonClass = computed(() => {
    const baseClasses = "px-4 py-2 rounded-lg cursor-pointer transition-colors flex items-center space-x-2";
    
    if (uploading.value) {
        return `${baseClasses} bg-yellow-600 hover:bg-yellow-700 text-white opacity-75 cursor-not-allowed`;
    }
    if (selectedFiles.value && selectedFiles.value.length > 0) {
        return `${baseClasses} bg-green-600 hover:bg-green-700 text-white`;
    }
    return `${baseClasses} bg-blue-600 hover:bg-blue-700 text-white`;
});

// Edit functions
const startEditing = (attachment: PropertyAttachment) => {
    editingAttachment.value = attachment.id;
    editForm.value = {
        title: attachment.title,
        caption: attachment.caption || '',
        type: attachment.type,
        is_visible_to_customer: attachment.is_visible_to_customer
    };
};

const cancelEditing = () => {
    editingAttachment.value = null;
    editForm.value = { title: '', caption: '', type: '', is_visible_to_customer: true };
};

const saveEdit = async (attachmentId: number) => {
    try {
        // Fixed: Use the proper API pattern like LocationAutocomplete
        await api.attachments.updateAttachment(attachmentId, editForm.value, {
            onSuccess: (response: any) => {
                if (response.success) {
                    emit('attachment-updated', response.data.attachment);
                    editingAttachment.value = null;
                }
            },
            onError: (errors: any) => {
                console.error('Failed to update attachment:', errors);
            }
        });
    } catch (error) {
        console.error('Failed to update attachment:', error);
    }
};

// Delete function
const deleteAttachment = async (attachmentId: number) => {
    if (!confirm('Are you sure you want to delete this attachment?')) return;
    
    try {
        await api.attachments.deleteAttachment(attachmentId, {
            onSuccess: (response: any) => {
                if (response.success) {
                    emit('attachment-deleted', attachmentId);
                }
            },
            onError: (errors: any) => {
                console.error('Failed to delete attachment:', errors);
            }
        });
    } catch (error) {
        console.error('Failed to delete attachment:', error);
    }
};

// Image modal functions
const openImageModal = (attachment: PropertyAttachment) => {
    selectedImage.value = attachment;
    currentImageIndex.value = imageAttachments.value.findIndex(img => img.id === attachment.id);
    showImageModal.value = true;
};

const closeImageModal = () => {
    showImageModal.value = false;
    selectedImage.value = null;
};

const nextImage = () => {
    if (currentImageIndex.value < imageAttachments.value.length - 1) {
        currentImageIndex.value++;
        selectedImage.value = imageAttachments.value[currentImageIndex.value];
    }
};

const previousImage = () => {
    if (currentImageIndex.value > 0) {
        currentImageIndex.value--;
        selectedImage.value = imageAttachments.value[currentImageIndex.value];
    }
};

// File selection handler - NEW
const handleFileSelection = (event: Event) => {
    const target = event.target as HTMLInputElement;
    selectedFiles.value = target.files;
    uploadStatus.value = 'idle';
    uploadProgress.value = 0;
    
    // Log selected files for debugging
    if (selectedFiles.value) {
        console.log(`Selected ${selectedFiles.value.length} file(s):`, 
            Array.from(selectedFiles.value).map(f => ({ name: f.name, size: f.size, type: f.type }))
        );
    }
};

// File upload handler - Enhanced
const handleFileUpload = async () => {
    if (!selectedFiles.value || selectedFiles.value.length === 0) return;
    
    uploading.value = true;
    uploadStatus.value = 'uploading';
    uploadProgress.value = 0;
    
    try {
        const formData = new FormData();
        Array.from(selectedFiles.value).forEach(file => {
            formData.append('files[]', file);
        });
        formData.append('is_visible_to_customer', 'true');
        
        // Simulate progress for better UX
        const progressInterval = setInterval(() => {
            if (uploadProgress.value < 90) {
                uploadProgress.value += Math.random() * 10;
            }
        }, 200);
        
        // Fixed: Use the proper API pattern like LocationAutocomplete
        await api.attachments.createAttachment(props.propertyId, formData, {
            onSuccess: (response: any) => {
                clearInterval(progressInterval);
                uploadProgress.value = 100;
                
                if (response.success) {
                    uploadStatus.value = 'success';
                    emit('attachments-reordered'); // Refresh the attachments
                    
                    // Reset form after a brief delay
                    setTimeout(() => {
                        const fileInput = document.getElementById('file-upload') as HTMLInputElement;
                        if (fileInput) fileInput.value = '';
                        selectedFiles.value = null;
                        uploadStatus.value = 'idle';
                        uploadProgress.value = 0;
                    }, 2000);
                } else {
                    uploadStatus.value = 'error';
                }
            },
            onError: (errors: any) => {
                clearInterval(progressInterval);
                uploadStatus.value = 'error';
                console.error('Failed to upload attachments:', errors);
            }
        });
    } catch (error) {
        uploadStatus.value = 'error';
        console.error('Failed to upload attachments:', error);
    } finally {
        uploading.value = false;
    }
};

// Clear selection handler - NEW
const clearSelection = () => {
    const fileInput = document.getElementById('file-upload') as HTMLInputElement;
    if (fileInput) fileInput.value = '';
    selectedFiles.value = null;
    uploadStatus.value = 'idle';
    uploadProgress.value = 0;
};

// File type icons
const getFileIcon = (attachment: PropertyAttachment) => {
    if (attachment.type === 'image') return '🖼️';
    if (attachment.file_type === 'application/pdf') return '📄';
    if (attachment.file_type?.includes('word')) return '📝';
    return '📎';
};

// File size formatting
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
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <!-- Header with upload -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    Property Attachments
                    <span class="text-sm font-normal text-gray-500 ml-2">({{ attachments.length }})</span>
                </h2>
                <div class="flex items-center space-x-3">
                    <!-- File Input -->
                    <input
                        type="file"
                        multiple
                        accept="image/*,.pdf,.doc,.docx"
                        @change="handleFileSelection"
                        class="hidden"
                        id="file-upload"
                    />
                    
                    <!-- Select Files Button -->
                    <label
                        for="file-upload"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg cursor-pointer transition-colors flex items-center space-x-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                        </svg>
                        <span>Select Files</span>
                    </label>

                    <!-- Upload/Status Button -->
                    <button
                        v-if="selectedFiles && selectedFiles.length > 0"
                        @click="handleFileUpload"
                        :disabled="uploading"
                        :class="uploadButtonClass"
                    >
                        <!-- Upload Icon or Spinner -->
                        <svg 
                            v-if="!uploading && uploadStatus !== 'success'" 
                            class="w-4 h-4" 
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        
                        <!-- Success Icon -->
                        <svg 
                            v-else-if="uploadStatus === 'success'" 
                            class="w-4 h-4 text-green-600" 
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        
                        <!-- Loading Spinner -->
                        <div 
                            v-else 
                            class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"
                        ></div>
                        
                        <span>{{ uploadButtonText }}</span>
                    </button>

                    <!-- Clear Selection Button -->
                    <button
                        v-if="selectedFiles && selectedFiles.length > 0 && !uploading"
                        @click="clearSelection"
                        class="text-gray-500 hover:text-gray-700 p-2"
                        title="Clear selection"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- File Selection Preview -->
            <div v-if="selectedFiles && selectedFiles.length > 0" class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h4 class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-2">
                            Selected Files ({{ selectedFiles.length }})
                        </h4>
                        <div class="space-y-1">
                            <div 
                                v-for="(file, index) in Array.from(selectedFiles)" 
                                :key="index"
                                class="flex items-center justify-between text-xs text-blue-800 dark:text-blue-200"
                            >
                                <span class="truncate mr-2">{{ file.name }}</span>
                                <span class="whitespace-nowrap">{{ formatFileSize(file.size) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Progress Bar -->
                <div v-if="uploading" class="mt-3">
                    <div class="flex items-center justify-between text-xs text-blue-800 dark:text-blue-200 mb-1">
                        <span>Uploading...</span>
                        <span>{{ Math.round(uploadProgress) }}%</span>
                    </div>
                    <div class="w-full bg-blue-200 dark:bg-blue-800 rounded-full h-2">
                        <div 
                            class="bg-blue-600 dark:bg-blue-400 h-2 rounded-full transition-all duration-300"
                            :style="{ width: `${uploadProgress}%` }"
                        ></div>
                    </div>
                </div>

                <!-- Success Message -->
                <div v-if="uploadStatus === 'success'" class="mt-2 text-xs text-green-700 dark:text-green-300 flex items-center">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Upload completed successfully!
                </div>

                <!-- Error Message -->
                <div v-if="uploadStatus === 'error'" class="mt-2 text-xs text-red-700 dark:text-red-300 flex items-center">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Upload failed. Please try again.
                </div>
            </div>
        </div>

        <div class="p-6 space-y-8">
            <!-- Images Section -->
            <div v-if="imageAttachments.length > 0" class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-medium text-gray-900 dark:text-gray-100 flex items-center">
                        🖼️ Images
                        <span class="ml-2 text-sm text-gray-500">({{ imageAttachments.length }})</span>
                    </h3>
                </div>

                <!-- Main Image -->
                <div v-if="imageAttachments.length > 0" class="space-y-3">
                    <div class="aspect-video bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden relative group">
                        <img 
                            :src="imageAttachments[0].path" 
                            :alt="imageAttachments[0].title"
                            class="w-full h-full object-cover cursor-pointer"
                            @click="openImageModal(imageAttachments[0])"
                        />
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-200 flex items-center justify-center">
                            <button 
                                @click="openImageModal(imageAttachments[0])"
                                class="bg-white bg-opacity-80 text-gray-800 px-3 py-2 rounded-lg opacity-0 group-hover:opacity-100 transition-all duration-200"
                            >
                                View Full Size
                            </button>
                        </div>
                        <div class="absolute top-2 right-2">
                            <span class="bg-black bg-opacity-50 text-white px-2 py-1 rounded text-xs">Main Image</span>
                        </div>
                    </div>
                    
                    <!-- Main Image Edit Card -->
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                        <div v-if="editingAttachment !== imageAttachments[0].id" class="flex items-center justify-between">
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ imageAttachments[0].title }}</h4>
                                <p v-if="imageAttachments[0].caption" class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ imageAttachments[0].caption }}</p>
                                <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500">
                                    <span>{{ formatFileSize(imageAttachments[0].file_size) }}</span>
                                    <span :class="imageAttachments[0].is_visible_to_customer ? 'text-green-600' : 'text-red-600'">
                                        {{ imageAttachments[0].is_visible_to_customer ? 'Visible' : 'Hidden' }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <!-- <button @click="toggleVisibility(imageAttachments[0].id)" class="text-gray-500 hover:text-gray-700 p-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="imageAttachments[0].is_visible_to_customer ? 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z' : 'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21'" />
                                    </svg>
                                </button> -->
                                <button @click="startEditing(imageAttachments[0])" class="text-blue-600 hover:text-blue-800 p-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button @click="deleteAttachment(imageAttachments[0].id)" class="text-red-600 hover:text-red-800 p-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Edit Form -->
                        <div v-else class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title</label>
                                <input 
                                    v-model="editForm.title"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Caption</label>
                                <textarea 
                                    v-model="editForm.caption"
                                    rows="2"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                ></textarea>
                            </div>
                            <div>
                                <label class="flex items-center">
                                    <input 
                                        v-model="editForm.is_visible_to_customer"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Visible to customers</span>
                                </label>
                            </div>
                            <div class="flex space-x-2 pt-2">
                                <button 
                                    @click="saveEdit(imageAttachments[0].id)"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm"
                                >
                                    Save
                                </button>
                                <button 
                                    @click="cancelEditing"
                                    class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-3 py-1 rounded text-sm"
                                >
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Images Grid -->
                <div v-if="imageAttachments.length > 1" class="space-y-4">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Additional Images</h4>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        <div 
                            v-for="(attachment) in imageAttachments.slice(1)" 
                            :key="attachment.id"
                            class="group relative"
                        >
                            <!-- Image Thumbnail -->
                            <div class="aspect-square bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden mb-2">
                                <img 
                                    :src="attachment.path" 
                                    :alt="attachment.title"
                                    class="w-full h-full object-cover cursor-pointer"
                                    @click="openImageModal(attachment)"
                                />
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-200 flex items-center justify-center">
                                    <button 
                                        @click="openImageModal(attachment)"
                                        class="bg-white bg-opacity-80 text-gray-800 p-2 rounded-full opacity-0 group-hover:opacity-100 transition-all duration-200"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Image Details Card -->
                            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-3">
                                <div v-if="editingAttachment !== attachment.id">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1 min-w-0">
                                            <h5 class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ attachment.title }}</h5>
                                            <p v-if="attachment.caption" class="text-xs text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">{{ attachment.caption }}</p>
                                            <div class="flex items-center justify-between mt-2">
                                                <span class="text-xs text-gray-500">{{ formatFileSize(attachment.file_size) }}</span>
                                                <span :class="attachment.is_visible_to_customer ? 'text-green-600' : 'text-red-600'" class="text-xs">
                                                    {{ attachment.is_visible_to_customer ? '👁️' : '🚫' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between mt-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                                        <!-- <button @click="toggleVisibility(attachment.id)" class="text-gray-500 hover:text-gray-700 p-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="attachment.is_visible_to_customer ? 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z' : 'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21'" />
                                            </svg>
                                        </button> -->
                                        <button @click="startEditing(attachment)" class="text-blue-600 hover:text-blue-800 p-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button @click="deleteAttachment(attachment.id)" class="text-red-600 hover:text-red-800 p-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Edit Form for Additional Images -->
                                <div v-else class="space-y-2">
                                    <input 
                                        v-model="editForm.title"
                                        type="text"
                                        placeholder="Title"
                                        class="w-full text-sm rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    />
                                    <textarea 
                                        v-model="editForm.caption"
                                        placeholder="Caption"
                                        rows="2"
                                        class="w-full text-sm rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    ></textarea>
                                    <label class="flex items-center text-xs">
                                        <input 
                                            v-model="editForm.is_visible_to_customer"
                                            type="checkbox"
                                            class="rounded border-gray-300 text-blue-600"
                                        />
                                        <span class="ml-1">Visible</span>
                                    </label>
                                    <div class="flex space-x-1">
                                        <button 
                                            @click="saveEdit(attachment.id)"
                                            class="bg-blue-600 text-white px-2 py-1 rounded text-xs"
                                        >
                                            Save
                                        </button>
                                        <button 
                                            @click="cancelEditing"
                                            class="bg-gray-300 text-gray-700 px-2 py-1 rounded text-xs"
                                        >
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documents Section -->
            <div v-if="documentAttachments.length > 0" class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-medium text-gray-900 dark:text-gray-100 flex items-center">
                        📄 Documents
                        <span class="ml-2 text-sm text-gray-500">({{ documentAttachments.length }})</span>
                    </h3>
                </div>

                <div class="space-y-3">
                    <div 
                        v-for="attachment in documentAttachments" 
                        :key="attachment.id"
                        class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700"
                    >
                        <div v-if="editingAttachment !== attachment.id" class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="text-2xl">{{ getFileIcon(attachment) }}</div>
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ attachment.title }}</h4>
                                    <p v-if="attachment.caption" class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ attachment.caption }}</p>
                                    <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500">
                                        <span>{{ attachment.original_filename }}</span>
                                        <span>{{ formatFileSize(attachment.file_size) }}</span>
                                        <span :class="attachment.is_visible_to_customer ? 'text-green-600' : 'text-red-600'">
                                            {{ attachment.is_visible_to_customer ? 'Visible' : 'Hidden' }}
                                        </span>
                                    </div>
                                </div>
                                <a 
                                    :href="attachment.path" 
                                    target="_blank"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm"
                                >
                                    Download
                                </a>
                            </div>
                            <div class="flex items-center space-x-2 ml-4">
                                <!-- <button @click="toggleVisibility(attachment.id)" class="text-gray-500 hover:text-gray-700 p-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="attachment.is_visible_to_customer ? 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z' : 'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21'" />
                                    </svg>
                                </button> -->
                                <button @click="startEditing(attachment)" class="text-blue-600 hover:text-blue-800 p-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button @click="deleteAttachment(attachment.id)" class="text-red-600 hover:text-red-800 p-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Edit Form for Documents -->
                        <div v-else class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title</label>
                                <input 
                                    v-model="editForm.title"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Caption</label>
                                <textarea 
                                    v-model="editForm.caption"
                                    rows="2"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                ></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                                <select 
                                    v-model="editForm.type"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                >
                                    <option value="document">Document</option>
                                    <option value="floor_plan">Floor Plan</option>
                                </select>
                            </div>
                            <div>
                                <label class="flex items-center">
                                    <input 
                                        v-model="editForm.is_visible_to_customer"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Visible to customers</span>
                                </label>
                            </div>
                            <div class="flex space-x-2 pt-2">
                                <button 
                                    @click="saveEdit(attachment.id)"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm"
                                >
                                    Save
                                </button>
                                <button 
                                    @click="cancelEditing"
                                    class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-3 py-1 rounded text-sm"
                                >
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Floor Plans Section -->
            <div v-if="floorPlanAttachments.length > 0" class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-medium text-gray-900 dark:text-gray-100 flex items-center">
                        📐 Floor Plans
                        <span class="ml-2 text-sm text-gray-500">({{ floorPlanAttachments.length }})</span>
                    </h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div 
                        v-for="attachment in floorPlanAttachments" 
                        :key="attachment.id"
                        class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700"
                    >
                        <!-- Floor Plan Preview -->
                        <div class="aspect-video bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden mb-3">
                            <img 
                                v-if="attachment.file_type?.startsWith('image/')"
                                :src="attachment.path" 
                                :alt="attachment.title"
                                class="w-full h-full object-contain cursor-pointer"
                                @click="openImageModal(attachment)"
                            />
                            <div v-else class="flex items-center justify-center h-full">
                                <div class="text-center">
                                    <div class="text-4xl mb-2">📐</div>
                                    <p class="text-sm text-gray-500">{{ attachment.original_filename }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Floor Plan Details -->
                        <div v-if="editingAttachment !== attachment.id">
                            <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-1">{{ attachment.title }}</h4>
                            <p v-if="attachment.caption" class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ attachment.caption }}</p>
                            <div class="flex items-center justify-between text-xs text-gray-500 mb-3">
                                <span>{{ formatFileSize(attachment.file_size) }}</span>
                                <span :class="attachment.is_visible_to_customer ? 'text-green-600' : 'text-red-600'">
                                    {{ attachment.is_visible_to_customer ? 'Visible' : 'Hidden' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <a 
                                    :href="attachment.path" 
                                    target="_blank"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm"
                                >
                                    View/Download
                                </a>
                                <div class="flex items-center space-x-2">
                                    <!-- <button @click="toggleVisibility(attachment.id)" class="text-gray-500 hover:text-gray-700 p-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="attachment.is_visible_to_customer ? 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z' : 'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21'" />
                                        </svg>
                                    </button> -->
                                    <button @click="startEditing(attachment)" class="text-blue-600 hover:text-blue-800 p-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button @click="deleteAttachment(attachment.id)" class="text-red-600 hover:text-red-800 p-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Edit Form for Floor Plans -->
                        <div v-else class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title</label>
                                <input 
                                    v-model="editForm.title"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Caption</label>
                                <textarea 
                                    v-model="editForm.caption"
                                    rows="2"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                ></textarea>
                            </div>
                            <div>
                                <label class="flex items-center">
                                    <input 
                                        v-model="editForm.is_visible_to_customer"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Visible to customers</span>
                                </label>
                            </div>
                            <div class="flex space-x-2 pt-2">
                                <button 
                                    @click="saveEdit(attachment.id)"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm"
                                >
                                    Save
                                </button>
                                <button 
                                    @click="cancelEditing"
                                    class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-3 py-1 rounded text-sm"
                                >
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- No Attachments Message -->
            <div v-if="attachments.length === 0" class="text-center py-12">
                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">No attachments</h3>
                <p class="mt-2 text-gray-500">Upload images, documents, or floor plans to get started.</p>
            </div>
        </div>

        <!-- Image Modal -->
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
                            <p class="text-xs opacity-75">{{ currentImageIndex + 1 }} of {{ imageAttachments.length }}</p>
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
                        v-if="currentImageIndex < imageAttachments.length - 1"
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
                </div>
            </div>
        </Teleport>
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