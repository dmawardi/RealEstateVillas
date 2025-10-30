<script setup lang="ts">
import BaseLayout from '@/layouts/BaseLayout.vue';
import SearchHeader from '@/components/ui/navigation/SearchHeader.vue';
import PropertyShowcase from '@/components/properties/PropertyShowcase.vue';
import Footer from '@/components/Footer.vue';
import type { Property } from '@/types';
import ProcessGuide from '@/components/properties/ProcessGuide.vue';
import UpperNavigation from '@/components/ui/navigation/UpperNavigation.vue';


interface Props {
    featured?: Property[];
    premium?: Property[];
    businessEmail?: string;
    businessPhone?: string;
}

const {featured, premium, businessEmail, businessPhone} = defineProps<Props>();
</script>

<template>
    <BaseLayout>
        <header class="bg-white dark:bg-gray-800 shadow p-4 w-10/12 mx-auto">
            <UpperNavigation />
            <SearchHeader />
        </header>
            <PropertyShowcase :featured="featured" :premium="premium" />
            <ProcessGuide />
            <Footer :businessPhone="businessPhone" :businessEmail="businessEmail" />
    </BaseLayout>
</template>
