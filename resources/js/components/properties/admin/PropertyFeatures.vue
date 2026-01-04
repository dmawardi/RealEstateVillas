

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { Property, Feature } from '@/types';
import { PropertyApi } from '@/services/api/properties';

interface Props {
    property: Property;
    editable?: boolean;
}

const props = defineProps<Props>();

// Editing state
const isEditing = ref(false);
const isLoading = ref(false);
const searchQuery = ref('');
const availableFeatures = ref<Feature[]>([]);
const selectedFeatures = ref<Array<{
    id: number;
    name: string;
    category: string;
    icon?: string;
    is_quantifiable: boolean;
    quantity?: number;
    notes?: string;
}>>([]);

// Initialize selected features from property
const initializeSelectedFeatures = () => {
    selectedFeatures.value = (props.property.features || []).map(feature => ({
        id: feature.id,
        name: feature.name,
        category: feature.category,
        icon: feature.icon,
        is_quantifiable: feature.is_quantifiable,
        quantity: feature.pivot.quantity || undefined,
        notes: feature.pivot.notes || undefined,
    }));
};

// Search functionality
const filteredAvailableFeatures = computed(() => {
    if (!searchQuery.value) return [];
    
    const query = searchQuery.value.toLowerCase();
    const selectedIds = selectedFeatures.value.map(f => f.id);
    
    return availableFeatures.value
        .filter(feature => 
            !selectedIds.includes(feature.id) &&
            (feature.name.toLowerCase().includes(query) ||
             feature.category.toLowerCase().includes(query))
        )
        .slice(0, 10); // Limit results for performance
});

// Load available features from API
const loadAvailableFeatures = async () => {
    try {
        const response: any = await PropertyApi.getAvailableFeatures();

        // The response is grouped by category, we need to flatten it
        if (response) {
            console.log('Available features loaded:', response);
            // Flatten the grouped features into a single array
            availableFeatures.value = Object.values(response).flat() as Feature[];
        } else {
            console.error('API returned empty response');
            availableFeatures.value = [];
        }
    } catch (error) {
        console.error('Failed to load available features:', error);
        availableFeatures.value = [];
    }
};

// Add feature to selection
const addFeature = (feature: Feature) => {
    selectedFeatures.value.push({
        id: feature.id,
        name: feature.name,
        category: feature.category,
        icon: feature.icon,
        is_quantifiable: feature.is_quantifiable,
        quantity: feature.is_quantifiable ? 1 : undefined,
        notes: undefined,
    });
    searchQuery.value = '';
};

// Remove feature from selection
const removeFeature = (featureId: number) => {
    selectedFeatures.value = selectedFeatures.value.filter(f => f.id !== featureId);
};

// Update feature quantity
const updateQuantity = (featureId: number, quantity: number | undefined) => {
    const feature = selectedFeatures.value.find(f => f.id === featureId);
    if (feature) {
        feature.quantity = quantity;
    }
};

// Update feature notes
const updateNotes = (featureId: number, notes: string) => {
    const feature = selectedFeatures.value.find(f => f.id === featureId);
    if (feature) {
        feature.notes = notes || undefined;
    }
};

// Start editing
const startEditing = () => {
    isEditing.value = true;
    initializeSelectedFeatures();
    loadAvailableFeatures();
};

// Cancel editing
const cancelEditing = () => {
    isEditing.value = false;
    searchQuery.value = '';
    selectedFeatures.value = [];
};

// Save changes using web route
const saveFeatures = () => {
    isLoading.value = true;
    
    const featuresData = selectedFeatures.value.map(feature => ({
        id: feature.id,
        ...(feature.quantity !== undefined && { quantity: feature.quantity }),
        ...(feature.notes && { notes: feature.notes }),
    }));
    
    router.patch(
        route('admin.properties.features.update', props.property.slug),
        { features: featuresData },
        {
            preserveScroll: true,
            onSuccess: () => {
                isEditing.value = false;
                searchQuery.value = '';
                selectedFeatures.value = [];
            },
            onError: (errors) => {
                console.error('Failed to update features:', errors);
                alert('Failed to update features. Please try again.');
            },
            onFinish: () => {
                isLoading.value = false;
            }
        }
    );
};

// Helper functions
const getCategoryColor = (category: string) => {
    const colors = {
        'interior': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        'exterior': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        'security': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        'accessibility': 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
        'amenity': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        'comfort': 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-200',
        'entertainment': 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200',
    };
    return colors[category as keyof typeof colors] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
};

const formatCategoryName = (category: string) => {
    return category.charAt(0).toUpperCase() + category.slice(1).replace('_', ' ');
};

// Quick remove feature using DELETE route
const quickRemoveFeature = (featureId: number) => {
    if (confirm('Are you sure you want to remove this feature from the property?')) {
        router.delete(
            route('admin.properties.features.detach', [props.property.id, featureId]),
            {
                preserveScroll: true,
                onSuccess: () => {
                    // Feature will be removed from the property and page will refresh
                },
                onError: (errors) => {
                    console.error('Failed to remove feature:', errors);
                    alert('Failed to remove feature. Please try again.');
                }
            }
        );
    }
};

// Initialize component
onMounted(() => {
    initializeSelectedFeatures();
});
</script>

<template>
    <div v-if="editable" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                    Property Features
                </h2>
                <button
                    v-if="editable && !isEditing"
                    @click="startEditing"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Features
                </button>
            </div>
        </div>

        <div class="p-6">
            <!-- Edit Mode -->
            <div v-if="isEditing" class="space-y-6">
                <!-- Search and Add Features -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Search & Add Features
                    </label>
                    <div class="relative">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Type to search features..."
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 pl-10"
                        />
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Search Results Dropdown -->
                    <div v-if="filteredAvailableFeatures.length > 0" class="mt-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg max-h-60 overflow-y-auto z-10 relative">
                        <button
                            v-for="feature in filteredAvailableFeatures"
                            :key="feature.id"
                            @click="addFeature(feature)"
                            class="w-full px-4 py-3 text-left hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-700 flex items-center justify-between border-b border-gray-100 dark:border-gray-700 last:border-b-0"
                        >
                            <div class="flex items-center space-x-3">
                                <div></div>
                                <div
                                    v-if="feature.icon"
                                    class="flex-shrink-0 w-5 h-5 text-gray-400 dark:text-gray-500"
                                    v-html="feature.icon"
                                />
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ feature.name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ formatCategoryName(feature.category) }}</div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span v-if="feature.is_quantifiable" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                    Quantifiable
                                </span>
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Selected Features as Pills -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Selected Features ({{ selectedFeatures.length }})
                    </label>
                    
                    <div v-if="selectedFeatures.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <p class="mt-2">No features selected</p>
                        <p class="text-sm">Search and click features above to add them</p>
                    </div>

                    <div v-else class="space-y-3">
                        <div
                            v-for="feature in selectedFeatures"
                            :key="feature.id"
                            class="flex items-center justify-between p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg"
                        >
                            <!-- Feature Info -->
                            <div class="flex items-center space-x-3 flex-1">
                                <div
                                    v-if="feature.icon"
                                    class="flex-shrink-0 w-5 h-5 text-gray-400 dark:text-gray-500"
                                    v-html="feature.icon"
                                />
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ feature.name }}</div>
                                    <span :class="['inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium', getCategoryColor(feature.category)]">
                                        {{ formatCategoryName(feature.category) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Controls -->
                            <div class="flex items-center space-x-3">
                                <!-- Quantity Input (if quantifiable) -->
                                <div v-if="feature.is_quantifiable" class="flex items-center space-x-2">
                                    <label class="text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">Quantity:</label>
                                    <input
                                        :value="feature.quantity || 1"
                                        @input="updateQuantity(feature.id, parseInt(($event.target as HTMLInputElement).value) || undefined)"
                                        type="number"
                                        min="1"
                                        max="999"
                                        class="w-16 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm"
                                    />
                                </div>

                                <!-- Notes Input -->
                                <div class="flex items-center space-x-2">
                                    <input
                                        :value="feature.notes || ''"
                                        @input="updateNotes(feature.id, ($event.target as HTMLInputElement).value)"
                                        type="text"
                                        placeholder="Add notes..."
                                        class="w-32 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm"
                                    />
                                </div>

                                <!-- Remove Button -->
                                <button
                                    @click="removeFeature(feature.id)"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full text-red-600 hover:bg-red-100 dark:hover:bg-red-900/20 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                    title="Remove feature"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button
                        @click="cancelEditing"
                        :disabled="isLoading"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Cancel
                    </button>
                    <button
                        @click="saveFeatures"
                        :disabled="isLoading"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg v-if="isLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ isLoading ? 'Saving...' : 'Update Features' }}
                    </button>
                </div>
            </div>

            <!-- View Mode -->
            <div v-else>
                <div v-if="property.features?.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <p class="mt-2">No features added to this property yet.</p>
                    <button
                        v-if="editable"
                        @click="startEditing"
                        class="mt-3 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium"
                    >
                        Add some features
                    </button>
                </div>

                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <div 
                        v-for="feature in property.features" 
                        :key="feature.id"
                        class="group flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
                    >
                        <div class="flex items-center space-x-3 flex-1">
                            <div
                                v-if="feature.icon"
                                class="flex-shrink-0 w-5 h-5 text-gray-400 dark:text-gray-500"
                                v-html="feature.icon"
                            />
                            <div>
                                <div class="font-medium text-gray-900 dark:text-gray-100 flex items-center space-x-2">
                                    <span>{{ feature.name }}</span>
                                    <span v-if="feature.pivot.quantity && feature.pivot.quantity > 1" class="text-sm text-gray-500 dark:text-gray-400">
                                        ({{ feature.pivot.quantity }})
                                    </span>
                                </div>
                                <div v-if="feature.pivot.notes" class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ feature.pivot.notes }}
                                </div>
                                <span :class="['inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium mt-1', getCategoryColor(feature.category)]">
                                    {{ formatCategoryName(feature.category) }}
                                </span>
                            </div>
                        </div>
                        
                        <!-- Quick Remove Button (appears on hover if editable) -->
                        <button
                            v-if="editable"
                            @click="quickRemoveFeature(feature.id)"
                            class="opacity-0 group-hover:opacity-100 p-1 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition-opacity"
                            title="Remove feature"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>