<script setup lang="ts">
import SupportPageLayout from '@/components/support/SupportPageLayout.vue';
import type { SEO } from '@/types';

interface Props {
    businessEmail?: string;
    businessPhone?: string;
    seoData?: SEO;
}
const { businessEmail, businessPhone, seoData } = defineProps<Props>();
</script>

<template>
    <SupportPageLayout
        :businessPhone="businessPhone"
        :businessEmail="businessEmail" 
        :seoData="seoData"
        heroTitle="Cookie Policy"
        heroDescription="Learn how we use cookies to enhance your experience..."
        contactTitle="Questions About Cookies?"
        contactDescription="Contact us for clarification on our cookie practices."
    >
        
    </SupportPageLayout>
</template>