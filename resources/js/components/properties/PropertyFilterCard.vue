<script setup lang="ts">
import type { PropertyFilters } from '@/types';
import { router } from '@inertiajs/vue3';
import { reactive } from 'vue';


interface Props {
    filters?: PropertyFilters;
}

const { filters } = defineProps<Props>();

// Reactive filters state initialized with current filter values
const currentFilters = reactive({
    property_type: filters?.propertyType || '',
    listing_type: filters?.listingType || '',
    bedrooms: filters?.bedrooms || '',
    village: filters?.village || '',
});

// Apply filters function
const applyFilters = () => {
    // Remove empty filters
    const cleanFilters = Object.fromEntries(
        Object.entries(currentFilters).filter(([_, value]) => value !== '')
    );

    router.get('/properties', cleanFilters, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Clear filters function
const clearFilters = () => {
    Object.keys(currentFilters).forEach(key => {
        currentFilters[key as keyof typeof currentFilters] = '';
    });
    
    router.get('/properties', {}, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Check if any filters are active
const hasActiveFilters = () => {
    return Object.values(currentFilters).some(value => value !== '');
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Property Type
                </label>
                <select 
                    v-model="currentFilters.property_type"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="">All Types</option>
                    <option value="house">House</option>
                    <option value="apartment">Apartment</option>
                    <option value="townhouse">Townhouse</option>
                    <option value="villa">Villa</option>
                    <option value="land">Land</option>
                    <option value="commercial">Commercial</option>
                    <option value="guest_house">Guest House</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Listing Type
                </label>
                <select 
                    v-model="currentFilters.listing_type"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="">All Listings</option>
                    <option value="for_sale">For Sale</option>
                    <option value="for_rent">For Rent</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Bedrooms
                </label>
                <select 
                    v-model="currentFilters.bedrooms"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
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
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Village
                </label>
                <select 
                    v-model="currentFilters.village"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="">All Areas</option>
                    <option value="seminyak">Seminyak</option>
                    <option value="canggu">Canggu</option>
                    <option value="ubud">Ubud</option>
                    <option value="sanur">Sanur</option>
                    <option value="denpasar">Denpasar</option>
                    <option value="kuta">Kuta</option>
                    <option value="legian">Legian</option>
                    <option value="jimbaran">Jimbaran</option>
                    <option value="nusa_dua">Nusa Dua</option>
                </select>
            </div>
        </div>
        
        <!-- Filter Actions -->
        <div class="mt-6 flex justify-between items-center">
            <button 
                v-if="hasActiveFilters()"
                @click="clearFilters"
                class="text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 underline"
            >
                Clear all filters
            </button>
            <div v-else></div>
            
            <button 
                @click="applyFilters"
                class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
                Apply Filters
            </button>
        </div>

        <!-- Active Filters Display -->
        <div v-if="hasActiveFilters()" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex flex-wrap gap-2">
                <span class="text-sm text-gray-600 dark:text-gray-400 mr-2">Active filters:</span>
                <span 
                    v-for="(value, key) in currentFilters" 
                    :key="key"
                    v-show="value"
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                >
                    {{ key.replace('_', ' ') }}: {{ value }}
                    <button 
                        @click="currentFilters[key as keyof typeof currentFilters] = ''; applyFilters()"
                        class="ml-2 hover:text-blue-600 focus:outline-none"
                    >
                        ×
                    </button>
                </span>
            </div>
        </div>
    </div>
</template>