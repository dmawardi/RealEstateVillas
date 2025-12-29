<script setup lang="ts">
// filepath: /Users/d/Web Development/projects/RealEstate/resources/js/components/SEOHead.vue
import { Head } from '@inertiajs/vue3';
import type { SEO } from '@/types';

interface Props {
    seoData?: SEO;
    pageType?: 'website' | 'article' | 'product' | 'profile';
}

const { seoData, pageType = 'website' } = defineProps<Props>();

// Default SEO fallbacks
const defaults = {
    title: 'Bali Villa Rentals & Land Sales | BaliVillaSpot',
    description: 'Discover luxury villas and premium land for sale in Bali. Commission-based rentals from trusted partners with expert local support.',
    canonicalUrl: 'https://balivillaspot.com',
    ogImage: 'https://balivillaspot.com/images/logo/Logo_w_text.png',
    siteName: 'BaliVillaSpot',
    author: 'BaliVillaSpot',
    locale: 'en_US'
};

// Computed properties for clean fallbacks
const title = seoData?.title || defaults.title;
const description = seoData?.description || defaults.description;
const canonicalUrl = seoData?.canonicalUrl || defaults.canonicalUrl;
const ogImage = seoData?.ogImage || defaults.ogImage;
const keywords = seoData?.keywords;
</script>

<template>
    <Head>
        <!-- Core SEO -->
        <title>{{ title }}</title>
        <meta name="description" :content="description" />
        <meta name="keywords" v-if="keywords" :content="keywords" />
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="author" :content="defaults.author" />
        <meta name="generator" content="Laravel + Vue.js" />
        
        <!-- Geo-targeting for Bali -->
        <meta name="geo.region" content="ID-BA" />
        <meta name="geo.placename" content="Bali, Indonesia" />
        <meta name="geo.position" content="-8.409518;115.188919" />
        <meta name="ICBM" content="-8.409518, 115.188919" />
        
        <!-- Language and locale -->
        <meta property="og:locale" :content="defaults.locale" />
        <meta property="og:locale:alternate" content="id_ID" />
        
        <!-- Open Graph -->
        <meta property="og:title" :content="title" />
        <meta property="og:description" :content="description" />
        <meta property="og:type" :content="pageType" />
        <meta property="og:url" :content="canonicalUrl" />
        <meta property="og:image" :content="ogImage" />
        <meta property="og:image:width" content="1200" />
        <meta property="og:image:height" content="630" />
        <meta property="og:image:type" content="image/jpeg" />
        <meta property="og:site_name" :content="defaults.siteName" />
        
        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" :content="title" />
        <meta name="twitter:description" :content="description" />
        <meta name="twitter:image" :content="ogImage" />
        <meta name="twitter:site" content="@BaliVillaSpot" />
        
        <!-- Business/Organization markup -->
        <meta property="business:contact_data:locality" content="Bali" />
        <meta property="business:contact_data:region" content="Bali" />
        <meta property="business:contact_data:country_name" content="Indonesia" />
        
        <!-- Technical SEO -->
        <link rel="canonical" :href="canonicalUrl" />
        <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1" />
    </Head>
</template>