<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import CompactSearchHeader from '@/components/ui/navigation/CompactSearchHeader.vue';
import { PropertyFilters, PaginatedProperties } from '@/types';
import { formatPrice } from '@/utils';
import { onMounted } from 'vue';

interface Props {
    properties: PaginatedProperties;
    filters?: PropertyFilters;
}

const { properties, filters } = defineProps<Props>();

const address = (
    number: string = '',
    street: string = '',
    village: string = '',
    district: string = '',
    regency: string = '',
    state: string = '',
    zip: string = ''
) => {
    return `Jl. ${street} ${number}, ${regency}, ${district}, ${village}, ${state} ${zip}`;
};

function formatPaginationLabel(label: string): string {
    return label.replace('&laquo;', '«').replace('&raquo;', '»');
}
</script>

<template>
    <Head title="Properties Management" />

    <AppLayout>
        <header class="my-4 mx-4">
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Properties Management
                </h2>
                <Link 
                    :href="route('admin.properties.create')"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Property
                </Link>
            </div>
        </header>

        <!-- Search -->
        <CompactSearchHeader :initialFilters="filters" routeURL="/admin/properties" />

        <!-- Properties Table -->
        <div class="mx-4 bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-md">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                ID
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Actions
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Title
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Type
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Address
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Price
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Bedrooms
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Bathrooms
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Area (sqft)
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Listed At
                            </th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="property in properties.data" :key="property.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ property.id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <Link :href="route('admin.properties.edit', property.id)" class="text-blue-600 hover:text-blue-500">
                                    Edit
                                </Link>
                                <!-- <button @click="confirmDelete(property.id)" class="text-red-600 hover:text-red-500">
                                    Delete
                                </button> -->
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ property.title }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ property.property_type }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ property.status }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ address(property.street_number, property.street_name, property.village, property.district, property.regency, property.state, property.postcode) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ formatPrice(property.price || 0) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ property.bedrooms }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ property.bathrooms }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ property.floor_area }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ property.listed_at }}
                            </td>
                            
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div v-if="properties.data.length === 0" class="text-center py-12">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                    No properties found
                </h3>
                <p class="text-gray-600 dark:text-gray-400 mb-4">
                    Get started by adding your first property.
                </p>
                <Link 
                    :href="route('admin.properties.create')"
                    class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Your First Property
                </Link>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="properties.data.length > 0 && properties.last_page > 1" class="mx-4 mt-6">
            <nav class="flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                    <Link
                        v-if="properties.current_page > 1"
                        :href="properties.links[0].url || ''"
                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                    >
                        Previous
                    </Link>
                    <Link
                        v-if="properties.current_page < properties.last_page"
                        :href="properties.links[properties.links.length - 1].url || ''"
                        class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                    >
                        Next
                    </Link>
                </div>
                
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            Showing
                            <span class="font-medium">{{ properties.from }}</span>
                            to
                            <span class="font-medium">{{ properties.to }}</span>
                            of
                            <span class="font-medium">{{ properties.total }}</span>
                            results
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                            <Link
                                v-for="link in properties.links"
                                :key="link.label"
                                :href="link.url || ''"
                                :class="[
                                    link.active
                                        ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                                ]"
                            >
                                {{ formatPaginationLabel(link.label) }}
                            </Link>
                        </nav>
                    </div>
                </div>
            </nav>
        </div>
    </AppLayout>
</template>