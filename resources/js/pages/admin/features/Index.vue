<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, computed } from 'vue';
import { Feature } from '@/types';
import { formatPaginationLabel } from '@/utils';

interface Props {
    features: {
        data: Feature[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
        first_page_url: string;
        last_page_url: string;
        next_page_url: string | null;
        prev_page_url: string | null;
        path: string;
    };
    filters: {
        search?: string;
        category?: string;
    };
}

const { features, filters } = defineProps<Props>();

// Search functionality
const searchQuery = ref(filters.search || '');
const selectedCategory = ref(filters.category || '');

// Available categories
const categories = [
    { value: '', label: 'All Categories' },
    { value: 'amenity', label: 'Amenities' },
    { value: 'safety', label: 'Safety & Security' },
    { value: 'comfort', label: 'Comfort' },
    { value: 'entertainment', label: 'Entertainment' },
    { value: 'outdoor', label: 'Outdoor' },
    { value: 'kitchen', label: 'Kitchen' },
    { value: 'bathroom', label: 'Bathroom' },
    { value: 'accessibility', label: 'Accessibility' },
];

const search = () => {
    router.get(route('admin.features.index'), {
        search: searchQuery.value,
        category: selectedCategory.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    searchQuery.value = '';
    selectedCategory.value = '';
    router.get(route('admin.features.index'), {}, {
        preserveState: true,
        replace: true,
    });
};

// Delete feature
const deleteFeature = (feature: Feature) => {
    if (confirm(`Are you sure you want to delete "${feature.name}"?`)) {
        router.delete(route('admin.features.destroy', feature.id), {
            preserveScroll: true,
        });
    }
};

const hasFilters = computed(() => {
    return searchQuery.value || selectedCategory.value;
});
console.log('hasFilters:', hasFilters.value);
console.log('searchQuery:', searchQuery.value);
console.log('features:', features);
</script>

<template>
    <Head title="Features Management" />

    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 lg:p-8">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-6">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                                    Features Management
                                </h1>
                                <p class="text-gray-600 dark:text-gray-400">
                                    Manage property features and amenities
                                </p>
                            </div>
                            <div class="mt-4 lg:mt-0">
                                <Link 
                                    :href="route('admin.features.create')"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Add Feature
                                </Link>
                            </div>
                        </div>

                        <!-- Filters -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Search -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Search Features
                                    </label>
                                    <input
                                        v-model="searchQuery"
                                        type="text"
                                        placeholder="Search by name or description..."
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        @keyup.enter="search"
                                    />
                                </div>

                                <!-- Category Filter -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Category
                                    </label>
                                    <select 
                                        v-model="selectedCategory"
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                        <option v-for="category in categories" :key="category.value" :value="category.value">
                                            {{ category.label }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex items-end space-x-2">
                                    <button
                                        @click="search"
                                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
                                    >
                                        Search
                                    </button>
                                    <button
                                        v-if="hasFilters"
                                        @click="clearFilters"
                                        class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors"
                                    >
                                        Clear
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Features List -->
                        <div v-if="features.data && features.data.length > 0" class="space-y-4">
                            <div 
                                v-for="feature in features.data" 
                                :key="feature.id"
                                class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <!-- Feature Icon -->
                                        <div class="flex-shrink-0">
                                            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                                <span class="text-lg">{{ feature.icon || '🏠' }}</span>
                                            </div>
                                        </div>

                                        <!-- Feature Details -->
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                                {{ feature.name }}
                                            </h3>
                                            <p v-if="feature.description" class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                {{ feature.description }}
                                            </p>
                                            <div class="flex items-center space-x-4 mt-2">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                    {{ feature.category || 'Uncategorized' }}
                                                </span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    ID: {{ feature.id }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center space-x-2">
                                        <Link 
                                            :href="route('admin.features.edit', feature.id)"
                                            class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                                        >
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Edit
                                        </Link>
                                        <button
                                            @click="deleteFeature(feature)"
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:bg-red-900 dark:text-red-200 dark:hover:bg-red-800 transition-colors"
                                        >
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="text-center py-12">
                            <div class="mx-auto h-12 w-12 text-gray-400">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 48 48">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M34 40h10v-4a6 6 0 00-10.712-3.714M34 40H14m20 0v-4a9.971 9.971 0 00-.712-3.714M14 40H4v-4a6 6 0 0110.712-3.714M14 40v-4a9.971 9.971 0 01.712-3.714M18 20a6 6 0 1112 0v-6a6 6 0 00-12 0v6z" />
                                </svg>
                            </div>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No features found</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ hasFilters ? 'Try adjusting your search criteria.' : 'Get started by creating your first feature.' }}
                            </p>
                            <div class="mt-6">
                                <Link 
                                    v-if="!hasFilters"
                                    :href="route('admin.features.create')"
                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Add Feature
                                </Link>
                                <button 
                                    v-else
                                    @click="clearFilters"
                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                                >
                                    Clear Filters
                                </button>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="features.data && features.data.length > 0 && features.last_page > 1" class="mt-8">
                            <nav class="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3 sm:px-6">
                                <div class="hidden sm:block">
                                    <p class="text-sm text-gray-700 dark:text-gray-300">
                                        Showing
                                        <span class="font-medium">{{ ((features.current_page - 1) * features.per_page) + 1 }}</span>
                                        to
                                        <span class="font-medium">{{ Math.min(features.current_page * features.per_page, features.total) }}</span>
                                        of
                                        <span class="font-medium">{{ features.total }}</span>
                                        results
                                    </p>
                                </div>
                                <div class="flex-1 flex justify-between sm:justify-end">
                                    <Link 
                                        v-for="link in features.links" 
                                        :key="link.label"
                                        :href="link.url ?? ''"
                                        :class="[
                                            'relative inline-flex items-center px-4 py-2 text-sm font-medium border transition-colors',
                                            link.active 
                                                ? 'z-10 bg-blue-50 border-blue-500 text-blue-600 dark:bg-blue-900 dark:border-blue-400 dark:text-blue-300' 
                                                : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700',
                                            !link.url ? 'cursor-not-allowed opacity-50' : 'cursor-pointer'
                                        ]"
                                        :preserve-scroll="true"
                                        :aria-disabled="!link.url"
                                    >
                                        {{ formatPaginationLabel(link.label) }}
                                    </Link>
                                </div>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>