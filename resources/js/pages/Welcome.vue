<script setup lang="ts">
import BaseLayout from '@/layouts/BaseLayout.vue';
import SearchHeader from '@/components/ui/navigation/SearchHeader.vue';
import PropertyShowcase from '@/components/properties/PropertyShowcase.vue';
import type { Property } from '@/types';

interface Props {
    featured?: Property[];
    premium?: Property[];
}

const {featured, premium} = defineProps<Props>();
</script>

<template>
    <BaseLayout>
            <SearchHeader />
            <PropertyShowcase :featured="featured" :premium="premium" />
    </BaseLayout>
</template>
