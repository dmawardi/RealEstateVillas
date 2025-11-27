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
        heroTitle="Privacy Policy"
        heroDescription="Learn how we protect your personal information..."
        contactTitle="Questions About Privacy?"
        contactDescription="Contact us for clarification on our privacy practices."
    >
        
    </SupportPageLayout>
</template>