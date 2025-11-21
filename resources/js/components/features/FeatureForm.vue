<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import { Feature } from '@/types';

interface Props {
    feature?: Feature; // Optional - only passed when editing
    submitRoute: string; // Route name for form submission
    cancelRoute: string; // Route to go back to
    method?: 'post' | 'put' | 'patch'; // HTTP method for submission
}

const { feature, submitRoute, cancelRoute, method = 'post' } = defineProps<Props>();

// Determine if we're editing or creating
const isEditing = computed(() => !!feature);


const categories = [
    { value: 'amenity', label: 'Amenities' },
    { value: 'safety', label: 'Safety Features' },
    { value: 'comfort', label: 'Comfort Features' },
    { value: 'entertainment', label: 'Entertainment' },
    { value: 'outdoor', label: 'Outdoor Features' },
    { value: 'kitchen', label: 'Kitchen Features' },
    { value: 'bathroom', label: 'Bathroom Features' },
    { value: 'accessibility', label: 'Accessibility Features' },
    { value: 'security', label: 'Security Features' },
];

// Common icons for features
const commonIcons = [
    '🏠', '🚿', '🛏️', '🚗', '📶', '🔒', '🏊', '🎮', '🍽️', '☕', '🌡️', 
    '💡', '🖥️', '🎵', '🌿', '🔥', '❄️', '📺', '🎯', '🛋️', '🚪', '🪟'
];

// Form setup
const form = useForm({
    name: feature?.name || '',
    slug: feature?.slug || '',
    description: feature?.description || '',
    category: feature?.category || 'amenity',
    icon: feature?.icon || '',
    is_quantifiable: feature?.is_quantifiable || false,
    is_active: feature?.is_active ?? true, // Default to true for new features
});

// Auto-generate slug from name
const generateSlug = () => {
    if (form.name && !isEditing.value) {
        form.slug = form.name
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim();
    }
};

// Custom icon input
const showCustomIconInput = ref(false);
const customIcon = ref('');

const selectIcon = (icon: string) => {
    form.icon = icon;
    showCustomIconInput.value = false;
    customIcon.value = '';
};

const useCustomIcon = () => {
    if (customIcon.value.trim()) {
        form.icon = customIcon.value.trim();
        showCustomIconInput.value = false;
        customIcon.value = '';
    }
};

// Form submission
const submit = () => {
    if (isEditing.value && feature) {
        // Update existing feature
        form.transform((data) => ({
            ...data,
            _method: method, // Laravel method spoofing for PUT/PATCH
        })).post(route(submitRoute, feature.id), {
            preserveScroll: true,
        });
    } else {
        // Create new feature
        form.post(route(submitRoute), {
            preserveScroll: true,
        });
    }
};

// Validation helpers
const slugError = computed(() => {
    if (form.errors.slug) return form.errors.slug;
    if (form.slug && !/^[a-z0-9-]+$/.test(form.slug)) {
        return 'Slug can only contain lowercase letters, numbers, and hyphens';
    }
    return '';
});

// Auto-focus name field on mount
const nameInput = ref<HTMLInputElement>();
onMounted(() => {
    nameInput.value?.focus();
});
</script>

<template>
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 lg:p-8">
            <!-- Header -->
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                    {{ isEditing ? 'Edit Feature' : 'Create New Feature' }}
                </h2>
                <p class="text-gray-600 dark:text-gray-400">
                    {{ isEditing ? 'Update the feature details below.' : 'Add a new feature to the property system.' }}
                </p>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Name and Slug Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Feature Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            ref="nameInput"
                            id="name"
                            v-model="form.name"
                            type="text"
                            placeholder="e.g., Swimming Pool, WiFi, Air Conditioning"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.name }"
                            @input="generateSlug"
                            required
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <!-- Slug -->
                    <div>
                        <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Slug <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="slug"
                            v-model="form.slug"
                            type="text"
                            placeholder="e.g., swimming-pool, wifi, air-conditioning"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': slugError }"
                            required
                        />
                        <p v-if="slugError" class="mt-1 text-sm text-red-600">{{ slugError }}</p>
                        <p v-else class="mt-1 text-xs text-gray-500">Used in URLs and must be unique</p>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Description
                    </label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="3"
                        placeholder="Optional description of the feature..."
                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.description }"
                    />
                    <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
                </div>

                <!-- Category and Icon Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Category -->
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Category <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="category"
                            v-model="form.category"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.category }"
                            required
                        >
                            <option v-for="category in categories" :key="category.value" :value="category.value">
                                {{ category.label }}
                            </option>
                        </select>
                        <p v-if="form.errors.category" class="mt-1 text-sm text-red-600">{{ form.errors.category }}</p>
                    </div>

                    <!-- Icon Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Icon
                        </label>
                        
                        <!-- Current Icon Display -->
                        <div class="flex items-center space-x-4 mb-3">
                            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                <div
                                    v-if="form.icon"
                                    class="flex-shrink-0 w-5 h-5 text-gray-400 dark:text-gray-500"
                                    v-html="form.icon"
                                />
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Current icon</p>
                                <button
                                    type="button"
                                    @click="form.icon = ''"
                                    class="text-xs text-red-600 hover:text-red-700"
                                >
                                    Clear icon
                                </button>
                            </div>
                        </div>

                        <!-- Icon Selection Grid -->
                        <div class="grid grid-cols-8 gap-2 mb-3">
                            <button
                                v-for="icon in commonIcons"
                                :key="icon"
                                type="button"
                                @click="selectIcon(icon)"
                                class="w-8 h-8 flex items-center justify-center rounded border hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                :class="{ 'bg-blue-100 border-blue-500': form.icon === icon }"
                            >
                                {{ icon }}
                            </button>
                        </div>

                        <!-- Custom Icon Input -->
                        <div class="space-y-2">
                            <button
                                type="button"
                                @click="showCustomIconInput = !showCustomIconInput"
                                class="text-sm text-blue-600 hover:text-blue-700"
                            >
                                {{ showCustomIconInput ? 'Cancel' : 'Use custom icon' }}
                            </button>
                            
                            <div v-if="showCustomIconInput" class="flex space-x-2">
                                <input
                                    v-model="customIcon"
                                    type="text"
                                    placeholder="Enter emoji or symbol"
                                    class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                                <button
                                    type="button"
                                    @click="useCustomIcon"
                                    class="px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                                >
                                    Use
                                </button>
                            </div>
                        </div>

                        <p v-if="form.errors.icon" class="mt-1 text-sm text-red-600">{{ form.errors.icon }}</p>
                    </div>
                </div>

                <!-- Feature Options -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Options</h3>
                    
                    <div class="space-y-3">
                        <!-- Is Quantifiable -->
                        <div class="flex items-start">
                            <input
                                id="is_quantifiable"
                                v-model="form.is_quantifiable"
                                type="checkbox"
                                class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            />
                            <div class="ml-3">
                                <label for="is_quantifiable" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Quantifiable Feature
                                </label>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Check if this feature can have a quantity (e.g., "3 bedrooms", "2 bathrooms")
                                </p>
                            </div>
                        </div>

                        <!-- Is Active -->
                        <div class="flex items-start">
                            <input
                                id="is_active"
                                v-model="form.is_active"
                                type="checkbox"
                                class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            />
                            <div class="ml-3">
                                <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Active Feature
                                </label>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Inactive features won't appear in property listings
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                    <Link
                        :href="route(cancelRoute)"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                    >
                        Cancel
                    </Link>
                    
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ form.processing ? 'Saving...' : (isEditing ? 'Update Feature' : 'Create Feature') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>