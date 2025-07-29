<script setup lang="ts">
import type { PropertyFilters } from '@/types';
import { reactive } from 'vue';
import BookingDateFilter from './_BookingDateFilter.vue';
import { api } from '@/services/api';


interface Props {
    filters?: PropertyFilters;
}

const { filters } = defineProps<Props>();

// Reactive filters state initialized with current filter values
const currentFilters = reactive({
    property_type: filters?.property_type || '',
    listing_type: filters?.listing_type || '',
    bedrooms: filters?.bedrooms || '',
    village: filters?.village || '',
    check_in_date: filters?.check_in_date || '',
    check_out_date: filters?.check_out_date || ''
});

// Apply filters function
const applyFilters = () => {
    // Remove empty filters
    const cleanFilters = Object.fromEntries(
        Object.entries(currentFilters).filter(([, value]) => value !== '')
    );

    // Use API service with filters as query parameters
    api.properties.getAllProperties(cleanFilters, {
        onSuccess: (response: any) => {
            console.log('Filtered properties loaded:', response);
        },
        onError: (errors: Record<string, string[]>) => {
            console.error('Failed to load filtered properties:', errors);
        }
    });
};

// Clear filters function
const clearFilters = () => {
    Object.keys(currentFilters).forEach(key => {
        currentFilters[key as keyof typeof currentFilters] = '';
    });
    
    api.properties.getAllProperties({}, {
        onSuccess: (response: any) => {
            console.log('All properties loaded:', response);
        },
        onError: (errors: Record<string, string[]>) => {
            console.error('Failed to load properties:', errors);
        }
    });
};

// Check if any filters are active
const hasActiveFilters = () => {
    return Object.values(currentFilters).some(value => value !== '');
};

// Handle date changes from BookingDateFilter
const onDatesChanged = (dates: { checkIn: string; checkOut: string }) => {
    currentFilters.check_in_date = dates.checkIn;
    currentFilters.check_out_date = dates.checkOut;
};

// Clear only booking dates
const clearBookingDates = () => {
    currentFilters.check_in_date = '';
    currentFilters.check_out_date = '';
};

// Check if booking dates are set
const hasBookingDates = () => {
    return currentFilters.check_in_date && currentFilters.check_out_date;
};

// Format filter display name
const formatFilterName = (key: string): string => {
    const names: Record<string, string> = {
        property_type: 'Property Type',
        listing_type: 'Listing Type',
        bedrooms: 'Bedrooms',
        village: 'Village',
        check_in_date: 'Check-in',
        check_out_date: 'Check-out',
    };
    return names[key] || key.replace('_', ' ');
};

// Format filter value for display
const formatFilterValue = (key: string, value: string): string => {
    if (key === 'check_in_date' || key === 'check_out_date') {
        return new Date(value).toLocaleDateString();
    }
    return value;
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-8">
        <!-- Booking date filter -->
        <div class="mb-6">
            <BookingDateFilter 
                    :check-in="currentFilters.check_in_date"
                    :check-out="currentFilters.check_out_date"
                    @update:check-in="currentFilters.check_in_date = $event"
                    @update:check-out="currentFilters.check_out_date = $event"
                    @dates-changed="onDatesChanged"
                />
        </div>

        <!-- Property filters -->
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
            <div class="flex items-center space-x-4">
                <button 
                    v-if="hasActiveFilters()"
                    @click="clearFilters"
                    class="text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 underline"
                >
                    Clear all filters
                </button>
                <button 
                    v-if="hasBookingDates()"
                    @click="clearBookingDates"
                    class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200 underline"
                >
                    Clear dates
                </button>
            </div>
            
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
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
                    :class="key.includes('date') 
                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' 
                        : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'"
                >
                    {{ formatFilterName(key) }}: {{ formatFilterValue(key, value) }}
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