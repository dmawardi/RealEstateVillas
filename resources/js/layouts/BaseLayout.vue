<script setup lang="ts">
// Props breadcrumbs
import FlashMessages from '@/components/FlashMessages.vue';
import type { BreadcrumbItemType } from '@/types';
import Breadcrumbs from '@/components/Breadcrumbs.vue';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}
const { breadcrumbs } =withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <FlashMessages />
    <main>
        <!-- Breadcrumbs bar -->
        <div v-if="breadcrumbs.length > 0" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <Breadcrumbs :breadcrumbs="breadcrumbs" />
        </div>
        <slot />
    </main>
</template>
