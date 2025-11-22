<script setup lang="ts">
import { Property } from '@/types';
import { formatDate } from '@/utils';
import { Link } from '@inertiajs/vue3';

interface Props {
    property: Property;
    cardClass: string;
    textClass: string;
}

const { property, cardClass, textClass } = defineProps<Props>();
</script>

<template>
    <div 
        :class="['border rounded-lg p-3', cardClass]">
        <div class="flex items-start justify-between">
            <div class="min-w-0 flex-1">
                <Link 
                    :href="route('admin.properties.show', property.slug)"
                    class="text-sm font-medium text-gray-900 dark:text-gray-100 hover:text-blue-600 dark:hover:text-blue-400 truncate block"
                >
                    {{ property.title }}
                </Link>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    ID: {{ property.property_id }}
                </p>
                <div v-if="property.pricing && property.pricing.length > 0" class="mt-1">
                    <p :class="['text-xs', textClass]">
                        Ends: {{ formatDate(new Date(property.pricing[0].end_date ?? "")) }}
                    </p>
                </div>
            </div>
            <Link 
                :href="route('admin.properties.edit', property.slug)"
                class="ml-2 text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 flex-shrink-0"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </Link>
        </div>
    </div>
</template>