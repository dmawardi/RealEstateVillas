<script setup lang="ts">
import BaseLayout from '@/layouts/BaseLayout.vue';
import PropertyShowcase from '@/components/properties/PropertyShowcase.vue';
import Footer from '@/components/Footer.vue';
import type { Property, SEO } from '@/types';
import ProcessGuide from '@/components/properties/ProcessGuide.vue';
import SearchHeader from '@/components/ui/navigation/SearchHeader.vue';
import SEOHead from '@/components/SEOHead.vue';


interface Props {
    featured?: Property[];
    premium?: Property[];
    businessEmail?: string;
    businessPhone?: string;
    seoData?: SEO;
}

const { featured, premium, businessEmail, businessPhone, seoData } = defineProps<Props>();
</script>

<template>
    <SEOHead 
        :seoData="seoData" 
        pageType="website"
    />
    <BaseLayout>
            <SearchHeader />
            <PropertyShowcase :featured="featured" :premium="premium" />
            <ProcessGuide :businessPhone="businessPhone?? ''" />
            <Footer :businessPhone="businessPhone" :businessEmail="businessEmail" />
    </BaseLayout>
</template>
