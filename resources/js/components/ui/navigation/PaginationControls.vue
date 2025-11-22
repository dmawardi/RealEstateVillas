<!-- resources/js/components/ui/PaginationControls.vue -->
<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    currentPage: number;
    totalPages: number;
    maxVisible?: number;
    variant?: 'default' | 'red' | 'yellow' | 'blue' | 'green';
    size?: 'sm' | 'md' | 'lg';
}

interface Emits {
    (e: 'page-changed', page: number): void;
}

const props = withDefaults(defineProps<Props>(), {
    maxVisible: 5,
    variant: 'default',
    size: 'sm'
});

const emit = defineEmits<Emits>();

// Generate page numbers array
const pageNumbers = computed(() => {
    const pages = [];
    const { currentPage, totalPages, maxVisible } = props;
    
    if (totalPages <= maxVisible) {
        for (let i = 1; i <= totalPages; i++) {
            pages.push(i);
        }
    } else {
        const start = Math.max(1, currentPage - Math.floor(maxVisible / 2));
        const end = Math.min(totalPages, start + maxVisible - 1);
        
        for (let i = start; i <= end; i++) {
            pages.push(i);
        }
    }
    
    return pages;
});

// Variant classes
const variantClasses = computed(() => {
    const variants = {
        default: {
            active: 'bg-blue-600 text-white border-blue-600',
            inactive: 'border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'
        },
        red: {
            active: 'bg-red-600 text-white border-red-600',
            inactive: 'border-gray-300 dark:border-gray-600 hover:bg-red-50 dark:hover:bg-red-900/20'
        },
        yellow: {
            active: 'bg-yellow-600 text-white border-yellow-600',
            inactive: 'border-gray-300 dark:border-gray-600 hover:bg-yellow-50 dark:hover:bg-yellow-900/20'
        },
        blue: {
            active: 'bg-blue-600 text-white border-blue-600',
            inactive: 'border-gray-300 dark:border-gray-600 hover:bg-blue-50 dark:hover:bg-blue-900/20'
        },
        green: {
            active: 'bg-green-600 text-white border-green-600',
            inactive: 'border-gray-300 dark:border-gray-600 hover:bg-green-50 dark:hover:bg-green-900/20'
        }
    };
    
    return variants[props.variant];
});

// Size classes
const sizeClasses = computed(() => {
    const sizes = {
        sm: 'px-3 py-1 text-xs',
        md: 'px-4 py-2 text-sm',
        lg: 'px-6 py-3 text-base'
    };
    
    return sizes[props.size];
});

// Button classes
const buttonBaseClasses = computed(() => {
    return `${sizeClasses.value} font-medium rounded-md border transition-colors duration-200`;
});

const disabledClasses = 'disabled:opacity-50 disabled:cursor-not-allowed';

// Event handlers
const goToPage = (page: number) => {
    if (page >= 1 && page <= props.totalPages && page !== props.currentPage) {
        emit('page-changed', page);
    }
};

const goToPrevious = () => {
    goToPage(props.currentPage - 1);
};

const goToNext = () => {
    goToPage(props.currentPage + 1);
};
</script>

<template>
    <div v-if="totalPages > 1" class="flex items-center justify-center space-x-2">
        <!-- Previous Button -->
        <button
            @click="goToPrevious"
            :disabled="currentPage === 1"
            :class="[
                buttonBaseClasses,
                variantClasses.inactive,
                disabledClasses
            ]"
        >
            <span class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Previous
            </span>
        </button>
        
        <!-- Page Numbers -->
        <button
            v-for="page in pageNumbers"
            :key="`page-${page}`"
            @click="goToPage(page)"
            :class="[
                buttonBaseClasses,
                page === currentPage ? variantClasses.active : variantClasses.inactive
            ]"
        >
            {{ page }}
        </button>
        
        <!-- Next Button -->
        <button
            @click="goToNext"
            :disabled="currentPage === totalPages"
            :class="[
                buttonBaseClasses,
                variantClasses.inactive,
                disabledClasses
            ]"
        >
            <span class="flex items-center">
                Next
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </span>
        </button>
    </div>
</template>