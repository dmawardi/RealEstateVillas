<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { ChevronDown, ChevronUp, X, Plus, Check } from 'lucide-vue-next';
import { PropertyApi } from '@/services/api/properties';

interface Feature {
    id: number;
    name: string;
    slug: string;
    category: string;
    icon?: string;
}

interface FeaturesByCategory {
    [category: string]: Feature[];
}

interface Props {
    modelValue: number[];
}

interface Emits {
    (e: 'update:modelValue', value: number[]): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

// State
const features = ref<FeaturesByCategory>({});
const isLoading = ref(true);
const error = ref<string | null>(null);
const expandedCategories = ref<Set<string>>(new Set());

// Computed
const selectedFeatures = computed({
    get: () => props.modelValue,
    set: (value: number[]) => emit('update:modelValue', value)
});

const selectedFeaturesData = computed(() => {
    const selected: Feature[] = [];
    Object.values(features.value).forEach(categoryFeatures => {
        categoryFeatures.forEach(feature => {
            if (selectedFeatures.value.includes(feature.id)) {
                selected.push(feature);
            }
        });
    });
    return selected;
});

const categoriesWithCounts = computed(() => {
    const categories: Array<{
        name: string;
        features: Feature[];
        selectedCount: number;
        totalCount: number;
    }> = [];

    Object.entries(features.value).forEach(([categoryName, categoryFeatures]) => {
        const selectedCount = categoryFeatures.filter(feature => 
            selectedFeatures.value.includes(feature.id)
        ).length;

        categories.push({
            name: categoryName,
            features: categoryFeatures,
            selectedCount,
            totalCount: categoryFeatures.length
        });
    });

    return categories.sort((a, b) => a.name.localeCompare(b.name));
});

// Methods
const fetchFeatures = async () => {
    try {
        isLoading.value = true;
        error.value = null;
        
        const response: any = await PropertyApi.getAvailableFeatures();

        // The response is grouped by category, we need to flatten it
        if (response) {
            // Use the response directly as it's already grouped by category
            features.value = response;
            
            // Auto-expand categories that have selected features
            Object.entries(response).forEach(([categoryName, categoryFeatures]) => {
            // Convert Laravel Collection to array if needed and check for selected features
            const featuresArray = Array.isArray(categoryFeatures) ? categoryFeatures : Object.values(categoryFeatures as Record<string, Feature>);
            const hasSelected = featuresArray.some((feature: Feature) => 
                selectedFeatures.value.includes(feature.id)
            );
            if (hasSelected) {
                expandedCategories.value.add(categoryName);
            }

            isLoading.value = false;

        });
        } else {
            console.error('API returned empty response');
            features.value = {};
        }
    } catch (error: any) {
        console.error('Failed to load available features:', error);
        features.value = {};
        error.value = 'Failed to load features';
    }
};

const toggleFeature = (featureId: number) => {
    const currentFeatures = [...selectedFeatures.value];
    const index = currentFeatures.indexOf(featureId);
    
    if (index > -1) {
        // Remove feature
        currentFeatures.splice(index, 1);
    } else {
        // Add feature
        currentFeatures.push(featureId);
    }
    
    selectedFeatures.value = currentFeatures;
};

const removeFeature = (featureId: number) => {
    selectedFeatures.value = selectedFeatures.value.filter(id => id !== featureId);
};

const clearAllFeatures = () => {
    selectedFeatures.value = [];
};

const toggleCategory = (categoryName: string) => {
    if (expandedCategories.value.has(categoryName)) {
        expandedCategories.value.delete(categoryName);
    } else {
        expandedCategories.value.add(categoryName);
    }
};

const isFeatureSelected = (featureId: number) => {
    return selectedFeatures.value.includes(featureId);
};

const isCategoryExpanded = (categoryName: string) => {
    return expandedCategories.value.has(categoryName);
};

// Lifecycle
onMounted(() => {
    fetchFeatures();
});
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <label class="block text-sm font-medium text-gray-900 dark:text-gray-100">
                Property Features
            </label>
            <button
                v-if="selectedFeatures.length > 0"
                @click="clearAllFeatures"
                class="text-xs text-gray-500 hover:text-red-500 dark:text-gray-400 dark:hover:text-red-400 transition-colors"
            >
                Clear all
            </button>
        </div>

        <!-- Selected Features Summary -->
        <div v-if="selectedFeatures.length > 0" class="space-y-2">
            <div class="text-sm text-gray-600 dark:text-gray-400">
                {{ selectedFeatures.length }} feature{{ selectedFeatures.length !== 1 ? 's' : '' }} selected
            </div>
            <div class="flex flex-wrap gap-2">
                <span
                    v-for="feature in selectedFeaturesData"
                    :key="feature.id"
                    class="inline-flex items-center px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 text-xs rounded-full"
                >
                    {{ feature.name }}
                    <button
                        @click="removeFeature(feature.id)"
                        class="ml-1 hover:text-red-600 dark:hover:text-red-400 transition-colors"
                    >
                        <X class="w-3 h-3" />
                    </button>
                </span>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="text-center py-4">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mx-auto"></div>
            <div class="text-sm text-gray-500 dark:text-gray-400 mt-2">Loading features...</div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="text-center py-4">
            <div class="text-red-600 dark:text-red-400 text-sm">{{ error }}</div>
            <button
                @click="fetchFeatures"
                class="mt-2 text-xs text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
            >
                Try again
            </button>
        </div>

        <!-- Features by Category -->
        <div v-else-if="Object.keys(features).length > 0" class="space-y-3">
            <div
                v-for="category in categoriesWithCounts"
                :key="category.name"
                class="border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden"
            >
                <!-- Category Header -->
                <button
                    @click="toggleCategory(category.name)"
                    class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors flex items-center justify-between text-left"
                >
                    <div class="flex items-center space-x-2">
                        <span class="font-medium text-gray-900 dark:text-gray-100 capitalize">
                            {{ category.name }}
                        </span>
                        <span
                            v-if="category.selectedCount > 0"
                            class="px-2 py-0.5 bg-blue-600 text-white text-xs rounded-full"
                        >
                            {{ category.selectedCount }}
                        </span>
                    </div>
                    <component
                        :is="isCategoryExpanded(category.name) ? ChevronUp : ChevronDown"
                        class="w-4 h-4 text-gray-500 dark:text-gray-400"
                    />
                </button>

                <!-- Category Features -->
                <div
                    v-if="isCategoryExpanded(category.name)"
                    class="p-4 bg-white dark:bg-gray-800"
                >
                    <div class="grid grid-cols-1 gap-2">
                        <button
                            v-for="feature in category.features"
                            :key="feature.id"
                            @click="toggleFeature(feature.id)"
                            class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-left"
                            :class="{
                                'bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700': isFeatureSelected(feature.id)
                            }"
                        >
                            <!-- Selection Indicator -->
                            <div
                                class="flex-shrink-0 w-4 h-4 rounded border-2 flex items-center justify-center transition-colors"
                                :class="{
                                    'bg-blue-600 border-blue-600': isFeatureSelected(feature.id),
                                    'border-gray-300 dark:border-gray-500': !isFeatureSelected(feature.id)
                                }"
                            >
                                <Check
                                    v-if="isFeatureSelected(feature.id)"
                                    class="w-3 h-3 text-white"
                                />
                            </div>

                            <!-- Feature Icon (if available) -->
                            <div
                                v-if="feature.icon"
                                class="flex-shrink-0 w-5 h-5 text-gray-400 dark:text-gray-500"
                                v-html="feature.icon"
                            />

                            <!-- Feature Name -->
                            <span
                                class="text-sm font-medium transition-colors"
                                :class="{
                                    'text-blue-900 dark:text-blue-100': isFeatureSelected(feature.id),
                                    'text-gray-700 dark:text-gray-200': !isFeatureSelected(feature.id)
                                }"
                            >
                                {{ feature.name }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-8">
            <div class="text-gray-500 dark:text-gray-400 text-sm">
                No features available
            </div>
        </div>
    </div>
</template>