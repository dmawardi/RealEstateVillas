<script setup lang="ts">
import { ref } from 'vue';
import UpperNavigation from './UpperNavigation.vue';
import Input from '@/components/ui/form/Input.vue';
import SearchIcon from '../form/SearchIcon.vue';

const modalOpen = ref(false);
const form = ref({
    search: '',
    mode: 'rent' as 'rent' | 'buy',
});

function handleSearch() {
    // Implement search logic here
    console.log('Searching for:', form.value.search, 'Mode:', form.value.mode);
    console.log('Modal Open:', modalOpen.value);
}

function selectMode(mode: 'rent' | 'buy') {
    form.value.mode = mode;
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
    </header>
</template>