<script setup lang="ts">
import BaseLayout from '@/layouts/BaseLayout.vue';
import PropertyShowcase from '@/components/properties/PropertyShowcase.vue';
import Footer from '@/components/Footer.vue';
import type { Property, SEO } from '@/types';
import ProcessGuide from '@/components/properties/ProcessGuide.vue';
import SearchHeader from '@/components/ui/navigation/SearchHeader.vue';
import { Head } from '@inertiajs/vue3';


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
    <Head>
        <title>{{ seoData?.title || 'Bali Villa Rentals & Land Sales | BaliVillaSpot' }}</title>
        <meta name="description" :content="seoData?.description || 'Discover luxury villas and premium land for sale in Bali. Commission-based rentals from trusted partners with expert local support.'" />
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="author" content="BaliVillaSpot" />
        <meta name="generator" content="Laravel + Vue.js" />
        
        <!-- Geo-targeting for Bali -->
        <meta name="geo.region" content="ID-BA" />
        <meta name="geo.placename" content="Bali, Indonesia" />
        <meta name="geo.position" content="-8.409518;115.188919" />
        <meta name="ICBM" content="-8.409518, 115.188919" />
        
        <!-- Language and locale -->
        <meta property="og:locale" content="en_US" />
        <meta property="og:locale:alternate" content="id_ID" />
        
        <!-- Enhanced Open Graph -->
        <meta property="og:title" :content="seoData?.title || 'Bali Villa Rentals & Land Sales'" />
        <meta property="og:description" :content="seoData?.description || 'Discover luxury villas and premium land'" />
        <meta property="og:type" content="website" />
        <meta property="og:url" :content="seoData?.canonicalUrl || 'https://balivillaspot.com'" />
        <meta property="og:image" :content="seoData?.ogImage || 'https://balivillaspot.com/images/logo/og-homepage.jpg'" />
        <meta property="og:image:width" content="1200" />
        <meta property="og:image:height" content="630" />
        <meta property="og:image:type" content="image/jpeg" />
        <meta property="og:site_name" content="BaliVillaSpot" />
        
        <!-- Business/Organization markup -->
        <meta property="business:contact_data:street_address" content="Bali, Indonesia" />
        <meta property="business:contact_data:locality" content="Bali" />
        <meta property="business:contact_data:region" content="Bali" />
        <meta property="business:contact_data:postal_code" content="" />
        <meta property="business:contact_data:country_name" content="Indonesia" />
        
        <!-- Technical SEO -->
        <link rel="canonical" :href="seoData?.canonicalUrl || 'https://balivillaspot.com'" />
        <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1" />
    </Head>
    <BaseLayout>
            <SearchHeader />
            <PropertyShowcase :featured="featured" :premium="premium" />
            <ProcessGuide />
            <Footer :businessPhone="businessPhone" :businessEmail="businessEmail" />
    </BaseLayout>
</template>
