<script setup lang="ts">
import { ref } from 'vue';
import SearchIcon from '../form/SearchIcon.vue';
import Modal from '@/components/Modal.vue';
import PriceFilter from '../filters/PriceFilter.vue';
import BookingDateFilter from '@/components/properties/_BookingDateFilter.vue';
import LocationAutocomplete from '../form/LocationAutocomplete.vue';
import { Location } from '@/types';
import LocationTagDisplay from '../form/LocationTagDisplay.vue';
import PropertyTypeFilter from '../filters/PropertyTypeFilter.vue';
import { processLocations } from '@/utils';
import FeatureFilter from '../filters/FeatureFilter.vue';

// Props to accept initial filter values from the page
interface Props {
    initialFilters?: Record<string, any>;
    routeURL: string;
    useTextSearch: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    initialFilters: () => ({}),
    routeURL: '/properties',
    useTextSearch: false
});

const modalOpen = ref(false);

// Initialize form with props data or defaults
const form = ref({
    search: props.initialFilters.search || '',
    mode: props.initialFilters.listing_type as 'for_rent' | 'for_sale' | undefined,
    priceFilter: {
        minPrice: props.initialFilters.min_price || '',
        maxPrice: props.initialFilters.max_price || '',
    },
    propertyTypes: props.initialFilters.property_type ? props.initialFilters.property_type.split(',') : [],
    bedrooms: props.initialFilters.bedrooms || '',
    bathrooms: props.initialFilters.bathrooms || '',
    locationFilter: [] as Location[], // Will be populated from initial filters
    minLandSize: props.initialFilters.min_land_size || '',
    maxLandSize: props.initialFilters.max_land_size || '',
    carSpaces: props.initialFilters.car_spaces || '',
    status: props.initialFilters.status ? props.initialFilters.status.split(',') : [],
    dateFilter: {
        checkIn: props.initialFilters.check_in_date || '',
        checkOut: props.initialFilters.check_out_date || ''
    },
    features: props.initialFilters.features ? 
    props.initialFilters.features.split(',').map((id: any) => parseInt(id.trim(), 10)).filter((id: any) => !isNaN(id)) : 
    []
});

// Convert initial location filters back to Location objects
const initializeLocationFilter = () => {
    const locations: Location[] = [];
    
    if (props.initialFilters.villages) {
        props.initialFilters.villages.split(',').forEach((village: string) => {
            locations.push({ name: village, type: 'village' });
        });
    }
    
    if (props.initialFilters.districts) {
        props.initialFilters.districts.split(',').forEach((district: string) => {
            locations.push({ name: district, type: 'district' });
        });
    }
    
    if (props.initialFilters.regencies) {
        props.initialFilters.regencies.split(',').forEach((regency: string) => {
            locations.push({ name: regency, type: 'regency' });
        });
    }
    
    form.value.locationFilter = locations;
};

// Initialize location filter on component mount
initializeLocationFilter();

function selectMode(mode: 'for_rent' | 'for_sale') {
    form.value.mode = mode;
}

function closeModal() {
    modalOpen.value = false;
}

function applyFilters() {
    modalOpen.value = false;
    handleSearch();
}

const handleSearch = () => {
    const flattenedFilters = {
        property_type: form.value.propertyTypes.join(','),
        ...(form.value.mode && { listing_type: form.value.mode }),
        bedrooms: form.value.bedrooms,
        bathrooms: form.value.bathrooms,
        min_price: form.value.priceFilter.minPrice,
        max_price: form.value.priceFilter.maxPrice,
        min_land_size: form.value.minLandSize,
        max_land_size: form.value.maxLandSize,
        car_spaces: form.value.carSpaces,
        status: form.value.status.join(','),
        check_in_date: form.value.dateFilter.checkIn,
        check_out_date: form.value.dateFilter.checkOut,
        search: form.value.search,
        features: form.value.features.length > 0 ? form.value.features.join(',') : '',
        ...processLocations(form.value.locationFilter),
    };

    // Remove empty filters
    const cleanFilters = Object.fromEntries(
        Object.entries(flattenedFilters).filter(([, value]) => {
            if (value === '' || value === null || value === undefined) return false;
            if (Array.isArray(value) && value.length === 0) return false;
            return true;
        })
    );

    const queryString = new URLSearchParams(cleanFilters as Record<string, string>).toString();
    window.location.href = `${props.routeURL}?${queryString}`;
};

// Methods for location tags
const removeLocationFromFilter = (location: Location) => {
    form.value.locationFilter = form.value.locationFilter.filter(
        selected => selected.name !== location.name
    );
};

const clearAllLocations = () => {
    form.value.locationFilter = [];
};

const clearAllFilters = () => {
    form.value = {
        search: '',
        mode: undefined,
        priceFilter: { minPrice: '', maxPrice: ''},
        propertyTypes: [],
        bedrooms: '',
        bathrooms: '',
        locationFilter: [],
        minLandSize: '',
        maxLandSize: '',
        carSpaces: '',
        status: [],
        dateFilter: { checkIn: '', checkOut: '' },
        features: []
    };
    handleSearch();
};

const clearSearch = () => {
    form.value.search = '';
    handleSearch();
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 p-4">
        <!-- Compact Search Bar -->
        <div class="max-w-7xl mx-auto">
            <!-- Rent/Buy Toggle - Smaller version -->
            <div class="flex justify-center mb-3">
                <div class="flex bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
                    <button
                        @click="selectMode('for_rent')"
                        :class="[
                            'px-4 py-1.5 text-sm font-medium transition-all duration-200',
                            form.mode === 'for_rent' 
                                ? 'bg-white dark:bg-gray-600 text-blue-600 dark:text-blue-400 shadow-sm rounded-md' 
                                : 'text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-gray-100'
                        ]"
                    >
                        Rent
                    </button>
                    <button
                        @click="selectMode('for_sale')"
                        :class="[
                            'px-4 py-1.5 text-sm font-medium transition-all duration-200',
                            form.mode === 'for_sale' 
                                ? 'bg-white dark:bg-gray-600 text-blue-600 dark:text-blue-400 shadow-sm rounded-md' 
                                : 'text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-gray-100'
                        ]"
                    >
                        Buy
                    </button>
                </div>
            </div>

            <!-- Compact Search Container -->
            <div class="flex flex-col space-y-2">
                <!-- Main Search Bar -->
                <div
                    :class="[
                        'bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 flex items-center px-3 py-2 shadow-sm',
                        form.locationFilter.length ? 'rounded-t-lg' : 'rounded-lg'
                    ]"
                >
                    <SearchIcon class="text-gray-400 mr-2" />
                    <LocationAutocomplete 
                        v-model="form.locationFilter" 
                        placeholder="Search locations..."
                        class="flex-grow"
                    />
                    
                    <!-- Action Buttons -->
                    <div class="flex space-x-2 ml-3">
                        <button
                            @click="modalOpen = true"
                            class="px-3 py-1.5 text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-md transition-colors"
                        >
                            Filters
                        </button>
                        <button
                            @click="clearAllFilters"
                            class="px-3 py-1.5 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-md transition-colors"
                        >
                            Clear
                        </button>
                        <button
                            @click="handleSearch"
                            class="px-4 py-1.5 text-sm bg-blue-600 text-white hover:bg-blue-700 rounded-md transition-colors"
                        >
                            Search
                        </button>
                    </div>
                </div>

                <!-- Selected Locations Display -->
                <div 
                    v-if="form.locationFilter.length > 0"
                    class="bg-white dark:bg-gray-800 border-l border-r border-b border-gray-300 dark:border-gray-600 rounded-b-lg px-3 pb-2"
                >
                    <LocationTagDisplay 
                        :locations="form.locationFilter"
                        @remove="removeLocationFromFilter" 
                        @clear-all="clearAllLocations"
                    />
                </div>
            </div>
        </div>
         <!-- Text Search Input -->
        <div class="" v-if="useTextSearch">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Text Search
                </label>
                <div class="relative">
                    <!-- Search Input -->
                    <input
                        v-model="form.search"
                        type="text"
                        placeholder="Search by title, property ID, street name, or district..."
                        class="w-full pl-10 pr-10 py-3 border border-gray-300 dark:border-gray-600 rounded-lg 
                               bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                               focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               placeholder-gray-400 dark:placeholder-gray-500"
                    />
                    
                    <!-- Search Icon -->
                    <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    
                    <!-- Clear Button -->
                    <button
                        v-if="form.search"
                        @click="clearSearch"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <!-- Search Info -->
                <div class="mt-2 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                    <span>
                        {{ form.search ? `Searching for: "${form.search}"` : 'Enter text to search properties...' }}
                    </span>
                    <span class="text-gray-400 dark:text-gray-500">
                        Press Enter to search • Esc to clear
                    </span>
                </div>
            </div>
        </div>

        <!-- Filters Modal -->
        <Modal v-model:open="modalOpen" title="Search Filters" closable @close="closeModal" size="lg">
            <div class="space-y-4">
                <!-- Date Filter -->
                <BookingDateFilter v-model="form.dateFilter" />

                <!-- Property Type Filter -->
                <PropertyTypeFilter v-model="form.propertyTypes" />

                <hr class="border-t border-gray-200 dark:border-gray-600" />

                <!-- Price Filter -->
                <PriceFilter v-model="form.priceFilter" />

                <hr class="border-t border-gray-200 dark:border-gray-600" />

                <!-- Bedrooms & Bathrooms - Compact Grid -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Bedrooms</label>
                        <select 
                            v-model="form.bedrooms"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Any</option>
                            <option value="1">1+</option>
                            <option value="2">2+</option>
                            <option value="3">3+</option>
                            <option value="4">4+</option>
                            <option value="5">5+</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Bathrooms</label>
                        <select 
                            v-model="form.bathrooms"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Any</option>
                            <option value="1">1+</option>
                            <option value="2">2+</option>
                            <option value="3">3+</option>
                            <option value="4">4+</option>
                        </select>
                    </div>
                </div>

                <hr class="border-t border-gray-200 dark:border-gray-600" />

                <!-- Land Size Filter -->
                <div>
                    <label class="block text-sm font-medium mb-2">Land Size</label>
                    <div class="grid grid-cols-2 gap-2">
                        <select 
                            v-model="form.minLandSize"
                            class="px-3 py-2 border border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Min Size</option>
                            <option value="100">100+ m² (1 Are)</option>
                            <option value="200">200+ m² (2 Are)</option>
                            <option value="300">300+ m² (3 Are)</option>
                            <option value="500">500+ m² (5 Are)</option>
                            <option value="1000">1,000+ m² (10 Are)</option>
                            <option value="2000">2,000+ m² (20 Are)</option>
                        </select>
                        <select 
                            v-model="form.maxLandSize"
                            class="px-3 py-2 border border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Max Size</option>
                            <option value="200">200 m² (2 Are)</option>
                            <option value="300">300 m² (3 Are)</option>
                            <option value="500">500 m² (5 Are)</option>
                            <option value="1000">1,000 m² (10 Are)</option>
                            <option value="2000">2,000 m² (20 Are)</option>
                            <option value="5000">5,000+ m² (50+ Are)</option>
                        </select>
                    </div>
                </div>

                <hr class="border-t border-gray-200 dark:border-gray-600" />

                <!-- Car Spaces Filter -->
                <div>
                    <label class="block text-sm font-medium mb-2">Car Spaces</label>
                    <select 
                        v-model="form.carSpaces"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">Any</option>
                        <option value="0">No Parking</option>
                        <option value="1">1+</option>
                        <option value="2">2+</option>
                        <option value="3">3+</option>
                        <option value="4">4+</option>
                    </select>
                </div>

                <hr class="border-t border-gray-200 dark:border-gray-600" />
                <!-- Features Filter -->
                <FeatureFilter v-model="form.features" />
            </div>

            <template #footer>
                <div class="flex justify-between">
                    <button
                        @click="clearAllFilters"
                        class="px-4 py-2 text-sm font-medium text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 rounded-md"
                    >
                        Clear All
                    </button>
                    <div class="flex space-x-2">
                        <button
                            @click="closeModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-md"
                        >
                            Cancel
                        </button>
                        <button
                            @click="applyFilters"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md"
                        >
                            Apply Filters
                        </button>
                    </div>
                </div>
            </template>
        </Modal>
    </div>
</template>