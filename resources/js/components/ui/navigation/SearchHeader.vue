<script setup lang="ts">
import { ref } from 'vue';
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

// Add these methods to your script section
const clearAllFilters = () => {
    form.value = {
        search: '',
        mode: form.value.mode, // Keep the current mode
        priceFilter: { minPrice: '', maxPrice: '' },
        propertyTypes: [],
        bedrooms: '',
        bathrooms: '',
        locationFilter: [],
        minLandSize: '',
        maxLandSize: '',
        carSpaces: '',
        status: [],
        dateFilter: { checkIn: '', checkOut: '' },
        features: [],
    };
};

const applyFiltersAndClose = () => {
    handleSearch();
    closeModal();
};
</script>

<template>
    <div class="bg-gradient-to-r from-primary/90 to-primary shadow-lg container mx-auto w-full relative overflow-hidden"
         style="background-image: url('/images/headers/header_image.jpeg'); background-size: cover; background-position: center;">
        
        <!-- Overlay for better text readability -->
        <div class="absolute inset-0 bg-primary/60 backdrop-blur-sm"></div>
        
        <!-- Search Section -->
        <div class="relative z-10 mx-auto w-full min-h-80 flex flex-col items-center justify-center px-4 py-8">
            
            <!-- Hero Text -->
            <div class="text-center mb-6 md:mb-8">
                <h1 class="font-display text-3xl md:text-5xl lg:text-6xl font-bold text-white mb-3 md:mb-4">
                    Find Your Perfect Villa
                </h1>
                <p class="font-body text-base md:text-xl text-base/90 max-w-2xl">
                    Discover rentals and premium land investments in beautiful Bali
                </p>
            </div>

            <!-- Search Container -->
            <div class="w-full max-w-4xl">
                <!-- Rent/Buy Toggle Bar -->
                <div class="flex justify-center mb-1">
                    <div class="flex bg-white/20 backdrop-blur-md rounded-t-xl p-1 border border-secondary/30">
                        <button
                            @click="selectMode('for_rent')"
                            :class="[
                                'px-4 md:px-8 py-3 text-sm md:text-base font-medium font-display transition-all duration-300',
                                form.mode === 'for_rent' 
                                ? 'bg-white shadow-lg rounded-lg transform scale-105' 
                                : 'text-white hover:text-base hover:bg-white/10 rounded-lg'
                            ]"
                        >
                            <span :class="['flex items-center gap-2', form.mode === 'for_rent' ? 'text-black' : 'text-white']">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                Rent Villas
                            </span>
                        </button>
                        <button
                            @click="selectMode('for_sale')"
                            :class="[
                                'px-4 md:px-8 py-3 text-sm md:text-base font-medium font-display transition-all duration-300',
                                form.mode === 'for_sale' 
                                ? 'bg-white text-primary shadow-lg rounded-lg transform scale-105' 
                                : 'text-white hover:text-base hover:bg-white/10 rounded-lg'
                            ]"
                        >
                            <span :class="['flex items-center gap-2', form.mode === 'for_sale' ? 'text-primary' : 'text-white']">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Buy Land
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Main Search Bar -->
                <div :class="[
                    'bg-white/95 backdrop-blur-md flex flex-col md:flex-row md:items-center md:justify-between w-full px-4 md:px-6 py-4 shadow-xl border border-secondary/20 space-y-4 md:space-y-0 md:space-x-4',
                    form.locationFilter.length ? 'rounded-t-xl' : 'rounded-xl'
                ]">
                    <div class="flex items-center w-full space-x-3">
                        <SearchIcon class="text-primary/70 flex-shrink-0" />
                        <LocationAutocomplete 
                            v-model="form.locationFilter" 
                            class="flex-grow min-w-0"
                            :class="'border-none focus:ring-2 focus:ring-accent/50 bg-transparent text-primary font-body placeholder:text-primary/50'"
                        />
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3 w-full sm:w-auto md:flex-shrink-0">
                        <button
                            @click="modalOpen = true"
                            class="group bg-secondary hover:bg-secondary-600 text-white px-4 sm:px-6 py-3 rounded-lg font-display font-medium transition-all duration-300 hover:shadow-lg transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-secondary/50 w-full sm:w-auto"
                        >
                            <span class="flex items-center justify-center gap-2">
                                <svg class="w-4 h-4 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4" />
                                </svg>
                                <span class="hidden sm:inline">Filters</span>
                                <span class="sm:hidden">Filter</span>
                            </span>
                        </button>
                        <button
                            @click="handleSearch"
                            class="group bg-accent hover:bg-accent-600 text-white px-4 sm:px-8 py-3 rounded-lg font-display font-medium transition-all duration-300 hover:shadow-lg transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-accent/50 w-full sm:w-auto"
                        >
                            <span class="flex items-center justify-center gap-2">
                                <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <span class="hidden sm:inline">Search Properties</span>
                                <span class="sm:hidden">Search</span>
                            </span>
                        </button>
                    </div>
                </div>
                
                <!-- Location Tags -->
                <div v-if="form.locationFilter.length" class="bg-white/95 backdrop-blur-md w-full rounded-b-xl border-t-0 border border-secondary/20 shadow-xl">
                    <LocationTagDisplay 
                        :locations="form.locationFilter"
                        @remove="removeLocationFromFilter" 
                        @clear-all="clearAllLocations"
                        :class="'p-4'"
                    />
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Modal with Brand Colors -->
    <Modal v-model:open="modalOpen" title="Refine Your Search" closable @close="modalOpen = false" size="lg">
        <!-- Modal content with brand styling -->
        <div class="space-y-6">
            <!-- Date Filter -->
            <div class="bg-base/50 rounded-xl p-4 border border-secondary/20">
                <h3 class="font-display text-lg font-semibold text-primary mb-3">Travel Dates</h3>
                <BookingDateFilter v-model="form.dateFilter" />
            </div>

            <!-- Property Type Filter -->
            <div class="bg-base/50 rounded-xl p-4 border border-secondary/20">
                <h3 class="font-display text-lg font-semibold text-primary mb-3">Property Type</h3>
                <PropertyTypeFilter v-model="form.propertyTypes" />
            </div>

            <!-- Price Filter -->
            <div class="bg-base/50 rounded-xl p-4 border border-secondary/20">
                <h3 class="font-display text-lg font-semibold text-primary mb-3">Price Range</h3>
                <PriceFilter v-model="form.priceFilter" />
            </div>

            <!-- Property Details -->
            <div class="bg-base/50 rounded-xl p-4 border border-secondary/20">
                <h3 class="font-display text-lg font-semibold text-primary mb-3">Property Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Bedrooms -->
                    <div>
                        <label class="block text-sm font-medium font-body text-primary/80 mb-2">Bedrooms</label>
                        <select 
                            v-model="form.bedrooms"
                            class="w-full px-4 py-3 border border-secondary/30 rounded-lg bg-white font-body text-primary focus:outline-none focus:ring-2 focus:ring-accent/50 focus:border-accent transition-colors"
                        >
                            <option value="">Any</option>
                            <option value="1">1+ Bedroom</option>
                            <option value="2">2+ Bedrooms</option>
                            <option value="3">3+ Bedrooms</option>
                            <option value="4">4+ Bedrooms</option>
                            <option value="5">5+ Bedrooms</option>
                        </select>
                    </div>

                    <!-- Bathrooms -->
                    <div>
                        <label class="block text-sm font-medium font-body text-primary/80 mb-2">Bathrooms</label>
                        <select 
                            v-model="form.bathrooms"
                            class="w-full px-4 py-3 border border-secondary/30 rounded-lg bg-white font-body text-primary focus:outline-none focus:ring-2 focus:ring-accent/50 focus:border-accent transition-colors"
                        >
                            <option value="">Any</option>
                            <option value="1">1+ Bathroom</option>
                            <option value="2">2+ Bathrooms</option>
                            <option value="3">3+ Bathrooms</option>
                            <option value="4">4+ Bathrooms</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Land Size Filter -->
            <div class="bg-base/50 rounded-xl p-4 border border-secondary/20">
                <h3 class="font-display text-lg font-semibold text-primary mb-3">Land Size</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium font-body text-primary/80 mb-2">Minimum Size</label>
                        <select 
                            v-model="form.minLandSize"
                            class="w-full px-4 py-3 border border-secondary/30 rounded-lg bg-white font-body text-primary focus:outline-none focus:ring-2 focus:ring-accent/50 focus:border-accent transition-colors"
                        >
                            <option value="">No Minimum</option>
                            <option value="100">100+ m² (1 Are)</option>
                            <option value="200">200+ m² (2 Are)</option>
                            <option value="300">300+ m² (3 Are)</option>
                            <option value="500">500+ m² (5 Are)</option>
                            <option value="1000">1,000+ m² (10 Are)</option>
                            <option value="2000">2,000+ m² (20 Are)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium font-body text-primary/80 mb-2">Maximum Size</label>
                        <select 
                            v-model="form.maxLandSize"
                            class="w-full px-4 py-3 border border-secondary/30 rounded-lg bg-white font-body text-primary focus:outline-none focus:ring-2 focus:ring-accent/50 focus:border-accent transition-colors"
                        >
                            <option value="">No Maximum</option>
                            <option value="200">Up to 200 m²</option>
                            <option value="300">Up to 300 m²</option>
                            <option value="500">Up to 500 m²</option>
                            <option value="1000">Up to 1,000 m²</option>
                            <option value="2000">Up to 2,000 m²</option>
                            <option value="5000">Up to 5,000 m²</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Parking -->
            <div class="bg-base/50 rounded-xl p-4 border border-secondary/20">
                <h3 class="font-display text-lg font-semibold text-primary mb-3">Parking</h3>
                <select 
                    v-model="form.carSpaces"
                    class="w-full px-4 py-3 border border-secondary/30 rounded-lg bg-white font-body text-primary focus:outline-none focus:ring-2 focus:ring-accent/50 focus:border-accent transition-colors"
                >
                    <option value="">Any</option>
                    <option value="0">No Parking Required</option>
                    <option value="1">1+ Car Space</option>
                    <option value="2">2+ Car Spaces</option>
                    <option value="3">3+ Car Spaces</option>
                    <option value="4">4+ Car Spaces</option>
                </select>
            </div>
            
            <!-- Features -->
            <div class="bg-base/50 rounded-xl p-4 border border-secondary/20">
                <h3 class="font-display text-lg font-semibold text-primary mb-3">Amenities & Features</h3>
                <FeatureFilter v-model="form.features" />
            </div>
        </div>

        <!-- Enhanced Modal Footer -->
        <template #footer>
            <div class="flex justify-between items-center">
                <button
                    @click="clearAllFilters"
                    class="text-sm font-body text-primary/60 hover:text-primary underline transition-colors"
                >
                    Reset All Filters
                </button>
                
                <div class="flex space-x-3">
                    <button
                        @click="closeModal"
                        class="px-6 py-3 text-sm font-medium font-display text-primary bg-secondary/20 hover:bg-secondary/30 rounded-lg transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        @click="applyFiltersAndClose"
                        class="px-8 py-3 text-sm font-medium font-display text-white bg-accent hover:bg-accent-600 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5"
                    >
                        Apply Filters
                    </button>
                </div>
            </div>
        </template>
    </Modal>
</template>

<style scoped>
/* Custom animations for enhanced UX */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.6s ease-out;
}

/* Custom backdrop blur for older browsers */
.backdrop-blur-fallback {
    background-color: rgba(40, 69, 68, 0.85);
}

@supports (backdrop-filter: blur(4px)) {
    .backdrop-blur-fallback {
        background-color: rgba(40, 69, 68, 0.6);
        backdrop-filter: blur(4px);
    }
}
</style>