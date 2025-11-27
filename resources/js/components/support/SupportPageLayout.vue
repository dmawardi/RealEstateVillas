// resources/js/components/support/SupportPageLayout.vue
<script setup lang="ts">
import BaseLayout from '@/layouts/BaseLayout.vue';
import Footer from '@/components/Footer.vue';
import SEOHead from '@/components/SEOHead.vue';
import SupportPageHero from '@/components/support/HeroHeader.vue';
import SupportContactCTA from '@/components/support/SupportContactCTA.vue';
import type { SEO } from '@/types';

interface Props {
    businessEmail?: string;
    businessPhone?: string;
    seoData?: SEO;
    heroTitle: string;
    heroDescription: string;
    showContactCTA?: boolean;
    contactTitle?: string;
    contactDescription?: string;
}

const { 
    businessEmail, 
    businessPhone, 
    seoData, 
    heroTitle, 
    heroDescription, 
    showContactCTA = true,
    contactTitle = "Need Help?",
    contactDescription = "Our team is here to assist you with any questions or concerns."
} = defineProps<Props>();
</script>

<template>
    <SEOHead :seoData="seoData" pageType="article" />
    
    <BaseLayout>
        <div class="min-h-screen bg-base dark:bg-gray-900">
            <SupportPageHero 
                :title="heroTitle" 
                :description="heroDescription" 
            />
            
            <section class="py-16">
                <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                    <slot />
                </div>
            </section>
            
            <SupportContactCTA 
                v-if="showContactCTA"
                :businessPhone="businessPhone"
                :title="contactTitle"
                :description="contactDescription"
            />
        </div>

        <Footer :businessPhone="businessPhone" :businessEmail="businessEmail" />
    </BaseLayout>
</template>