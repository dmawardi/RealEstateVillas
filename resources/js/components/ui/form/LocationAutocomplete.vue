<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { api } from '@/services/api';
import { Location } from '@/types';

interface Props {
    modelValue: string[];
    placeholder?: string;
    maxSelections?: number;
}

interface Emits {
    (e: 'update:modelValue', value: string[]): void;
}

const props = withDefaults(defineProps<Props>(), {
    placeholder: 'Search locations...',
    maxSelections: 10
});

const emit = defineEmits<Emits>();

// Reactive state
const searchQuery = ref('');
const isOpen = ref(false);
const isLoading = ref(false);
const allLocations = ref<Location[]>([]);
const inputRef = ref<HTMLInputElement>();

// Computed properties
const selectedLocations = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
});

const filteredLocations = computed(() => {
    if (!searchQuery.value.trim()) {
        return allLocations.value.slice(0, 50); // Show first 50 when no search
    }
    
    const query = searchQuery.value.toLowerCase();
    return allLocations.value
        .filter(location => 
            location.name.toLowerCase().includes(query) ||
            (location.parent && location.parent.toLowerCase().includes(query))
        )
        .slice(0, 20); // Limit search results
});

const canAddMore = computed(() => {
    return selectedLocations.value.length < props.maxSelections;
});

const hasSelections = computed(() => {
    return selectedLocations.value.length > 0;
});

// Methods
const loadLocations = async () => {
    isLoading.value = true;
    try {
        await api.properties.getAllLocations({
            onSuccess: (response: any) => {
                // Transform the API response into the expected format
                const locations: Location[] = [];
                let idCounter = 1;
                
                // Add villages
                if (response.villages) {
                    response.villages.forEach((village: string) => {
                        locations.push({
                            id: idCounter++,
                            name: village,
                            type: 'village'
                        });
                    });
                }
                
                // Add districts
                if (response.districts) {
                    response.districts.forEach((district: string) => {
                        locations.push({
                            id: idCounter++,
                            name: district,
                            type: 'district'
                        });
                    });
                }
                
                // Add regencies
                if (response.regencies) {
                    response.regencies.forEach((regency: string) => {
                        locations.push({
                            id: idCounter++,
                            name: regency,
                            type: 'regency'
                        });
                    });
                }
                
                allLocations.value = locations;
                console.log('Locations loaded:', allLocations.value.length);
            },
            onError: (errors: any) => {
                console.error('Failed to load locations:', errors);
                // Fallback data for development
                allLocations.value = generateFallbackLocations();
            }
        });
    } catch (error) {
        console.error('Error loading locations:', error);
        allLocations.value = generateFallbackLocations();
    } finally {
        isLoading.value = false;
    }
};

const generateFallbackLocations = (): Location[] => {
    // Fallback data based on common Bali locations
    return [
        { id: 1, name: 'Badung', type: 'regency' },
        { id: 2, name: 'Seminyak', type: 'village', parent: 'Badung' },
        { id: 3, name: 'Canggu', type: 'village', parent: 'Badung' },
        { id: 4, name: 'Kuta', type: 'village', parent: 'Badung' },
        { id: 5, name: 'Legian', type: 'village', parent: 'Badung' },
        { id: 6, name: 'Jimbaran', type: 'village', parent: 'Badung' },
        { id: 7, name: 'Nusa Dua', type: 'village', parent: 'Badung' },
        { id: 8, name: 'Gianyar', type: 'regency' },
        { id: 9, name: 'Ubud', type: 'village', parent: 'Gianyar' },
        { id: 10, name: 'Sanur', type: 'village', parent: 'Denpasar' },
        { id: 11, name: 'Denpasar', type: 'regency' },
        { id: 12, name: 'Buleleng', type: 'regency' },
        { id: 13, name: 'Lovina', type: 'village', parent: 'Buleleng' },
        { id: 14, name: 'Tabanan', type: 'regency' },
        { id: 15, name: 'Tanah Lot', type: 'village', parent: 'Tabanan' },
        { id: 16, name: 'Klungkung', type: 'regency' },
        { id: 17, name: 'Nusa Penida', type: 'village', parent: 'Klungkung' },
        { id: 18, name: 'Karangasem', type: 'regency' },
        { id: 19, name: 'Amed', type: 'village', parent: 'Karangasem' },
        { id: 20, name: 'Candidasa', type: 'village', parent: 'Karangasem' }
    ];
};

const addLocation = (location: Location) => {
    if (!selectedLocations.value.includes(location.name) && canAddMore.value) {
        selectedLocations.value = [...selectedLocations.value, location.name];
        searchQuery.value = '';
        inputRef.value?.focus();
    }
};

const removeLocation = (locationName: string) => {
    selectedLocations.value = selectedLocations.value.filter(name => name !== locationName);
};

const clearAll = () => {
    selectedLocations.value = [];
    searchQuery.value = '';
};

const handleInputFocus = () => {
    isOpen.value = true;
};

const handleInputBlur = () => {
    // Delay closing to allow for selection clicks
    setTimeout(() => {
        isOpen.value = false;
    }, 200);
};

const handleKeydown = (event: KeyboardEvent) => {
    if (event.key === 'Escape') {
        isOpen.value = false;
        inputRef.value?.blur();
    }
};

const getLocationTypeIcon = (type: string) => {
    switch (type) {
        case 'regency': return '🏛️';
        case 'district': return '🏘️';
        case 'village': return '🏡';
        default: return '📍';
    }
};

const getLocationTypeLabel = (type: string) => {
    switch (type) {
        case 'regency': return 'Regency';
        case 'district': return 'District';
        case 'village': return 'Village';
        default: return 'Location';
    }
};

// Watchers
watch(() => props.modelValue, (newValue) => {
    if (newValue.length === 0) {
        searchQuery.value = '';
    }
});

// Lifecycle
onMounted(() => {
    loadLocations();
});
</script>

<template>
    <div class="relative">
        <!-- Selected locations display -->
        <div v-if="hasSelections" class="mb-2">
            <div class="flex flex-wrap gap-1 mb-2">
                <span 
                    v-for="location in selectedLocations" 
                    :key="location"
                    class="inline-flex items-center px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-xs rounded-full"
                >
                    {{ location }}
                    <button 
                        @click="removeLocation(location)"
                        class="ml-1 text-blue-600 dark:text-blue-300 hover:text-blue-800 dark:hover:text-blue-100 focus:outline-none"
                        type="button"
                    >
                        ×
                    </button>
                </span>
            </div>
            <button 
                @click="clearAll"
                class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 underline"
                type="button"
            >
                Clear all ({{ selectedLocations.length }})
            </button>
        </div>

        <!-- Search input -->
        <div class="relative">
            <input
                ref="inputRef"
                v-model="searchQuery"
                type="text"
                :placeholder="canAddMore ? placeholder : `Maximum ${maxSelections} locations selected`"
                :disabled="!canAddMore"
                @focus="handleInputFocus"
                @blur="handleInputBlur"
                @keydown="handleKeydown"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md 
                       bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                       focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                       disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:cursor-not-allowed"
            />
            
            <!-- Loading indicator -->
            <div v-if="isLoading" class="absolute right-3 top-1/2 transform -translate-y-1/2">
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div>
            </div>
        </div>

        <!-- Dropdown -->
        <div 
            v-if="isOpen && !isLoading && filteredLocations.length > 0" 
            class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg max-h-60 overflow-y-auto"
        >
            <div 
                v-for="location in filteredLocations" 
                :key="location.id"
                @click="addLocation(location)"
                class="px-3 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700 last:border-b-0"
                :class="{
                    'opacity-50 cursor-not-allowed': selectedLocations.includes(location.name),
                    'bg-gray-50 dark:bg-gray-750': selectedLocations.includes(location.name)
                }"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="text-sm">{{ getLocationTypeIcon(location.type) }}</span>
                        <div>
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ location.name }}
                            </div>
                            <div v-if="location.parent" class="text-xs text-gray-500 dark:text-gray-400">
                                {{ location.parent }}
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-xs px-2 py-1 bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-300 rounded">
                            {{ getLocationTypeLabel(location.type) }}
                        </span>
                        <span v-if="selectedLocations.includes(location.name)" class="text-green-600 dark:text-green-400 text-sm">
                            ✓
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- No results message -->
        <div 
            v-if="isOpen && !isLoading && searchQuery && filteredLocations.length === 0"
            class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg p-3"
        >
            <div class="text-sm text-gray-500 dark:text-gray-400 text-center">
                No locations found for "{{ searchQuery }}"
            </div>
        </div>

        <!-- Selection limit message -->
        <div v-if="!canAddMore" class="mt-1 text-xs text-amber-600 dark:text-amber-400">
            Maximum {{ maxSelections }} locations selected
        </div>
    </div>
</template>