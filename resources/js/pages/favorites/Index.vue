<script setup lang="ts">
import { Property, SEO } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';
import PropertyCard from '@/components/properties/PropertyCard.vue';
import SEOHead from '@/components/SEOHead.vue';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Heart, Grid3X3, List, SortAsc } from 'lucide-vue-next';

interface PaginatedFavorites {
    data: Property[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

interface Props {
    favorites: PaginatedFavorites;
    seoData?: SEO;
    filters?: {
        search?: string;
        property_type?: string;
        listing_type?: string;
        location?: string;
        min_price?: string;
        max_price?: string;
        bedrooms?: string;
        bathrooms?: string;
        sort?: string;
    };
}

const { favorites, seoData, filters } = defineProps<Props>();

// Filter state
const searchTerm = ref(filters?.search || '');
const selectedPropertyType = ref(filters?.property_type || '');
const selectedListingType = ref(filters?.listing_type || '');
const selectedLocation = ref(filters?.location || '');
const minPrice = ref(filters?.min_price || '');
const maxPrice = ref(filters?.max_price || '');
const selectedBedrooms = ref(filters?.bedrooms || '');
const selectedBathrooms = ref(filters?.bathrooms || '');
const sortBy = ref(filters?.sort || 'newest');

// View mode
const viewMode = ref<'grid' | 'list'>('grid');

// Property type options
const propertyTypeOptions = [
    { value: '', label: 'All Property Types' },
    { value: 'villa', label: 'Villa' },
    { value: 'apartment', label: 'Apartment' },
    { value: 'house', label: 'House' },
    { value: 'land', label: 'Land' },
    { value: 'commercial', label: 'Commercial' },
    { value: 'guest_house', label: 'Guest House' },
    { value: 'townhouse', label: 'Townhouse' },
];

// Listing type options
const listingTypeOptions = [
    { value: '', label: 'All Listing Types' },
    { value: 'for_rent', label: 'For Rent' },
    { value: 'for_sale', label: 'For Sale' },
];

// Bedroom options
const bedroomOptions = [
    { value: '', label: 'Any Bedrooms' },
    { value: '1', label: '1 Bedroom' },
    { value: '2', label: '2 Bedrooms' },
    { value: '3', label: '3 Bedrooms' },
    { value: '4', label: '4+ Bedrooms' },
];

// Bathroom options
const bathroomOptions = [
    { value: '', label: 'Any Bathrooms' },
    { value: '1', label: '1 Bathroom' },
    { value: '2', label: '2 Bathrooms' },
    { value: '3', label: '3+ Bathrooms' },
];

// Sort options
const sortOptions = [
    { value: 'newest', label: 'Recently Added' },
    { value: 'oldest', label: 'Oldest First' },
    { value: 'price_low', label: 'Price: Low to High' },
    { value: 'price_high', label: 'Price: High to Low' },
    { value: 'title', label: 'Alphabetical' },
];

// Computed properties
const hasActiveFilters = computed(() => {
    return searchTerm.value || selectedPropertyType.value || selectedListingType.value || 
           selectedLocation.value || minPrice.value || maxPrice.value || 
           selectedBedrooms.value || selectedBathrooms.value || sortBy.value !== 'newest';
});

const hasFavorites = computed(() => favorites.data.length > 0);

const uniqueLocations = computed(() => {
    const locations = new Set<string>();
    favorites.data.forEach(property => {
        if (property.village && property.district) {
            locations.add(`${property.village}, ${property.district}`);
        }
    });
    return Array.from(locations).sort();
});

// Methods
const applyFilters = () => {
    const filterParams = {
        search: searchTerm.value || undefined,
        property_type: selectedPropertyType.value || undefined,
        listing_type: selectedListingType.value || undefined,
        location: selectedLocation.value || undefined,
        min_price: minPrice.value || undefined,
        max_price: maxPrice.value || undefined,
        bedrooms: selectedBedrooms.value || undefined,
        bathrooms: selectedBathrooms.value || undefined,
        sort: sortBy.value !== 'newest' ? sortBy.value : undefined,
    };

    // Remove empty values
    Object.keys(filterParams).forEach(key => {
        const typedKey = key as keyof typeof filterParams;
        if (filterParams[typedKey] === undefined || filterParams[typedKey] === '') {
            delete filterParams[typedKey];
        }
    });

    router.get('/my-favorites', filterParams, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    searchTerm.value = '';
    selectedPropertyType.value = '';
    selectedListingType.value = '';
    selectedLocation.value = '';
    minPrice.value = '';
    maxPrice.value = '';
    selectedBedrooms.value = '';
    selectedBathrooms.value = '';
    sortBy.value = 'newest';
    
    router.get('/my-favorites', {}, {
        preserveState: true,
        replace: true,
    });
};

const handlePageChange = (page: number) => {
    router.get('/my-favorites', {
        ...filters,
        page
    }, {
        preserveState: true,
        replace: true,
    });
};

const toggleViewMode = () => {
    viewMode.value = viewMode.value === 'grid' ? 'list' : 'grid';
};
</script>

<template>
    <SEOHead v-if="seoData" :seoData="seoData" />

    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-4">
                    <Heart class="h-8 w-8 text-red-500 fill-current" />
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">My Favorites</h1>
                </div>
                <p class="text-gray-600 dark:text-gray-400">
                    Properties you've saved for later viewing
                </p>
            </div>

            <!-- Filters Section -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        Filter Favorites
                    </h2>
                    
                    <!-- View Toggle -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-500 dark:text-gray-400">View:</span>
                        <button
                            @click="toggleViewMode"
                            class="flex items-center gap-2 px-3 py-1.5 rounded-md bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                        >
                            <Grid3X3 v-if="viewMode === 'list'" class="h-4 w-4" />
                            <List v-else class="h-4 w-4" />
                            <span class="text-sm capitalize">{{ viewMode === 'grid' ? 'List' : 'Grid' }}</span>
                        </button>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Search Properties
                        </label>
                        <input
                            v-model="searchTerm"
                            type="text"
                            placeholder="Property title, location..."
                            class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm"
                            @keyup.enter="applyFilters"
                        />
                    </div>

                    <!-- Property Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Property Type
                        </label>
                        <select
                            v-model="selectedPropertyType"
                            class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm"
                        >
                            <option v-for="option in propertyTypeOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </div>

                    <!-- Listing Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Listing Type
                        </label>
                        <select
                            v-model="selectedListingType"
                            class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm"
                        >
                            <option v-for="option in listingTypeOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </div>

                    <!-- Location -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Location
                        </label>
                        <select
                            v-model="selectedLocation"
                            class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm"
                        >
                            <option value="">All Locations</option>
                            <option v-for="location in uniqueLocations" :key="location" :value="location">
                                {{ location }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Advanced Filters Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4 mt-4">
                    <!-- Price Range -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Min Price (IDR)
                        </label>
                        <input
                            v-model="minPrice"
                            type="number"
                            placeholder="0"
                            class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Max Price (IDR)
                        </label>
                        <input
                            v-model="maxPrice"
                            type="number"
                            placeholder="No limit"
                            class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm"
                        />
                    </div>

                    <!-- Bedrooms -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Bedrooms
                        </label>
                        <select
                            v-model="selectedBedrooms"
                            class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm"
                        >
                            <option v-for="option in bedroomOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </div>

                    <!-- Bathrooms -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Bathrooms
                        </label>
                        <select
                            v-model="selectedBathrooms"
                            class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm"
                        >
                            <option v-for="option in bathroomOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </div>

                    <!-- Sort -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Sort By
                        </label>
                        <select
                            v-model="sortBy"
                            class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm"
                        >
                            <option v-for="option in sortOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Filter Actions -->
                <div class="flex items-center gap-3 mt-4">
                    <button
                        @click="applyFilters"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors flex items-center gap-2"
                    >
                        <SortAsc class="h-4 w-4" />
                        Apply Filters
                    </button>
                    
                    <button
                        v-if="hasActiveFilters"
                        @click="clearFilters"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors"
                    >
                        Clear All
                    </button>
                    
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ favorites.total }} favorite{{ favorites.total !== 1 ? 's' : '' }} found
                    </div>
                </div>
            </div>

            <!-- Favorites Content -->
            <div v-if="hasFavorites">
                <!-- Results Summary -->
                <div class="mb-6 flex items-center justify-between">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        Showing {{ favorites.from }}-{{ favorites.to }} of {{ favorites.total }} favorites
                    </div>
                    <div v-if="favorites.last_page > 1" class="text-sm text-gray-500 dark:text-gray-400">
                        Page {{ favorites.current_page }} of {{ favorites.last_page }}
                    </div>
                </div>

                <!-- Properties Grid/List -->
                <div 
                    :class="[
                        'transition-all duration-300',
                        viewMode === 'grid' 
                            ? 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 gap-6' 
                            : 'space-y-6'
                    ]"
                >
                    <PropertyCard
                        v-for="property in favorites.data"
                        :key="property.id"
                        :property="property"
                        :class="viewMode === 'list' ? 'max-w-none' : ''"
                    />
                </div>

                <!-- Pagination -->
                <div v-if="favorites.last_page > 1" class="mt-8">
                    <div class="flex items-center justify-center space-x-2">
                        <!-- Previous Button -->
                        <button
                            v-if="favorites.current_page > 1"
                            @click="handlePageChange(favorites.current_page - 1)"
                            class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700"
                        >
                            Previous
                        </button>

                        <!-- Page Numbers -->
                        <template v-for="page in Math.min(5, favorites.last_page)" :key="page">
                            <button
                                @click="handlePageChange(page)"
                                :class="[
                                    'px-3 py-2 text-sm font-medium rounded-md',
                                    page === favorites.current_page
                                        ? 'text-white bg-blue-600 border border-blue-600'
                                        : 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700'
                                ]"
                            >
                                {{ page }}
                            </button>
                        </template>

                        <!-- Next Button -->
                        <button
                            v-if="favorites.current_page < favorites.last_page"
                            @click="handlePageChange(favorites.current_page + 1)"
                            class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12">
                <div class="max-w-md mx-auto">
                    <Heart class="mx-auto h-16 w-16 text-gray-300 dark:text-gray-600 mb-4" />
                    
                    <h3 class="mt-4 text-xl font-medium text-gray-900 dark:text-gray-100">
                        {{ hasActiveFilters ? 'No favorites match your filters' : 'No favorites yet' }}
                    </h3>
                    
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        {{ hasActiveFilters 
                            ? 'Try adjusting your filters to find more properties.' 
                            : 'Start exploring our properties and save your favorites by clicking the heart icon.' }}
                    </p>

                    <div class="mt-6">
                        <button
                            v-if="hasActiveFilters"
                            @click="clearFilters"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md text-sm font-medium transition-colors"
                        >
                            Clear Filters
                        </button>
                        <a
                            v-else
                            href="/properties"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md text-sm font-medium transition-colors inline-block"
                        >
                            Browse Properties
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Add any specific styles if needed */
</style>