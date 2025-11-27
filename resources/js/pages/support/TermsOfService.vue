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
        heroTitle="Terms of Service"
        heroDescription="Understand the terms and conditions for using our services."
        contactTitle="Questions About Terms?"
        contactDescription="Contact us for clarification on our terms of service."
    >
        
    </SupportPageLayout>
</template>