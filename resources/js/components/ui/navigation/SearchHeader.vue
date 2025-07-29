<script setup lang="ts">
import { computed, ref } from 'vue';
import UpperNavigation from './UpperNavigation.vue';
import Input from '@/components/ui/form/Input.vue';
import SearchIcon from '../form/SearchIcon.vue';
import Modal from '@/components/Modal.vue';
import PriceFilter from '../filters/PriceFilter.vue';

const modalOpen = ref(false);
const form = ref({
    search: '',
    mode: 'rent' as 'rent' | 'buy',
    priceFilter: {
        minPrice: '',
        maxPrice: '',
        priceRate: 'monthly' as 'weekly' | 'monthly',
    },
    propertyTypes: [] as string[],
});

function handleSearch() {
    // Implement search logic here
    console.log('Searching for:', form.value.search, 'Mode:', form.value.mode);
    console.log('Modal Open:', modalOpen.value);
}

function selectMode(mode: 'rent' | 'buy') {
    form.value.mode = mode;
}

// Modal close handler (to be called within the modal)
function closeModal() {
    modalOpen.value = false;
}
</script>

<template>
    <header class="bg-white dark:bg-gray-800 shadow p-4 w-10/12 mx-auto">
        <UpperNavigation />
        
        <!-- Search Section -->
        <div class="mx-auto bg-blue-300 w-full h-80 flex flex-col items-center justify-center rounded-md shadow-md mt-4">
            <!-- Rent/Buy Toggle Bar - positioned above search bar -->
            <div>
                <div class="flex bg-gray-100 dark:bg-gray-700 rounded-t-lg p-1">
                    <button
                        @click="selectMode('rent')"
                        :class="[
                            'px-2 md:px-6 py-2 text-sm font-medium transition-all duration-200',
                            form.mode === 'rent' 
                                ? 'bg-white dark:bg-gray-600 text-blue-600 dark:text-blue-400 shadow-sm rounded-t-md' 
                                : 'text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-gray-100 rounded-t-md'
                        ]"
                    >
                        Rent
                    </button>
                    <button
                        @click="selectMode('buy')"
                        :class="[
                            'px-2 md:px-6 py-2 text-sm font-medium transition-all duration-200',
                            form.mode === 'buy' 
                                ? 'bg-white dark:bg-gray-600 text-blue-600 dark:text-blue-400 shadow-sm rounded-t-md' 
                                : 'text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-gray-100 rounded-t-md'
                        ]"
                    >
                        Buy
                    </button>
                </div>
            </div>

            <!-- Search bar -->
            <div class="bg-white flex flex-col md:flex-row md:items-center md:justify-between w-2/3 px-4 py-2 rounded-md shadow-md space-y-2 md:space-y-0 md:space-x-2">
                <div class="flex items-center w-full space-x-1">
                    <SearchIcon />
                    <Input
                        v-model="form.search"
                        type="text"
                        placeholder="Search..."
                        class="rounded-md py-1"
                    />
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
        </div>
        <Modal v-model:open="modalOpen" title="Filters" closable @close="modalOpen = false" size="lg">
             <!-- Modal content -->
            <div class="space-y-4">                
                <!-- Price Filter -->
                <div>
                    <label class="block text-sm font-medium mb-2">Property Type</label>
                    <div class="flex flex-col space-y-1">
                        <label class="inline-flex items-center">
                            <input type="checkbox" value="villa" class="form-checkbox text-blue-600" />
                            <span class="ml-2">Villa</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" value="apartment" class="form-checkbox text-blue-600" />
                            <span class="ml-2">Apartment</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" value="commercial" class="form-checkbox text-blue-600" />
                            <span class="ml-2">Commercial</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" value="guest_house" class="form-checkbox text-blue-600" />
                            <span class="ml-2">Guest House</span>
                        </label>
                    </div>
                </div>
                <hr class="border-t border-gray-200 dark:border-gray-600 my-4" />
                <PriceFilter v-model="form.priceFilter" />
                <hr class="border-t border-gray-200 dark:border-gray-600 my-4" />
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
    </header>
</template>