<script setup lang="ts">
import { computed, onMounted, onUnmounted } from 'vue';

// Props interface defining the modal's properties
interface Props {
    open?: boolean;           // Controls modal visibility
    title?: string;          // Optional modal title
    size?: 'sm' | 'md' | 'lg' | 'xl'; // Modal size variants
    closable?: boolean;      // Whether modal can be closed
    closeOnOverlay?: boolean; // Close when clicking overlay
}

// Events interface defining what events this modal emits
interface Emits {
    (e: 'close'): void;      // Emitted when modal should close
    (e: 'opened'): void;     // Emitted when modal is opened
    (e: 'closed'): void;     // Emitted when modal is closed
}

// Set default values for props
const props = withDefaults(defineProps<Props>(), {
    open: false,
    size: 'md',
    closable: true,
    closeOnOverlay: true,
});

const emit = defineEmits<Emits>();

// Computed size classes for different modal sizes
const sizeClasses = computed(() => {
    const sizes = {
        sm: 'max-w-md',
        md: 'max-w-lg',
        lg: 'max-w-2xl',
        xl: 'max-w-4xl'
    };
    return sizes[props.size];
});

// Handle modal close
const handleClose = () => {
    if (props.closable) {
        emit('close');
    }
};

// Handle overlay click
const handleOverlayClick = () => {
    if (props.closeOnOverlay) {
        handleClose();
    }
};

// Handle escape key press
const handleEscapeKey = (event: KeyboardEvent) => {
    if (event.key === 'Escape' && props.open && props.closable) {
        handleClose();
    }
};

// Add/remove escape key listener
onMounted(() => {
    document.addEventListener('keydown', handleEscapeKey);
    if (props.open) {
        emit('opened');
    }
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleEscapeKey);
});

// Watch for open state changes
const handleOpened = () => {
    if (props.open) {
        emit('opened');
        // Prevent body scroll when modal is open
        document.body.style.overflow = 'hidden';
    } else {
        emit('closed');
        // Restore body scroll when modal is closed
        document.body.style.overflow = '';
    }
};

// Watch open prop changes
import { watch } from 'vue';
watch(() => props.open, handleOpened);
</script>

<template>
    <!-- Modal overlay -->
    <Teleport to="body">
        <Transition
            enter-active-class="duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="open"
                class="fixed inset-0 z-50 overflow-y-auto"
                aria-labelledby="modal-title"
                role="dialog"
                aria-modal="true"
            >
                <!-- Background overlay -->
                <div
                    class="fixed inset-0 backdrop-blur-none bg-black/20 transition-opacity"
                    @click="handleOverlayClick"
                ></div>

                <!-- Modal container -->
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <Transition
                        enter-active-class="duration-300 ease-out"
                        enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                        leave-active-class="duration-200 ease-in"
                        leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                        leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    >
                        <div
                            v-if="open"
                            :class="[
                                'relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:p-6',
                                sizeClasses
                            ]"
                        >
                            <!-- Modal header -->
                            <div v-if="title || closable" class="flex items-center justify-between mb-4">
                                <h3 v-if="title" id="modal-title" class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    {{ title }}
                                </h3>
                                <button
                                    v-if="closable"
                                    @click="handleClose"
                                    class="rounded-md bg-white dark:bg-gray-800 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    <span class="sr-only">Close</span>
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Modal content -->
                            <div class="text-gray-900 dark:text-gray-100">
                                <slot></slot>
                            </div>

                            <!-- Modal footer (optional) -->
                            <div v-if="$slots.footer" class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <slot name="footer"></slot>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>