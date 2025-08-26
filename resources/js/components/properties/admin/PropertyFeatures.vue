<script setup lang="ts">
import { Feature } from '@/types';

interface Props {
    features: Feature[];
}

const { features } = defineProps<Props>();

// Helper function to get feature icon based on name
const getFeatureIcon = (featureName: string) => {
    const iconMap: Record<string, string> = {
        'swimming pool': '🏊‍♂️',
        'wifi': '📶',
        'air conditioning': '❄️',
        'parking': '🚗',
        'garden': '🌿',
        'balcony': '🏠',
        'terrace': '🏡',
        'kitchen': '🍳',
        'gym': '💪',
        'security': '🔒',
        'laundry': '👕',
        'furnished': '🛋️',
        'pets allowed': '🐕',
        'beach access': '🏖️',
        'mountain view': '🏔️',
        'ocean view': '🌊',
        'rice field view': '🌾',
    };
    
    const normalizedName = featureName.toLowerCase();
    return iconMap[normalizedName] || '✓';
};

// Group features by category (optional enhancement)
const groupedFeatures = features.reduce((groups: Record<string, Feature[]>, feature) => {
    const category = feature.category || 'Other';
    if (!groups[category]) {
        groups[category] = [];
    }
    groups[category].push(feature);
    return groups;
}, {});

const hasCategories = Object.keys(groupedFeatures).length > 1 && features.some(f => f.category);
</script>

<template>
    <div v-if="features.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    Property Features
                    <span class="text-sm font-normal text-gray-500 ml-2">({{ features.length }})</span>
                </h2>
                <div class="flex space-x-2">
                    <span class="text-xs text-gray-500 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                        {{ features.length }} features
                    </span>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <!-- Grouped Features (if categories exist) -->
            <div v-if="hasCategories" class="space-y-6">
                <div v-for="(categoryFeatures, categoryName) in groupedFeatures" :key="categoryName">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3 uppercase tracking-wide">
                        {{ categoryName }}
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        <div 
                            v-for="feature in categoryFeatures" 
                            :key="feature.id"
                            class="flex items-center space-x-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
                        >
                            <div class="flex-shrink-0">
                                <span class="text-lg">{{ getFeatureIcon(feature.name) }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                    {{ feature.name }}
                                </p>
                                <div class="flex items-center space-x-2 mt-1">
                                    <span v-if="feature.pivot?.quantity && feature.pivot.quantity > 1" 
                                          class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ feature.pivot.quantity }}x
                                    </span>
                                    <p v-if="feature.pivot?.notes" class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                        {{ feature.pivot.notes }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Simple Grid Layout (no categories) -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div 
                    v-for="feature in features" 
                    :key="feature.id"
                    class="flex items-center space-x-3 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors border border-gray-200 dark:border-gray-600"
                >
                    <div class="flex-shrink-0">
                        <span class="text-xl">{{ getFeatureIcon(feature.name) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ feature.name }}
                        </p>
                        <div class="flex items-center space-x-2 mt-1">
                            <span v-if="feature.pivot?.quantity && feature.pivot.quantity > 1" 
                                  class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                Qty: {{ feature.pivot.quantity }}
                            </span>
                        </div>
                        <p v-if="feature.pivot?.notes" class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">
                            {{ feature.pivot.notes }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Feature Summary -->
            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                <div class="flex flex-wrap gap-2">
                    <span 
                        v-for="feature in features.slice(0, 8)" 
                        :key="`tag-${feature.id}`"
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200"
                    >
                        {{ getFeatureIcon(feature.name) }} {{ feature.name }}
                    </span>
                    <span v-if="features.length > 8" 
                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                        +{{ features.length - 8 }} more
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- No Features State -->
    <div v-else class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Property Features</h2>
        </div>
        <div class="p-6">
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No features listed</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    This property doesn't have any features listed yet.
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>