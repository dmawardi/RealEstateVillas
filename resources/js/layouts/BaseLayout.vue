<script setup lang="ts">
// Props breadcrumbs
import FlashMessages from '@/components/FlashMessages.vue';
import type { BreadcrumbItemType } from '@/types';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import UpperNavigation from '@/components/ui/navigation/UpperNavigation.vue';
import { usePage } from '@inertiajs/vue3';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

usePage();

const { breadcrumbs } = defineProps<Props>();
</script>

<template>
    <FlashMessages />
    <UpperNavigation />
    <!-- Breadcrumbs bar -->
    <div v-if="(breadcrumbs?.length ?? 0) > 0" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
        <Breadcrumbs :breadcrumbs="breadcrumbs ?? []" />
    </div>
    <main>
        <slot />
    </main>
</template>
