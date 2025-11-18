<script setup lang="ts">
import { computed, ref } from 'vue';
import SearchIcon from '../form/SearchIcon.vue';
import Modal from '@/components/Modal.vue';
import PriceFilter from '../filters/PriceFilter.vue';
import BookingDateFilter from '@/components/properties/_BookingDateFilter.vue';
import LocationAutocomplete from '../form/LocationAutocomplete.vue';
import PropertyTypeFilter from '../filters/PropertyTypeFilter.vue';
import { Location } from '@/types';
import LocationTagDisplay from '../form/LocationTagDisplay.vue';
import { processLocations } from '@/utils';
import FeatureFilter from '../filters/FeatureFilter.vue';

const modalOpen = ref(false);
const form = ref({
    search: '',
    mode: 'for_rent' as 'for_rent' | 'for_sale',
    priceFilter: {
        minPrice: '',
        maxPrice: '',
    },
    propertyTypes: [] as string[],
    bedrooms: '',
    bathrooms: '',
    locationFilter: [] as Location[],
    minLandSize: '',
    maxLandSize: '',
    carSpaces: '',
    status: [] as string[],
    dateFilter: {
        checkIn: '',
        checkOut: ''
    },
    features: [] as number[],

});

function selectMode(mode: 'for_rent' | 'for_sale') {
    form.value.mode = mode;
}

// Modal close handler (to be called within the modal)
function closeModal() {
    modalOpen.value = false;
}

const handleSearch = () => {
    // Flatten the bookingDates object into separate fields for the backend
    const flattenedFilters = {
        property_type: form.value.propertyTypes,
        listing_type: form.value.mode,
        bedrooms: form.value.bedrooms,
        bathrooms: form.value.bathrooms,
        min_price: form.value.priceFilter.minPrice,
        max_price: form.value.priceFilter.maxPrice,
        min_land_size: form.value.minLandSize,
        max_land_size: form.value.maxLandSize,
        car_spaces: form.value.carSpaces,
        status: form.value.status,
        check_in_date: form.value.dateFilter.checkIn,
        check_out_date: form.value.dateFilter.checkOut,
        search: form.value.search,
        features: form.value.features.length > 0 ? form.value.features.join(',') : '',
        // explode location filter data
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

    // Redirect to the search results page with query parameters
    window.location.href = `/properties?${queryString}`;
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
</script>

<template>
    <div class="bg-white dark:bg-gray-800 shadow container mx-auto px-4 sm:px-6 lg:px-8 xl:px-12">
        <!-- Search Section -->
        <div class="mx-auto bg-blue-300 w-full h-80 flex flex-col items-center justify-center rounded-md shadow-md mt-4">
            <!-- Rent/Buy Toggle Bar - positioned above search bar -->
            <div>
                <div class="flex bg-gray-100 dark:bg-gray-700 rounded-t-lg p-1">
                    <button
                        @click="selectMode('for_rent')"
                        :class="[
                            'px-2 md:px-6 py-2 text-sm font-medium transition-all duration-200',
                            form.mode === 'for_rent' 
                                ? 'bg-white dark:bg-gray-600 text-blue-600 dark:text-blue-400 shadow-sm rounded-t-md' 
                                : 'text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-gray-100 rounded-t-md'
                        ]"
                    >
                        Rent
                    </button>
                    <button
                        @click="selectMode('for_sale')"
                        :class="[
                            'px-2 md:px-6 py-2 text-sm font-medium transition-all duration-200',
                            form.mode === 'for_sale' 
                                ? 'bg-white dark:bg-gray-600 text-blue-600 dark:text-blue-400 shadow-sm rounded-t-md' 
                                : 'text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-gray-100 rounded-t-md'
                        ]"
                    >
                        Buy
                    </button>
                </div>
            </div>

            <!-- Search bar -->
            <div
                :class="[
                    'bg-white flex flex-col md:flex-row md:items-center md:justify-between w-11/12 px-4 py-2 shadow-md space-y-2 md:space-y-0 md:space-x-2',
                    form.locationFilter.length ? 'rounded-t-md' : 'rounded-md'
                ]"
            >
                <div class="flex items-center w-full space-x-1">
                    <SearchIcon />
                    <LocationAutocomplete v-model="form.locationFilter" class="flex-grow" />
                </div>
                <!-- Action buttons -->
                <div class="flex space-x-2 md:flex-shrink-0 justify-end">
                    <button
                        @click="modalOpen = true"
                        class="bg-blue-600 text-white px-4 py-1.5 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        Filters
                    </button>
                    <button
                        @click="handleSearch"
                        class="bg-blue-600 text-white px-4 py-1.5 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        Search
                    </button>
                </div>
            </div>
            <div class="bg-white w-2/3 rounded-b-md">
                <LocationTagDisplay :locations="form.locationFilter"
                @remove="removeLocationFromFilter" 
                @clear-all="clearAllLocations" />
            </div>
        </div>
        <Modal v-model:open="modalOpen" title="Filters" closable @close="modalOpen = false" size="lg">
             <!-- Modal content -->
            <div class="space-y-4">
                <!-- Date filter -->
                 <BookingDateFilter v-model="form.dateFilter" />
                <!-- Type Filter -->
                <PropertyTypeFilter v-model="form.propertyTypes" />

                <hr class="border-t border-gray-200 dark:border-gray-600 my-4" />

                <!-- Price Filter -->
                <PriceFilter v-model="form.priceFilter" />

                <hr class="border-t border-gray-200 dark:border-gray-600 my-4" />

                <!-- Bedrooms Filter -->
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

                <hr class="border-t border-gray-200 dark:border-gray-600 my-4" />

                <!-- Bathrooms Filter -->
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

                <hr class="border-t border-gray-200 dark:border-gray-600 my-4" />

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

                <hr class="border-t border-gray-200 dark:border-gray-600 my-4" />

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
                
                <hr class="border-t border-gray-200 dark:border-gray-600 my-4" />

                <FeatureFilter v-model="form.features" />
            </div>

            <!-- Modal footer with action buttons -->
            <template #footer>
                <div class="flex justify-end space-x-2">
                    <button
                        @click="closeModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-md"
                    >
                        Cancel
                    </button>
                    <button
                        @click="closeModal"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md"
                    >
                        Apply Filters
                    </button>
                </div>
            </template>
        </Modal>
    </div>
</template>