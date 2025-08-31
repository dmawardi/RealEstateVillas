<script setup lang="ts">
// filepath: /Users/d/Web Development/projects/RealEstate/resources/js/components/properties/admin/forms/Media.vue
import { computed } from 'vue';

interface MediaFormData {
    virtual_tour_url: string | null;
    video_url: string | null;
    floor_plan: File | null;
    images: File[];
}

interface AttachmentData {
    id: number;
    path: string;
    title: string;
    type: string;
}

interface Props {
    modelValue: MediaFormData;
    existingAttachments?: AttachmentData[];
    existingFloorPlan?: string | null;
    errors?: Record<string, string | undefined>;
}

interface Emits {
    (e: 'update:modelValue', value: MediaFormData): void;
    (e: 'delete-attachment', attachmentId: number): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

// Single computed property for two-way binding
const formData = computed({
    get: () => props.modelValue,
    set: (value: MediaFormData) => emit('update:modelValue', value)
});

// Helper functions for error handling
const getFieldError = (field: string) => {
    return props.errors?.[`media.${field}`] || props.errors?.[field];
};

const hasFieldError = (field: string) => {
    return !!getFieldError(field);
};

// Simple helper function to update individual fields
const updateField = (field: keyof MediaFormData, value: any) => {
    formData.value = { ...formData.value, [field]: value };
};

// File handling functions
const handleImageUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        updateField('images', Array.from(target.files));
    }
};

const handleFloorPlanUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        updateField('floor_plan', target.files[0]);
    }
};

const removeSelectedImage = (index: number) => {
    const images = [...formData.value.images];
    images.splice(index, 1);
    updateField('images', images);
};

const removeFloorPlan = () => {
    updateField('floor_plan', null);
};

const deleteExistingAttachment = (attachmentId: number) => {
    emit('delete-attachment', attachmentId);
};

// Helper functions
const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const isValidImageType = (file: File): boolean => {
    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    return validTypes.includes(file.type);
};

const isValidFloorPlanType = (file: File): boolean => {
    const validTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
    return validTypes.includes(file.type);
};

// Computed properties
const selectedImagesPreview = computed(() => {
    return Array.from(formData.value.images).map((file, index) => ({
        file,
        index,
        url: URL.createObjectURL(file),
        name: file.name,
        size: formatFileSize(file.size),
        isValid: isValidImageType(file)
    }));
});

const floorPlanPreview = computed(() => {
    if (!formData.value.floor_plan) return null;
    return {
        file: formData.value.floor_plan,
        name: formData.value.floor_plan.name,
        size: formatFileSize(formData.value.floor_plan.size),
        isValid: isValidFloorPlanType(formData.value.floor_plan),
        isPdf: formData.value.floor_plan.type === 'application/pdf'
    };
});

const totalImages = computed(() => {
    const existing = props.existingAttachments?.length || 0;
    const newImages = formData.value.images.length;
    return existing + newImages;
});

const maxImagesReached = computed(() => totalImages.value >= 20);
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Media & Images</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Upload images, floor plans, and add virtual tour links to showcase this property
            </p>
        </div>
        
        <div class="p-6 space-y-6">
            <!-- Existing Images -->
            <div v-if="existingAttachments && existingAttachments.length > 0">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        Existing Images ({{ existingAttachments.length }})
                    </h3>
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        Click the trash icon to remove an image
                    </span>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div 
                        v-for="attachment in existingAttachments" 
                        :key="attachment.id"
                        class="relative aspect-square bg-gray-200 dark:bg-gray-700 rounded-lg overflow-hidden group"
                    >
                        <img 
                            :src="attachment.path" 
                            :alt="attachment.title"
                            class="w-full h-full object-cover"
                        />
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all duration-200 flex items-center justify-center">
                            <button
                                type="button"
                                @click="deleteExistingAttachment(attachment.id)"
                                class="opacity-0 group-hover:opacity-100 bg-red-500 hover:bg-red-600 text-white p-2 rounded-full transition-all duration-200"
                                title="Delete Image"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-2">
                            <p class="text-white text-xs truncate">{{ attachment.title }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add New Images -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label for="images" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Add New Images
                    </label>
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        {{ totalImages }}/20 images
                    </span>
                </div>
                
                <div class="space-y-4">
                    <!-- File Input -->
                    <div>
                        <input
                            id="images"
                            type="file"
                            multiple
                            accept="image/jpeg,image/jpg,image/png,image/webp"
                            @change="handleImageUpload"
                            :disabled="maxImagesReached"
                            :class="[
                                'block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold',
                                maxImagesReached 
                                    ? 'file:bg-gray-100 file:text-gray-400 cursor-not-allowed' 
                                    : 'file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100',
                                hasFieldError('images') ? 'border-red-300' : ''
                            ]"
                        />
                        <p v-if="getFieldError('images')" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ getFieldError('images') }}
                        </p>
                        <p v-else-if="maxImagesReached" class="mt-1 text-sm text-orange-600 dark:text-orange-400">
                            Maximum of 20 images reached
                        </p>
                        <p v-else class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Upload multiple images (JPEG, PNG, WebP - max 5MB each)
                        </p>
                    </div>

                    <!-- Selected Images Preview -->
                    <div v-if="selectedImagesPreview.length > 0" class="space-y-4">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Selected Images ({{ selectedImagesPreview.length }})
                        </h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div 
                                v-for="preview in selectedImagesPreview" 
                                :key="preview.index"
                                class="relative aspect-square bg-gray-200 dark:bg-gray-700 rounded-lg overflow-hidden group"
                            >
                                <img 
                                    :src="preview.url" 
                                    :alt="preview.name"
                                    class="w-full h-full object-cover"
                                />
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all duration-200 flex items-center justify-center">
                                    <button
                                        type="button"
                                        @click="removeSelectedImage(preview.index)"
                                        class="opacity-0 group-hover:opacity-100 bg-red-500 hover:bg-red-600 text-white p-2 rounded-full transition-all duration-200"
                                        title="Remove Image"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-2">
                                    <p class="text-white text-xs truncate">{{ preview.name }}</p>
                                    <p class="text-gray-300 text-xs">{{ preview.size }}</p>
                                    <p v-if="!preview.isValid" class="text-red-400 text-xs">Invalid file type</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Floor Plan Section -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Floor Plan</h3>
                
                <!-- Existing Floor Plan -->
                <div v-if="existingFloorPlan && !formData.floor_plan" class="mb-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    Current Floor Plan
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ existingFloorPlan.split('/').pop() }}
                                </p>
                            </div>
                        </div>
                        <a 
                            :href="existingFloorPlan" 
                            target="_blank"
                            class="text-blue-600 hover:text-blue-700 text-sm font-medium"
                        >
                            View
                        </a>
                    </div>
                </div>

                <!-- Upload New Floor Plan -->
                <div>
                    <label for="floor_plan" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ existingFloorPlan ? 'Replace Floor Plan' : 'Upload Floor Plan' }}
                    </label>
                    <input
                        id="floor_plan"
                        type="file"
                        accept=".pdf,.jpg,.jpeg,.png"
                        @change="handleFloorPlanUpload"
                        :class="[
                            'block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100',
                            hasFieldError('floor_plan') ? 'border-red-300' : ''
                        ]"
                    />
                    <p v-if="getFieldError('floor_plan')" class="mt-1 text-sm text-red-600 dark:text-red-400">
                        {{ getFieldError('floor_plan') }}
                    </p>
                    <p v-else class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        PDF, JPEG, PNG files accepted (max 10MB)
                    </p>
                </div>

                <!-- New Floor Plan Preview -->
                <div v-if="floorPlanPreview" class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <svg v-if="floorPlanPreview.isPdf" class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <svg v-else class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ floorPlanPreview.name }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ floorPlanPreview.size }}
                                    <span v-if="!floorPlanPreview.isValid" class="text-red-500 ml-2">Invalid file type</span>
                                </p>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="removeFloorPlan"
                            class="text-red-600 hover:text-red-700 text-sm font-medium"
                        >
                            Remove
                        </button>
                    </div>
                </div>
            </div>

            <!-- Virtual Tour and Video URLs -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Virtual Content</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="virtual_tour_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Virtual Tour URL
                        </label>
                        <input
                            id="virtual_tour_url"
                            :value="formData.virtual_tour_url || ''"
                            @input="updateField('virtual_tour_url', ($event.target as HTMLInputElement).value || null)"
                            type="url"
                            :class="[
                                'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100',
                                hasFieldError('virtual_tour_url') 
                                    ? 'border-red-300 focus:border-red-500 focus:ring-red-500' 
                                    : 'border-gray-300 dark:border-gray-600'
                            ]"
                            placeholder="https://my.matterport.com/show/?m=..."
                        />
                        <p v-if="getFieldError('virtual_tour_url')" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ getFieldError('virtual_tour_url') }}
                        </p>
                        <p v-else class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Matterport, Kuula, or other 360° tour link
                        </p>
                    </div>

                    <div>
                        <label for="video_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Video URL
                        </label>
                        <input
                            id="video_url"
                            :value="formData.video_url || ''"
                            @input="updateField('video_url', ($event.target as HTMLInputElement).value || null)"
                            type="url"
                            :class="[
                                'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100',
                                hasFieldError('video_url') 
                                    ? 'border-red-300 focus:border-red-500 focus:ring-red-500' 
                                    : 'border-gray-300 dark:border-gray-600'
                            ]"
                            placeholder="https://youtube.com/watch?v=..."
                        />
                        <p v-if="getFieldError('video_url')" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ getFieldError('video_url') }}
                        </p>
                        <p v-else class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            YouTube, Vimeo, or other video platform link
                        </p>
                    </div>
                </div>
            </div>

            <!-- Media Guidelines -->
            <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-orange-800 dark:text-orange-300">
                            Media Best Practices
                        </h3>
                        <div class="mt-2 text-sm text-orange-700 dark:text-orange-400">
                            <ul class="list-disc list-inside space-y-1">
                                <li><strong>Images:</strong> Use high-resolution photos with good lighting (minimum 1200px width)</li>
                                <li><strong>Order matters:</strong> First image will be used as the main property photo</li>
                                <li><strong>Floor plans:</strong> Clear, detailed drawings help buyers understand the layout</li>
                                <li><strong>Virtual tours:</strong> 360° tours significantly increase engagement and inquiries</li>
                                <li><strong>File sizes:</strong> Optimize images for web to ensure fast loading times</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>