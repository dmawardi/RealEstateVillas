<script setup lang="ts">
import { Property } from '@/types';
import { defineProps } from 'vue';

interface Props {
    property: Property
}

const { property } = defineProps<Props>();
</script>

<template>
    <!-- Agent Contact -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">
            Contact Agent
        </h3>
        <div class="space-y-3">
            <div>
                <div class="font-medium text-gray-900 dark:text-gray-100">{{ property.agent_name }}</div>
                <div v-if="property.agency_name" class="text-sm text-gray-600 dark:text-gray-400">
                    {{ property.agency_name }}
                </div>
            </div>
            <div v-if="property.agent_phone">
                <a :href="`tel:${property.agent_phone}`" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                    {{ property.agent_phone }}
                </a>
            </div>
            <div v-if="property.agent_email">
                <a :href="`mailto:${property.agent_email}`" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                    {{ property.agent_email }}
                </a>
            </div>
            <button class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                Send Enquiry
            </button>
        </div>
    </div>
</template>