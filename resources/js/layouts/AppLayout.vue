<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import BaseLayout from './BaseLayout.vue';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <BaseLayout>
        <AppLayout :breadcrumbs="breadcrumbs">
            <slot />
        </AppLayout>
    </BaseLayout>
</template>
