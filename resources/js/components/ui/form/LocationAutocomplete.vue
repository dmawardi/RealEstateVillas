<script setup lang="ts">
// ================================================================
// IMPORTS AND TYPE DEFINITIONS
// ================================================================

// Vue composition API imports for reactivity and lifecycle management
import { ref, computed, onMounted, watch } from 'vue';
// API service for making HTTP requests to the backend
import { api } from '@/services/api';
// Type definition for location objects
import { Location } from '@/types';

// ================================================================
// COMPONENT INTERFACE DEFINITIONS
// ================================================================

/**
 * Props interface defining what data this component accepts from parent
 * - modelValue: Array of selected location names (for v-model binding)
 * - placeholder: Text shown in the input when empty
 * - maxSelections: Maximum number of locations user can select
 */
interface Props {
    modelValue: Location[];
    placeholder?: string;
    maxSelections?: number;
}

/**
 * Events interface defining what events this component can emit to parent
 * - update:modelValue: Emitted when selected locations change (enables v-model)
 */
interface Emits {
    (e: 'update:modelValue', value: Location[]): void;
}

// ================================================================
// COMPONENT SETUP AND CONFIGURATION
// ================================================================

/**
 * Define props with default values
 * withDefaults provides fallback values if parent doesn't specify them
 */
const props = withDefaults(defineProps<Props>(), {
    placeholder: 'Search locations...',  // Default placeholder text
    maxSelections: 10                    // Default maximum selections allowed
});

/**
 * Define events this component can emit
 * Used for two-way data binding with parent component
 */
const emit = defineEmits<Emits>();

// ================================================================
// REACTIVE STATE VARIABLES
// ================================================================

/**
 * User's current search input - what they're typing in the input field
 * Reactive ref so Vue tracks changes and updates UI accordingly
 */
const searchQuery = ref('');

/**
 * Whether the dropdown list is currently visible
 * Controls showing/hiding the location suggestions
 */
const isOpen = ref(false);

/**
 * Whether we're currently fetching location data from API
 * Used to show loading spinner and prevent multiple simultaneous requests
 */
const isLoading = ref(false);

/**
 * Complete list of all available locations loaded from API
 * Contains all villages, districts, and regencies that can be selected
 */
const allLocations = ref<Location[]>([]);

/**
 * Reference to the HTML input element
 * Allows us to programmatically focus/blur the input field
 */
const inputRef = ref<HTMLInputElement>();

// ================================================================
// COMPUTED PROPERTIES (DERIVED STATE)
// ================================================================

/**
 * Two-way binding for selected locations
 * Getter: Returns current selected locations from props
 * Setter: Emits update event to parent when selections change
 * This enables v-model functionality on the component
 */
const selectedLocations = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
});

/**
 * Filtered list of locations based on user's search query
 * Returns different results based on whether user is searching:
 * - No search: Shows first 50 locations (performance optimization)
 * - With search: Shows up to 20 locations matching the query
 * Searches both location name and parent location name
 */
const filteredLocations = computed(() => {
    // If no search query, show first 50 locations to avoid overwhelming UI
    if (!searchQuery.value.trim()) {
        return allLocations.value.slice(0, 50);
    }
    
    // Convert search query to lowercase for case-insensitive matching
    const query = searchQuery.value.toLowerCase();
    
    // Filter locations by name or parent name, limit to 20 results
    return allLocations.value
        .filter(location => 
            location.name.toLowerCase().includes(query) ||
            (location.parent && location.parent.toLowerCase().includes(query))
        )
        .slice(0, 20); // Limit search results for performance
});

/**
 * Whether user can add more locations
 * Prevents selection beyond the maximum allowed limit
 */
const canAddMore = computed(() => {
    return selectedLocations.value.length < props.maxSelections;
});

/**
 * Whether user has selected any locations
 * Used to conditionally show the selected locations display area
 */
const hasSelections = computed(() => {
    return selectedLocations.value.length > 0;
});

// ================================================================
// API AND DATA LOADING METHODS
// ================================================================

/**
 * Loads all available locations from the backend API
 * Transforms the API response into a standardized format
 * Includes error handling with fallback data for development
 */
const loadLocations = async () => {
    // Set loading state to show spinner
    isLoading.value = true;
    
    try {
        // Make API call to get locations data
        await api.properties.getAllLocations({
            // Success callback - transform API response to our format
            onSuccess: (response: any) => {
                // Initialize array to hold transformed location objects
                const locations: Location[] = [];
                
                // Transform villages from API response
                if (response.villages) {
                    response.villages.forEach((village: string) => {
                        locations.push({
                            name: village,
                            type: 'village'
                        });
                    });
                }
                
                // Transform districts from API response
                if (response.districts) {
                    response.districts.forEach((district: string) => {
                        locations.push({
                            name: district,
                            type: 'district'
                        });
                    });
                }
                
                // Transform regencies from API response
                if (response.regencies) {
                    response.regencies.forEach((regency: string) => {
                        locations.push({
                            name: regency,
                            type: 'regency'
                        });
                    });
                }
                
                // Update reactive state with transformed data
                allLocations.value = locations;
                console.log('Locations loaded:', allLocations.value.length);
            },
            
            // Error callback - handle API failures gracefully
            onError: (errors: any) => {
                console.error('Failed to load locations:', errors);
                // Use fallback data if API fails (useful for development)
                allLocations.value = generateFallbackLocations();
            }
        });
    } catch (error) {
        // Handle any unexpected errors (network issues, etc.)
        console.error('Error loading locations:', error);
        allLocations.value = generateFallbackLocations();
    } finally {
        // Always clear loading state when done (success or failure)
        isLoading.value = false;
    }
};

/**
 * Generates hardcoded fallback location data
 * Used when API is unavailable or fails to load
 * Contains common Bali locations for development/testing
 */
const generateFallbackLocations = (): Location[] => {
    return [
        // Regencies (top-level administrative divisions)
        { name: 'Badung', type: 'regency' },
        { name: 'Gianyar', type: 'regency' },
        { name: 'Denpasar', type: 'regency' },
        { name: 'Buleleng', type: 'regency' },
        { name: 'Tabanan', type: 'regency' },
        { name: 'Klungkung', type: 'regency' },
        { name: 'Karangasem', type: 'regency' },
        
        // Villages with their parent regencies
        { name: 'Seminyak', type: 'village', parent: 'Badung' },
        { name: 'Canggu', type: 'village', parent: 'Badung' },
        { name: 'Kuta', type: 'village', parent: 'Badung' },
        { name: 'Legian', type: 'village', parent: 'Badung' },
        { name: 'Jimbaran', type: 'village', parent: 'Badung' },
        { name: 'Nusa Dua', type: 'village', parent: 'Badung' },
        { name: 'Ubud', type: 'village', parent: 'Gianyar' },
        { name: 'Sanur', type: 'village', parent: 'Denpasar' },
        { name: 'Lovina', type: 'village', parent: 'Buleleng' },
        { name: 'Tanah Lot', type: 'village', parent: 'Tabanan' },
        { name: 'Nusa Penida', type: 'village', parent: 'Klungkung' },
        { name: 'Amed', type: 'village', parent: 'Karangasem' },
        { name: 'Candidasa', type: 'village', parent: 'Karangasem' }
    ];
};

// ================================================================
// USER INTERACTION METHODS
// ================================================================

/**
 * Adds a location to the selected list when user clicks on it
 * Includes validation to prevent duplicates and enforce selection limits
 * 
 * @param location - The location object to add to selections
 */
const addLocation = (location: Location) => {
    // Check if location isn't already selected AND user can add more
    if (!selectedLocations.value.some(selected => selected.name === location.name) && canAddMore.value) {
        // Add location object to selected list (creates new array for reactivity)
        selectedLocations.value = [...selectedLocations.value, location];
        
        // Clear search input after selection
        searchQuery.value = '';
    }
};

/**
 * Removes a location from the selected list
 * Called when user clicks the × button on a selected location tag
 * 
 * @param locationName - Name of the location to remove
 */
const removeLocation = (location: Location) => {
    // Filter out the specified location (creates new array for reactivity)
    selectedLocations.value = selectedLocations.value.filter(selected => selected.name !== location.name);
};

/**
 * Clears all selected locations and search query
 * Called when user clicks "Clear all" button
 */
const clearAll = () => {
    selectedLocations.value = [];  // Empty the selections array
    searchQuery.value = '';        // Clear the search input
};

// ================================================================
// INPUT FOCUS AND BLUR HANDLING
// ================================================================

/**
 * Handles when user focuses on the input field
 * Opens the dropdown to show available locations
 */
const handleInputFocus = () => {
    isOpen.value = true;
};

/**
 * Handles when user clicks away from the input field
 * Closes the dropdown with a small delay to allow for location selection clicks
 * The delay prevents the dropdown from closing before a click event registers
 */
const handleInputBlur = () => {
    // 200ms delay allows time for dropdown click events to complete
    setTimeout(() => {
        isOpen.value = false;
    }, 200);
};

/**
 * Handles keyboard events when user is typing in the input
 * Currently only handles Escape key to close dropdown and blur input
 * 
 * @param event - The keyboard event object
 */
const handleKeydown = (event: KeyboardEvent) => {
    if (event.key === 'Escape') {
        isOpen.value = false;        // Close the dropdown
        inputRef.value?.blur();      // Remove focus from input
    }
};

// ================================================================
// UI DISPLAY HELPER METHODS
// ================================================================

/**
 * Returns an emoji icon based on location type
 * Used for visual distinction in the dropdown list
 * 
 * @param type - The location type ('regency', 'district', 'village')
 * @returns Appropriate emoji string
 */
const getLocationTypeIcon = (type: string) => {
    switch (type) {
        case 'regency': return '🏛️';  // Government building for regencies
        case 'district': return '🏘️'; // Houses for districts
        case 'village': return '🏡';   // House for villages
        default: return '📍';          // Pin for unknown types
    }
};

/**
 * Returns a human-readable label for location type
 * Used in the dropdown to show location type badges
 * 
 * @param type - The location type identifier
 * @returns Capitalized, readable type name
 */
const getLocationTypeLabel = (type: string) => {
    switch (type) {
        case 'regency': return 'Regency';
        case 'district': return 'District';
        case 'village': return 'Village';
        default: return 'Location';
    }
};

// ================================================================
// REACTIVE WATCHERS
// ================================================================

/**
 * Watches for changes in the modelValue prop from parent
 * Clears the search query when all selections are removed
 * This provides a clean state when parent resets the component
 */
watch(() => props.modelValue, (newValue) => {
    if (newValue.length === 0) {
        searchQuery.value = '';
    }
});

// ================================================================
// COMPONENT LIFECYCLE
// ================================================================

/**
 * Component mount lifecycle hook
 * Automatically loads location data when component is first rendered
 * This ensures data is available as soon as user can interact with component
 */
onMounted(() => {
    loadLocations();
});
</script>

<template>
    <!-- ================================================================ -->
    <!-- MAIN CONTAINER - Relative positioning for dropdown positioning -->
    <!-- ================================================================ -->
    <div class="relative">
        
        <!-- ================================================================ -->
        <!-- SEARCH INPUT SECTION - Main interaction element -->
        <!-- ================================================================ -->
        <div class="relative">
            <!-- Main text input for searching locations -->
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
            
            <!-- Loading spinner shown during API calls -->
            <div v-if="isLoading" class="absolute right-3 top-1/2 transform -translate-y-1/2">
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div>
            </div>
        </div>

        <!-- ================================================================ -->
        <!-- DROPDOWN RESULTS - Location suggestions list -->
        <!-- ================================================================ -->
        <!-- Only show when: dropdown is open, not loading, and has results -->
        <div 
            v-if="isOpen && !isLoading && filteredLocations.length > 0" 
            class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg max-h-60 overflow-y-auto"
        >
            <!-- Individual location option -->
            <div 
                v-for="location in filteredLocations" 
                :key="`${location.name}-${location.type}`"
                @click="addLocation(location)"
                class="px-3 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700 last:border-b-0"                
                :class="{
                    'opacity-50 cursor-not-allowed': selectedLocations.some(selected => selected.name === location.name),
                    'bg-gray-50 dark:bg-gray-750': selectedLocations.some(selected => selected.name === location.name)
                }"
            >
                <!-- Location information layout -->
                <div class="flex items-center justify-between">
                    <!-- Left side: Icon and location details -->
                    <div class="flex items-center space-x-2">
                        <!-- Type-specific emoji icon -->
                        <span class="text-sm">{{ getLocationTypeIcon(location.type) }}</span>
                        
                        <!-- Location name and parent hierarchy -->
                        <div>
                            <!-- Primary location name -->
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ location.name }}
                            </div>
                            <!-- Parent location (if exists) -->
                            <div v-if="location.parent" class="text-xs text-gray-500 dark:text-gray-400">
                                {{ location.parent }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right side: Type badge and selection indicator -->
                    <div class="flex items-center space-x-2">
                        <!-- Location type badge -->
                        <span class="text-xs px-2 py-1 bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-300 rounded">
                            {{ getLocationTypeLabel(location.type) }}
                        </span>
                        <!-- Checkmark for selected locations -->
                        <span v-if="selectedLocations.some(selected => selected.name === location.name)" class="text-green-600 dark:text-green-400 text-sm">
                            ✓
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================================================================ -->
        <!-- NO RESULTS MESSAGE - Shown when search yields no matches -->
        <!-- ================================================================ -->
        <div 
            v-if="isOpen && !isLoading && searchQuery && filteredLocations.length === 0"
            class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg p-3"
        >
            <div class="text-sm text-gray-500 dark:text-gray-400 text-center">
                No locations found for "{{ searchQuery }}"
            </div>
        </div>
        <!-- ================================================================ -->
        <!-- SELECTION LIMIT WARNING - Shown when max selections reached -->
        <!-- ================================================================ -->
        <div v-if="!canAddMore" class="mt-1 text-xs text-amber-600 dark:text-amber-400">
            Maximum {{ maxSelections }} locations selected
        </div>
    </div>
</template>