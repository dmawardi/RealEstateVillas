<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import type { PageProps as InertiaPageProps } from '@inertiajs/core';

// Extend the base Inertia PageProps
interface PageProps extends InertiaPageProps {
    flash?: {
        success?: string;
        error?: string;
        warning?: string;
        info?: string;
    };
    errors?: Record<string, string[]>;
}

const page = usePage<PageProps>();
// Add console logging for debugging
console.log('🔥 FlashMessages component loaded');
console.log('🔥 Current page props:', page.props);
console.log('🔥 Flash data:', page.props.flash);

// Reactive state for showing messages
const showSuccess = ref(false);
const showError = ref(false);
const showWarning = ref(false);
const showInfo = ref(false);

// Computed properties for flash messages
const successMessage = computed(() => page.props.flash?.success || '');
const errorMessage = computed(() => page.props.flash?.error || '');
const warningMessage = computed(() => page.props.flash?.warning || '');
const infoMessage = computed(() => page.props.flash?.info || '');

// Computed for validation errors - convert to user-friendly message
const validationErrors = computed(() => {
    if (!page.props.errors) return '';
    
    const errors = page.props.errors;
    const errorMessages = Object.values(errors).flat();
    
    if (errorMessages.length === 0) return '';
    
    // Return first error or combine multiple errors
    return errorMessages.length === 1 
        ? errorMessages[0] 
        : `${errorMessages.length} validation errors occurred. Please check the form.`;
});

// Watch for changes in page props and show messages
watch(() => page.props.flash, (newFlash) => {
    if (newFlash?.success) {
        showSuccess.value = true;
        setTimeout(() => showSuccess.value = false, 5000);
    }
    if (newFlash?.error) {
        showError.value = true;
        setTimeout(() => showError.value = false, 7000);
    }
    if (newFlash?.warning) {
        showWarning.value = true;
        setTimeout(() => showWarning.value = false, 6000);
    }
    if (newFlash?.info) {
        showInfo.value = true;
        setTimeout(() => showInfo.value = false, 5000);
    }
}, { immediate: true, deep: true });

// Watch for validation errors
watch(() => page.props.errors, (newErrors) => {
    if (newErrors && Object.keys(newErrors).length > 0) {
        showError.value = true;
        setTimeout(() => showError.value = false, 7000);
    }
}, { immediate: true, deep: true });

// Manual close functions
const closeSuccess = () => showSuccess.value = false;
const closeError = () => showError.value = false;
const closeWarning = () => showWarning.value = false;
const closeInfo = () => showInfo.value = false;

// Initialize on mount
onMounted(() => {
    // Check for existing flash messages
    if (page.props.flash?.success) showSuccess.value = true;
    if (page.props.flash?.error) showError.value = true;
    if (page.props.flash?.warning) showWarning.value = true;
    if (page.props.flash?.info) showInfo.value = true;
    if (page.props.errors && Object.keys(page.props.errors).length > 0) {
        showError.value = true;
    }
});
</script>

<template>
    <!-- Success Toast -->
    <Transition name="slide-fade">
        <div v-if="showSuccess && successMessage" 
             class="fixed top-4 right-4 bg-green-500 text-white p-4 rounded-lg z-50 shadow-lg max-w-sm min-w-72">
            <div class="flex items-start justify-between">
                <div class="flex items-start">
                    <!-- Success Icon -->
                    <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <div>
                        <p class="font-medium">Success!</p>
                        <p class="text-sm opacity-90">{{ successMessage }}</p>
                    </div>
                </div>
                <button @click="closeSuccess" class="ml-2 hover:bg-green-600 px-2 py-1 rounded flex-shrink-0">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
    </Transition>

    <!-- Error Toast (for flash errors or validation errors) -->
    <Transition name="slide-fade">
        <div v-if="showError && (errorMessage || validationErrors)" 
             class="fixed top-4 right-4 bg-red-500 text-white p-4 rounded-lg z-50 shadow-lg max-w-sm min-w-72">
            <div class="flex items-start justify-between">
                <div class="flex items-start">
                    <!-- Error Icon -->
                    <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <div>
                        <p class="font-medium">Error!</p>
                        <p class="text-sm opacity-90">{{ errorMessage || validationErrors }}</p>
                    </div>
                </div>
                <button @click="closeError" class="ml-2 hover:bg-red-600 px-2 py-1 rounded flex-shrink-0">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
    </Transition>

    <!-- Warning Toast -->
    <Transition name="slide-fade">
        <div v-if="showWarning && warningMessage" 
             class="fixed top-16 right-4 bg-yellow-500 text-white p-4 rounded-lg z-50 shadow-lg max-w-sm min-w-72">
            <div class="flex items-start justify-between">
                <div class="flex items-start">
                    <!-- Warning Icon -->
                    <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <div>
                        <p class="font-medium">Warning!</p>
                        <p class="text-sm opacity-90">{{ warningMessage }}</p>
                    </div>
                </div>
                <button @click="closeWarning" class="ml-2 hover:bg-yellow-600 px-2 py-1 rounded flex-shrink-0">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
    </Transition>

    <!-- Info Toast -->
    <Transition name="slide-fade">
        <div v-if="showInfo && infoMessage" 
             class="fixed top-28 right-4 bg-blue-500 text-white p-4 rounded-lg z-50 shadow-lg max-w-sm min-w-72">
            <div class="flex items-start justify-between">
                <div class="flex items-start">
                    <!-- Info Icon -->
                    <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    <div>
                        <p class="font-medium">Info</p>
                        <p class="text-sm opacity-90">{{ infoMessage }}</p>
                    </div>
                </div>
                <button @click="closeInfo" class="ml-2 hover:bg-blue-600 px-2 py-1 rounded flex-shrink-0">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.slide-fade-enter-active {
    transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
    transition: all 0.3s cubic-bezier(1.0, 0.5, 0.8, 1.0);
}

.slide-fade-enter-from,
.slide-fade-leave-to {
    transform: translateX(20px);
    opacity: 0;
}
</style>