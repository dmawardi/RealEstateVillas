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
        <title>{{ seoData?.title || 'Premium Real Estate Properties in Bali | Your Company' }}</title>
        <meta name="description" :content="seoData?.description || 'Discover luxury villas, apartments and investment properties in Bali. Expert real estate services with comprehensive property management.'" />
        <meta name="keywords" :content="seoData?.keywords || 'bali real estate, property investment, villa rental, apartment bali, property management'" />
        
        <!-- Open Graph -->
        <meta property="og:title" :content="seoData?.title || 'Premium Real Estate Properties in Bali'" />
        <meta property="og:description" :content="seoData?.description || 'Discover luxury properties in Bali'" />
        <meta property="og:type" content="website" />
        <meta property="og:url" :content="seoData?.canonicalUrl || 'https://balivillaspot.com'" />
        <meta property="og:image" content="https://balivillaspot.com/images/og-homepage.jpg" />
        <meta property="og:site_name" content="Your Real Estate Company" />
        
        <!-- Twitter Cards -->
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" :content="seoData?.title" />
        <meta name="twitter:description" :content="seoData?.description" />
        <meta name="twitter:image" content="https://balivillaspot.com/images/twitter-homepage.jpg" />
        
        <!-- Technical SEO -->
        <link rel="canonical" :href="seoData?.canonicalUrl || 'https://balivillaspot.com'" />
        <meta name="robots" content="index, follow, max-image-preview:large" />
        </Head>
    <BaseLayout>
            <SearchHeader />
            <PropertyShowcase :featured="featured" :premium="premium" />
            <ProcessGuide />
            <Footer :businessPhone="businessPhone" :businessEmail="businessEmail" />
    </BaseLayout>
</template>
