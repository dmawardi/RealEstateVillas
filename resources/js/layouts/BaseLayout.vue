<script setup lang="ts">
// Props breadcrumbs
import FlashMessages from '@/components/FlashMessages.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}
const { breadcrumbs } =withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <!-- Breadcrumbs bar -->
    <nav class="bg-white dark:bg-gray-800 shadow-sm">
        <FlashMessages />
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <ol class="list-reset flex text-gray-700 dark:text-gray-300">
                <li>
                    <a href="/" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">Home</a>
                </li>
                <li class="mx-2">/</li>
                <li v-for="(breadcrumb, index) in breadcrumbs" :key="index">
                    <a :href="breadcrumb.href" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                        {{ breadcrumb.title }}
                    </a>
                    <span v-if="index < breadcrumbs.length - 1" class="mx-2">/</span>
                </li>
            </ol>
        </div>
    </nav>
    <slot />
</template>
