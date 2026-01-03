<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref, computed, watch, onBeforeUnmount } from 'vue';

const props = defineProps(['property']);
const notes = ref(props.property.notes || '');
const saving = ref(false);
const lastSaved = ref(props.property.notes || '');
const lastSavedAt = ref<Date | null>(null);
const autoSaveTimeout = ref<number | null>(null);
const autoSaveEnabled = ref(false); // Default OFF for safety

// Track changes
const hasUnsavedChanges = computed(() => notes.value !== lastSaved.value);
const characterCount = computed(() => notes.value.length);
const maxLength = 65535;

// Safe auto-save (only for significant changes)
const autoSave = () => {
    if (autoSaveEnabled.value && hasUnsavedChanges.value && !saving.value) {
        const lengthDiff = Math.abs(notes.value.length - lastSaved.value.length);
        // Only auto-save if change seems intentional (>5 chars difference)
        if (lengthDiff > 5) {
            updateNotes(true);
        }
    }
};

// Watch for changes
watch(notes, () => {
    if (autoSaveTimeout.value) {
        clearTimeout(autoSaveTimeout.value);
    }
    if (autoSaveEnabled.value) {
        // 5 second delay for safety
        autoSaveTimeout.value = setTimeout(autoSave, 5000);
    }
}, { immediate: false });

const updateNotes = (isAutoSave = false) => {
    if (saving.value) return;
    saving.value = true;
    
    router.patch(route('admin.properties.notes.update', props.property.slug), {
        notes: notes.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            lastSaved.value = notes.value;
            lastSavedAt.value = new Date();
        },
        onError: (errors) => {
            console.error(errors);
            // Revert on error if auto-save
            if (isAutoSave) {
                notes.value = lastSaved.value;
            }
        },
        onFinish: () => {
            saving.value = false;
        }
    });
};

// Revert to last saved version
const revertChanges = () => {
    if (confirm('Revert all changes to last saved version?')) {
        notes.value = lastSaved.value;
        if (autoSaveTimeout.value) {
            clearTimeout(autoSaveTimeout.value);
        }
    }
};

// Toggle auto-save with warning
const toggleAutoSave = (event: Event) => {
    const checkbox = event.target as HTMLInputElement;
    
    if (checkbox.checked) {
        const confirmed = confirm(
            'Enable auto-save? Changes will save automatically after 5 seconds. ' +
            'You can use "Revert" to undo unwanted changes.'
        );
        if (confirmed) {
            autoSaveEnabled.value = true;
        } else {
            // Revert checkbox if user cancels
            checkbox.checked = false;
        }
    } else {
        autoSaveEnabled.value = false;
        if (autoSaveTimeout.value) {
            clearTimeout(autoSaveTimeout.value);
        }
    }
};

// Warn before leaving with unsaved changes
const beforeUnload = (e: BeforeUnloadEvent) => {
    if (hasUnsavedChanges.value) {
        e.preventDefault();
        e.returnValue = '';
    }
};

watch(hasUnsavedChanges, (newVal) => {
    if (newVal) {
        window.addEventListener('beforeunload', beforeUnload);
    } else {
        window.removeEventListener('beforeunload', beforeUnload);
    }
});

onBeforeUnmount(() => {
    if (autoSaveTimeout.value) {
        clearTimeout(autoSaveTimeout.value);
    }
    window.removeEventListener('beforeunload', beforeUnload);
});
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    Property Notes
                </h3>
                
                <!-- Auto-save toggle and status -->
                <div class="flex items-center space-x-4">
                    <label class="flex items-center space-x-2 text-sm">
                        <input 
                            type="checkbox" 
                            :checked="autoSaveEnabled"
                            @click="toggleAutoSave"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        >
                        <span class="text-gray-700 dark:text-gray-300">Auto-save</span>
                    </label>
                    
                    <!-- Status indicator -->
                    <div class="text-sm">
                        <div v-if="saving" class="flex items-center text-blue-600">
                            <svg class="animate-spin h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Saving...
                        </div>
                        <div v-else-if="hasUnsavedChanges" class="text-amber-600">
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                Unsaved changes
                            </span>
                        </div>
                        <div v-else class="text-green-600">
                            ✓ Saved
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <div class="space-y-4">
                <div class="relative">
                    <textarea
                        v-model="notes"
                        rows="8"
                        :maxlength="maxLength"
                        placeholder="Add internal notes about this property..."
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg p-3 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 placeholder-gray-500 resize-y focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    ></textarea>
                    
                    <!-- Character count -->
                    <div class="absolute bottom-2 right-2 text-xs text-gray-500">
                        {{ characterCount.toLocaleString() }} / {{ maxLength.toLocaleString() }}
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <span v-if="autoSaveEnabled && hasUnsavedChanges">
                            Auto-save in 5s after you stop typing
                        </span>
                        <span v-else-if="!autoSaveEnabled && hasUnsavedChanges">
                            Remember to save your changes
                        </span>
                        <span v-else>
                            All changes saved
                        </span>
                    </div>
                    
                    <div class="flex space-x-3">
                        <!-- Revert button -->
                        <button
                            v-if="hasUnsavedChanges"
                            @click="revertChanges"
                            class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-sm font-medium rounded-lg transition-colors"
                        >
                            Revert
                        </button>
                        
                        <!-- Save button -->
                        <button
                            @click="updateNotes()"
                            :disabled="saving || !hasUnsavedChanges"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white text-sm font-medium rounded-lg transition-colors"
                        >
                            {{ saving ? 'Saving...' : 'Save Notes' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>