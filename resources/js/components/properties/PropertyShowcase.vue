<script setup lang="ts">
import { ref, computed } from 'vue';
import PropertyCard from '@/components/properties/PropertyCard.vue';
import type { Property } from '@/types';

interface Props {
    featured?: Property[];
    premium?: Property[];
}

const { featured = [], premium = [] } = defineProps<Props>();

// Component state
const activeSection = ref<'featured' | 'premium'>('featured');
const currentPage = ref(1);
const itemsPerPage = ref(6);
const viewMode = ref<'scroll' | 'paginate'>('scroll');

// Computed properties
const currentProperties = computed(() => {
    return activeSection.value === 'featured' ? featured : premium;
});

const totalPages = computed(() => {
    return Math.ceil((currentProperties.value?.length || 0) / itemsPerPage.value);
});

const paginatedProperties = computed(() => {
    const properties = currentProperties.value || [];
    
    if (viewMode.value === 'scroll') {
        return properties.slice(0, currentPage.value * itemsPerPage.value);
    } else {
        const start = (currentPage.value - 1) * itemsPerPage.value;
        const end = start + itemsPerPage.value;
        return properties.slice(start, end);
    }
});

const hasMoreProperties = computed(() => {
    const properties = currentProperties.value || [];
    return viewMode.value === 'scroll' && paginatedProperties.value.length < properties.length;
});

const canGoToPrevPage = computed(() => currentPage.value > 1);
const canGoToNextPage = computed(() => currentPage.value < totalPages.value);

// Methods
const switchSection = (section: 'featured' | 'premium') => {
    activeSection.value = section;
    currentPage.value = 1;
};

const loadMore = () => {
    if (hasMoreProperties.value) {
        currentPage.value++;
    }
};

const goToPage = (page: number) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
        // Scroll to top of section
        document.getElementById('property-showcase')?.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'start' 
        });
    }
};

const toggleViewMode = () => {
    viewMode.value = viewMode.value === 'scroll' ? 'paginate' : 'scroll';
    currentPage.value = 1;
};

const getSectionTitle = (section: 'featured' | 'premium') => {
    return section === 'featured' ? 'Featured Properties' : 'Premium Properties';
};

const getSectionDescription = (section: 'featured' | 'premium') => {
    return section === 'featured' 
        ? 'Handpicked properties that stand out from the crowd'
        : 'Exclusive premium listings with exceptional features';
};

const getSectionIcon = (section: 'featured' | 'premium') => {
    return section === 'featured' 
        ? 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'
        : 'M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z';
};
</script>

<template>
    <section id="property-showcase" class="py-16 bg-gray-50 dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100 sm:text-4xl mb-4">
                    Discover Your Perfect Property
                </h2>
                <p class="text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                    Explore our carefully curated selection of featured and premium properties
                </p>
            </div>

            <!-- Section Tabs -->
            <div class="flex justify-center mb-8">
                <div class="flex bg-white dark:bg-gray-800 rounded-lg p-1 shadow-sm border border-gray-200 dark:border-gray-700">
                    <button
                        @click="switchSection('featured')"
                        :class="[
                            'flex items-center px-6 py-3 rounded-md text-sm font-medium transition-all duration-200',
                            activeSection === 'featured'
                                ? 'bg-blue-600 text-white shadow-sm'
                                : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100'
                        ]"
                    >
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path :d="getSectionIcon('featured')" />
                        </svg>
                        Featured ({{ featured?.length || 0 }})
                    </button>
                    <button
                        @click="switchSection('premium')"
                        :class="[
                            'flex items-center px-6 py-3 rounded-md text-sm font-medium transition-all duration-200',
                            activeSection === 'premium'
                                ? 'bg-blue-600 text-white shadow-sm'
                                : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100'
                        ]"
                    >
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path :d="getSectionIcon('premium')" />
                        </svg>
                        Premium ({{ premium?.length || 0 }})
                    </button>
                </div>
            </div>

            <!-- View Mode Toggle & Controls -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
                <div class="mb-4 sm:mb-0">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-1">
                        {{ getSectionTitle(activeSection) }}
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        {{ getSectionDescription(activeSection) }}
                    </p>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Items per page selector (for pagination mode) -->
                    <div v-if="viewMode === 'paginate'" class="flex items-center space-x-2">
                        <label class="text-sm text-gray-600 dark:text-gray-400">Show:</label>
                        <select 
                            v-model="itemsPerPage" 
                            @change="currentPage = 1"
                            class="text-sm border border-gray-300 dark:border-gray-600 rounded px-2 py-1 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
                        >
                            <option :value="6">6</option>
                            <option :value="9">9</option>
                            <option :value="12">12</option>
                        </select>
                    </div>

                    <!-- View mode toggle -->
                    <button
                        @click="toggleViewMode"
                        class="flex items-center px-3 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    >
                        <svg v-if="viewMode === 'scroll'" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ viewMode === 'scroll' ? 'Pagination' : 'Load More' }}
                    </button>
                </div>
            </div>

            <!-- Properties Grid -->
            <div v-if="(currentProperties?.length || 0) > 0">
                <!-- Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8 mb-8">
                    <div 
                        v-for="property in paginatedProperties" 
                        :key="property.id"
                    >
                        <PropertyCard :property="property" />
                    </div>
                </div>

                <!-- Load More Button (Scroll Mode) -->
                <div v-if="viewMode === 'scroll'" class="text-center">
                    <button
                        v-if="hasMoreProperties"
                        @click="loadMore"
                        class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors shadow-sm"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Load More Properties
                    </button>
                    <div v-else class="text-gray-600 dark:text-gray-400">
                        <p class="mb-2">You've seen all {{ currentProperties?.length || 0 }} properties!</p>
                        <button
                            @click="currentPage = 1"
                            class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 text-sm underline"
                        >
                            Back to top
                        </button>
                    </div>
                </div>

                <!-- Pagination (Pagination Mode) -->
                <div v-if="viewMode === 'paginate' && totalPages > 1" class="flex flex-col sm:flex-row items-center justify-between">
                    <!-- Page Info -->
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-4 sm:mb-0">
                        Showing {{ (currentPage - 1) * itemsPerPage + 1 }} to {{ Math.min(currentPage * itemsPerPage, currentProperties?.length || 0) }} of {{ currentProperties?.length || 0 }} properties
                    </div>

                    <!-- Pagination Controls -->
                    <div class="flex items-center space-x-1">
                        <!-- Previous Button -->
                        <button
                            @click="goToPage(currentPage - 1)"
                            :disabled="!canGoToPrevPage"
                            :class="[
                                'px-3 py-2 text-sm font-medium rounded-md transition-colors',
                                canGoToPrevPage
                                    ? 'text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'
                                    : 'text-gray-400 dark:text-gray-600 bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 cursor-not-allowed'
                            ]"
                        >
                            Previous
                        </button>

                        <!-- Page Numbers -->
                        <div class="flex items-center space-x-1">
                            <template v-for="page in Math.min(totalPages, 5)" :key="page">
                                <button
                                    @click="goToPage(page)"
                                    :class="[
                                        'px-3 py-2 text-sm font-medium rounded-md transition-colors',
                                        page === currentPage
                                            ? 'bg-blue-600 text-white'
                                            : 'text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'
                                    ]"
                                >
                                    {{ page }}
                                </button>
                            </template>
                            
                            <!-- Show ellipsis and last page if there are many pages -->
                            <template v-if="totalPages > 5">
                                <span class="px-2 text-gray-500">...</span>
                                <button
                                    @click="goToPage(totalPages)"
                                    :class="[
                                        'px-3 py-2 text-sm font-medium rounded-md transition-colors',
                                        totalPages === currentPage
                                            ? 'bg-blue-600 text-white'
                                            : 'text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'
                                    ]"
                                >
                                    {{ totalPages }}
                                </button>
                            </template>
                        </div>

                        <!-- Next Button -->
                        <button
                            @click="goToPage(currentPage + 1)"
                            :disabled="!canGoToNextPage"
                            :class="[
                                'px-3 py-2 text-sm font-medium rounded-md transition-colors',
                                canGoToNextPage
                                    ? 'text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'
                                    : 'text-gray-400 dark:text-gray-600 bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 cursor-not-allowed'
                            ]"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-16">
                <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <h3 class="text-xl font-medium text-gray-900 dark:text-gray-100 mb-2">
                    No {{ activeSection }} properties available
                </h3>
                <p class="text-gray-600 dark:text-gray-400">
                    Check back soon for new {{ activeSection }} properties or browse other sections.
                </p>
            </div>
        </div>
    </section>
</template>