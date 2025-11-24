<script setup lang="ts">
import { Property } from '@/types';
import { formatCurrency, formatListingType, formatPropertyType } from '@/utils';
import { Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

interface Props {
    properties?: Property[];
}

const { properties } = defineProps<Props>();

const sortBy = ref<'views' | 'price'>('views');
const filterType = ref<string>('all');

const propertyTypes = computed(() => {
    const types = [...new Set(properties?.map(p => p.property_type) ?? [])];
    return types;
});

const filteredAndSortedProperties = computed(() => {
    let filtered = properties;
    
    if (filterType.value !== 'all') {
        filtered = properties?.filter(p => p.property_type === filterType.value);
    }
    
    return filtered?.sort((a, b) => {
        if (sortBy.value === 'views') {
            return b.view_count - a.view_count;
        } else {
            return (b.price ?? 0) - (a.price ?? 0);
        }
    }).slice(0, 10);
});

const getListingTypeBadge = (type: string) => {
    const badges = {
        for_sale: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300',
        for_rent: 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300',
        sold: 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-300',
        off_market: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300'
    };
    return badges[type as keyof typeof badges] || badges.for_sale;
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Top Properties</h3>
            
            <div class="flex flex-col sm:flex-row gap-2 mt-4 sm:mt-0">
                <!-- Sort Options -->
                <select 
                    v-model="sortBy"
                    class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                >
                    <option value="views">Sort by Views</option>
                    <option value="price">Sort by Price</option>
                </select>
                
                <!-- Filter Options -->
                <select 
                    v-model="filterType"
                    class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                >
                    <option value="all">All Types</option>
                    <option v-for="type in propertyTypes" :key="type" :value="type">
                        {{ formatPropertyType(type) }}
                    </option>
                </select>
            </div>
        </div>

        <div class="space-y-4">
            <div 
                v-for="(property, index) in filteredAndSortedProperties" 
                :key="property.id"
                class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
            >
                <div class="flex items-center space-x-4">
                    <!-- Rank -->
                    <div class="flex-shrink-0 w-8 h-8 bg-blue-100 dark:bg-blue-900/20 rounded-full flex items-center justify-center">
                        <span class="text-sm font-semibold text-blue-600 dark:text-blue-400">{{ index + 1 }}</span>
                    </div>
                    
                    <!-- Property Info -->
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center space-x-2 mb-1">
                            <Link 
                                :href="route('properties.show', property.id)"
                                class="font-medium text-gray-900 dark:text-gray-100 hover:text-blue-600 dark:hover:text-blue-400 truncate"
                            >
                                {{ property.title }}
                            </Link>
                            <span :class="['inline-flex items-center px-2 py-1 rounded-full text-xs font-medium', getListingTypeBadge(property.listing_type)]">
                                {{ formatListingType(property.listing_type) }}
                            </span>
                        </div>
                        <div class="flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                            <span>{{ property.property_id }}</span>
                            <span>{{ property.district }}, {{ property.regency }}</span>
                            <span v-if="property.bedrooms">{{ property.bedrooms }}bd</span>
                            <span v-if="property.bathrooms">{{ property.bathrooms }}ba</span>
                        </div>
                    </div>
                </div>

                <!-- Stats -->
                <div class="flex items-center space-x-6 text-sm">
                    <div class="text-right">
                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ formatCurrency(property.price ?? 0) }}</div>
                        <div class="text-gray-500 dark:text-gray-400">{{ property.view_count }} views</div>
                    </div>
                    <Link 
                        :href="route('properties.show', property.id)"
                        class="flex-shrink-0 text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </div>
            </div>
        </div>

        <div class="mt-4 text-center">
            <Link 
                href="/admin/properties"
                class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 font-medium"
            >
                View All Properties →
            </Link>
        </div>
    </div>
</template>