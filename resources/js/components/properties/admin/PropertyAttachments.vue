<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Property, PropertyAttachment } from '@/types';
import Documents from '@/components/properties/admin/attachments/Documents.vue';
import Images from '@/components/properties/admin/attachments/Images.vue';
import AllAttachments from '@/components/properties/admin/attachments/AllAttachments.vue';

interface Props {
    property: Property;
}


const { property } = defineProps<Props>();
const emit = defineEmits(['attachment-updated', 'attachment-deleted', 'attachments-reordered']);

// Computed categories
const attachments = computed(() => property.attachments ?? []);

// Component state
const editingAttachment = ref<number | null>(null);
const savingAttachment = ref<number | null>(null);
const editForm = ref({
    title: '',
    caption: '',
    type: '',
    is_visible_to_customer: true,
    order: 0
});

// Collapsible sections state
const sectionsCollapsed = ref({
    allAttachments: false,
    documents: false,
    images: false
});

// Upload state
const uploading = ref(false);
const selectedFiles = ref<FileList | null>(null);
const uploadProgress = ref(0);
const uploadStatus = ref<'idle' | 'uploading' | 'success' | 'error'>('idle');

// Image modal state
const showImageModal = ref(false);
const selectedImage = ref<PropertyAttachment | null>(null);
const currentImageIndex = ref(0);



const imageAttachments = computed(() => 
    attachments.value
        .filter(att => att.type === 'image')
        .sort((a, b) => a.order - b.order)
);

const documentAttachments = computed(() => 
    attachments.value
        .filter(att => att.type === 'document' || att.type === 'floor_plan')
        .sort((a, b) => a.order - b.order)
);

// All attachment types for editing
const attachmentTypes = [
    { value: 'image', label: 'Image' },
    { value: 'document', label: 'Document' },
    { value: 'floor_plan', label: 'Floor Plan' }
];

// Upload button computed properties
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

// Section toggle functions
const toggleSection = (section: 'allAttachments' | 'documents' | 'images') => {
    sectionsCollapsed.value[section] = !sectionsCollapsed.value[section];
};

// Edit functions
const startEditing = (attachment: PropertyAttachment) => {
    editingAttachment.value = attachment.id;
    editForm.value = {
        title: attachment.title || '',
        caption: attachment.caption || '',
        type: attachment.type || 'document',
        is_visible_to_customer: attachment.is_visible_to_customer ?? true,
        order: attachment.order || 0
    };
};

const cancelEditing = () => {
    editingAttachment.value = null;
    savingAttachment.value = null;
    editForm.value = { 
        title: '', 
        caption: '', 
        type: '', 
        is_visible_to_customer: true, 
        order: 0 
    };
};

const saveEdit = async (attachmentId: number) => {
    savingAttachment.value = attachmentId;
    
    try {
        router.put(route('admin.attachments.update', attachmentId), editForm.value, {
            preserveScroll: true,
            onSuccess: () => {
                emit('attachment-updated', { id: attachmentId, ...editForm.value });
                editingAttachment.value = null;
                console.log('✅ Attachment updated successfully');
            },
            onError: (errors: any) => {
                console.error('❌ Failed to update attachment:', errors);
                
                if (errors.message) {
                    alert(`Failed to update attachment: ${errors.message}`);
                } else {
                    const errorMessages = Object.values(errors).flat();
                    alert(`Validation errors: ${errorMessages.join(', ')}`);
                }
            },
            onFinish: () => {
                savingAttachment.value = null;
            }
        });
    } catch (error) {
        console.error('❌ Unexpected error updating attachment:', error);
        alert('An unexpected error occurred. Please try again.');
        savingAttachment.value = null;
    }
};

// Delete function
const deleteAttachment = async (attachmentId: number) => {
    if (!confirm('Are you sure you want to delete this attachment? This action cannot be undone.')) return;
    
    try {
        router.delete(route('admin.attachments.destroy', attachmentId), {
            preserveScroll: true,
            onSuccess: () => {
                emit('attachment-deleted', attachmentId);
                console.log('✅ Attachment deleted successfully');
            },
            onError: (errors: any) => {
                console.error('❌ Failed to delete attachment:', errors);
                alert('Failed to delete attachment. Please try again.');
            }
        });
    } catch (error) {
        console.error('❌ Failed to delete attachment:', error);
        alert('An unexpected error occurred while deleting.');
    }
};

// Image modal functions
const openImageModal = (attachment: PropertyAttachment) => {
    if (attachment.type === 'image') {
        selectedImage.value = attachment;
        currentImageIndex.value = imageAttachments.value.findIndex(img => img.id === attachment.id);
        showImageModal.value = true;
    }
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

// File upload functions
const handleFileSelection = (event: Event) => {
    const target = event.target as HTMLInputElement;
    selectedFiles.value = target.files;
    uploadStatus.value = 'idle';
    uploadProgress.value = 0;
    
    if (selectedFiles.value) {
        console.log(`Selected ${selectedFiles.value.length} file(s):`, 
            Array.from(selectedFiles.value).map(f => ({ name: f.name, size: f.size, type: f.type }))
        );
    }
};

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
        formData.append('is_visible_to_customer', '0');
        
        const progressInterval = setInterval(() => {
            if (uploadProgress.value < 90) {
                uploadProgress.value += Math.random() * 10;
            }
        }, 200);
        
        router.post(route('admin.properties.attachments.store', property.slug), formData, {
            forceFormData: true,
            preserveScroll: true,
            onProgress: () => {
                // Update progress if browser supports it
                uploadProgress.value = Math.min(95, uploadProgress.value + 5);
            },
            onSuccess: () => {
                clearInterval(progressInterval);
                uploadProgress.value = 100;
                uploadStatus.value = 'success';
                emit('attachments-reordered');
                
                setTimeout(() => {
                    const fileInput = document.getElementById('file-upload') as HTMLInputElement;
                    if (fileInput) fileInput.value = '';
                    selectedFiles.value = null;
                    uploadStatus.value = 'idle';
                    uploadProgress.value = 0;
                }, 2000);
            },
            onError: (errors: any) => {
                clearInterval(progressInterval);
                uploadStatus.value = 'error';
                console.error('Failed to upload attachments:', errors);
                
                if (errors.message) {
                    alert(`Upload failed: ${errors.message}`);
                } else if (errors.files) {
                    alert(`Upload failed: ${errors.files[0]}`);
                } else {
                    alert('Upload failed. Please try again.');
                }
            },
            onFinish: () => {
                uploading.value = false;
                clearInterval(progressInterval);
            }
        });
    } catch (error) {
        uploadStatus.value = 'error';
        uploading.value = false;
        console.error('Failed to upload attachments:', error);
        alert('An unexpected error occurred during upload.');
    }
};

const clearSelection = () => {
    const fileInput = document.getElementById('file-upload') as HTMLInputElement;
    if (fileInput) fileInput.value = '';
    selectedFiles.value = null;
    uploadStatus.value = 'idle';
    uploadProgress.value = 0;
};

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
                        accept="image/*,.pdf,.doc,.docx,.webp"
                        @change="handleFileSelection"
                        class="hidden"
                        id="file-upload"
                        title="Maximum file size: 10MB per file"
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
                        <svg 
                            v-if="!uploading && uploadStatus !== 'success'" 
                            class="w-4 h-4" 
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        
                        <svg 
                            v-else-if="uploadStatus === 'success'" 
                            class="w-4 h-4 text-green-600" 
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        
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

                <!-- Success/Error Messages -->
                <div v-if="uploadStatus === 'success'" class="mt-2 text-xs text-green-700 dark:text-green-300 flex items-center">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Upload completed successfully!
                </div>

                <div v-if="uploadStatus === 'error'" class="mt-2 text-xs text-red-700 dark:text-red-300 flex items-center">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Upload failed. Please try again.
                </div>
            </div>

            <!-- File Size Limit Notice -->
            <div class="text-xs text-gray-500 mt-2">
                Maximum file size: 10MB per file. Supported formats: Images (JPG, PNG, WebP), PDF, DOC, DOCX
            </div>
        </div>

        <div class="p-6 space-y-8">
            <!-- All Attachments Section (Collapsible) -->
            <div v-if="attachments.length > 0" class="space-y-4">
                <div class="flex items-center justify-between">
                    <button
                        @click="toggleSection('allAttachments')"
                        class="flex items-center space-x-2 text-base font-medium text-gray-900 dark:text-gray-100 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                    >
                        <svg 
                            class="w-5 h-5 transition-transform duration-200"
                            :class="{ 'rotate-90': !sectionsCollapsed.allAttachments }"
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <span>All Attachments ({{ attachments.length }})</span>
                    </button>
                </div>

                <AllAttachments 
                    v-show="!sectionsCollapsed.allAttachments"
                    :attachments="attachments"
                    :editing-attachment="editingAttachment"
                    :saving-attachment="savingAttachment"
                    :attachment-types="attachmentTypes"
                    v-model:edit-form="editForm"
                    @start-editing="startEditing"
                    @cancel-editing="cancelEditing"
                    @save-edit="saveEdit"
                    @delete-attachment="deleteAttachment"
                    @open-image-modal="openImageModal"
                />
            </div>

            <!-- Documents Section (Collapsible) -->
            <div v-if="documentAttachments.length > 0" class="space-y-4">
                <div class="flex items-center justify-between">
                    <button
                        @click="toggleSection('documents')"
                        class="flex items-center space-x-2 text-base font-medium text-gray-900 dark:text-gray-100 hover:text-green-600 dark:hover:text-green-400 transition-colors"
                    >
                        <svg 
                            class="w-5 h-5 transition-transform duration-200"
                            :class="{ 'rotate-90': !sectionsCollapsed.documents }"
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <span>📄 Documents & Floor Plans ({{ documentAttachments.length }})</span>
                    </button>
                </div>

                <Documents
                    v-show="!sectionsCollapsed.documents"
                    :documents="documentAttachments"
                    :editing-attachment="editingAttachment"
                    :saving-attachment="savingAttachment"
                    :attachment-types="attachmentTypes"
                    v-model:edit-form="editForm"
                    @start-editing="startEditing"
                    @cancel-editing="cancelEditing"
                    @save-edit="saveEdit"
                    @delete-attachment="deleteAttachment"
                />
            </div>

            <!-- Images Section (Collapsible) -->
            <div v-if="imageAttachments.length > 0" class="space-y-4">
                <div class="flex items-center justify-between">
                    <button
                        @click="toggleSection('images')"
                        class="flex items-center space-x-2 text-base font-medium text-gray-900 dark:text-gray-100 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                    >
                        <svg 
                            class="w-5 h-5 transition-transform duration-200"
                            :class="{ 'rotate-90': !sectionsCollapsed.images }"
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <span>🖼️ Images ({{ imageAttachments.length }})</span>
                    </button>
                </div>

                <Images
                    v-show="!sectionsCollapsed.images"
                    :images="imageAttachments"
                    :editing-attachment="editingAttachment"
                    :saving-attachment="savingAttachment"
                    :attachment-types="attachmentTypes"
                    v-model:edit-form="editForm"
                    @start-editing="startEditing"
                    @cancel-editing="cancelEditing"
                    @save-edit="saveEdit"
                    @delete-attachment="deleteAttachment"
                    @open-image-modal="openImageModal"
                />
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
                    <img 
                        v-if="selectedImage"
                        :src="selectedImage.url" 
                        :alt="selectedImage.title"
                        class="max-w-full max-h-full object-contain rounded-lg"
                    />
                    
                    <div class="absolute bottom-4 left-4 right-4 text-center">
                        <div class="bg-black bg-opacity-50 text-white px-4 py-2 rounded-lg">
                            <p class="text-sm font-medium">{{ selectedImage?.title || 'Property Image' }}</p>
                            <p class="text-xs opacity-75">{{ currentImageIndex + 1 }} of {{ imageAttachments.length }}</p>
                        </div>
                    </div>

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