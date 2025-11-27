<script setup lang="ts">
import { computed, ref } from 'vue';
import type { SEO } from '@/types';
import SupportPageLayout from '@/components/support/SupportPageLayout.vue';

interface Props {
    businessEmail?: string;
    businessPhone?: string;
    seoData?: SEO;
}

const { businessEmail, businessPhone, seoData } = defineProps<Props>();

interface FAQItem {
    id: number;
    question: string;
    answer: string;
    category: 'general' | 'rentals' | 'sales' | 'payment' | 'legal';
}

const activeCategory = ref<'all' | 'general' | 'rentals' | 'sales' | 'payment' | 'legal'>('all');
const openItems = ref<number[]>([]);

const faqItems: FAQItem[] = [
    // General Questions
    {
        id: 1,
        category: 'general',
        question: 'What areas of Bali do you cover?',
        answer: 'We specialize in premium properties across Bali\'s most sought-after locations including Canggu, Seminyak, Pererenan, Uluwatu, Sanur, and Ubud. Our portfolio focuses on areas with strong rental yields and investment potential.'
    },
    {
        id: 2,
        category: 'general',
        question: 'Do you provide property management services?',
        answer: 'Yes, we offer comprehensive property management services at request including guest check-ins, cleaning, maintenance, and 24/7 support. Our local team ensures your property is well-maintained and profitable.'
    },
    {
        id: 3,
        category: 'general',
        question: 'Can foreigners buy property in Bali?',
        answer: 'Foreigners can own property in Bali through various legal structures including leasehold arrangements, Indonesian company ownership (PT PMA), or through Indonesian nominees. We work with experienced legal professionals to guide you through the process.'
    },

    // Rental Questions
    {
        id: 4,
        category: 'rentals',
        question: 'What is included in villa rentals?',
        answer: 'Inclusions vary by property and may include amenities such as housekeeping, pool maintenance, WiFi, air conditioning, and more. Please check the specific property page for detailed information. If you have questions about what\'s included, feel free to reach out to our team.'
    },
    {
        id: 5,
        category: 'rentals',
        question: 'How far in advance should I book?',
        answer: 'We recommend booking 2-3 months in advance for peak seasons (July-August, December-January). For off-peak periods, 4-6 weeks is usually sufficient. Last-minute bookings are possible but may have limited availability.'
    },
    {
        id: 6,
        category: 'rentals',
        question: 'What is your cancellation policy?',
        answer: 'Cancellation policies vary by property and season. Generally, bookings can be cancelled up to 30 days before arrival for a full refund. Cancellations within 14-30 days may incur a 50% penalty, and less than 14 days typically result in no refund.'
    },

    // Sales Questions
    {
        id: 7,
        category: 'sales',
        question: 'What is the property buying process in Bali?',
        answer: 'The process involves property selection, legal due diligence, negotiation, contract signing, and transfer of ownership. Our team guides you through each step, from initial viewing to final handover, ensuring all legal requirements are met.'
    },
    {
        id: 8,
        category: 'sales',
        question: 'What are the additional costs when buying property?',
        answer: 'Additional costs include legal fees (1-2%), notary fees, transfer taxes (5% for land), and potential renovation or furnishing costs. We provide detailed cost breakdowns during the purchase process.'
    },
    {
        id: 9,
        category: 'sales',
        question: 'Can I get financing for property purchases?',
        answer: 'Local Indonesian banks offer financing to foreign buyers under certain conditions. We can connect you with mortgage brokers who specialize in foreign property purchases and understand the local banking requirements.'
    },

    // Payment Questions
    {
        id: 10,
        category: 'payment',
        question: 'What payment methods do you accept?',
        answer: 'We accept bank transfers. For property purchases, we typically require wire transfers for security and legal compliance reasons.'
    },
    {
        id: 11,
        category: 'payment',
        question: 'When is payment required for rentals?',
        answer: 'A 30% deposit is required to confirm your booking, with the remaining balance due 30 days before arrival. For bookings made within 30 days of arrival, full payment is required immediately.'
    },

    // Legal Questions
    {
        id: 12,
        category: 'legal',
        question: 'What legal documents do I need?',
        answer: 'For rentals, you\'ll need a valid passport. For purchases, required documents include passport, proof of funds, tax identification, and various Indonesian legal documents which our partner legal team will prepare.'
    },
    {
        id: 13,
        category: 'legal',
        question: 'Do you provide legal assistance?',
        answer: 'Yes, we work with experienced Indonesian partners specializing in property transactions. Our partner legal team handles due diligence, contract preparation, and ensures all transactions comply with local regulations.'
    }
];

const categories = [
    { id: 'all', name: 'All Questions', count: faqItems.length },
    { id: 'general', name: 'General', count: faqItems.filter(item => item.category === 'general').length },
    { id: 'rentals', name: 'Rentals', count: faqItems.filter(item => item.category === 'rentals').length },
    { id: 'sales', name: 'Sales', count: faqItems.filter(item => item.category === 'sales').length },
    { id: 'payment', name: 'Payment', count: faqItems.filter(item => item.category === 'payment').length },
    { id: 'legal', name: 'Legal', count: faqItems.filter(item => item.category === 'legal').length }
];

const filteredFAQs = computed(() => {
    if (activeCategory.value === 'all') {
        return faqItems;
    }
    return faqItems.filter(item => item.category === activeCategory.value);
});

const toggleItem = (id: number) => {
    const index = openItems.value.indexOf(id);
    if (index > -1) {
        openItems.value.splice(index, 1);
    } else {
        openItems.value.push(id);
    }
};

const isOpen = (id: number) => {
    return openItems.value.includes(id);
};

const switchCategory = (category: 'all' | 'general' | 'rentals' | 'sales' | 'payment' | 'legal') => {
    activeCategory.value = category;
    // Close all open items when switching categories
    openItems.value = [];
};
</script>

<template>
    <SupportPageLayout
        :businessPhone="businessPhone"
        :businessEmail="businessEmail" 
        :seoData="seoData"
        heroTitle="Frequently Asked Questions"
        heroDescription="Everything you need to know about villa rentals, property sales, and our services in Bali."
        contactTitle="Still Have Questions?"
        contactDescription="Our experienced team is ready to help you with personalized advice about villa rentals, property investments, and everything Bali real estate."
    >
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Category Filter -->
        <div class="mb-12">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-1.5 shadow-lg border border-secondary/30 dark:border-gray-700 inline-flex flex-wrap gap-1">
                <button
                    v-for="category in categories"
                    :key="category.id"
                    @click="switchCategory(category.id as any)"
                    :class="[
                        'px-4 py-2.5 rounded-lg font-display font-medium transition-all duration-300 text-sm',
                        activeCategory === category.id
                            ? 'bg-accent text-white shadow-md transform scale-105'
                            : 'text-primary/70 dark:text-gray-300 hover:text-primary hover:bg-secondary/10 dark:hover:text-gray-100'
                    ]"
                >
                    {{ category.name }}
                    <span class="ml-1 text-xs opacity-75">({{ category.count }})</span>
                </button>
            </div>
        </div>
    
        <!-- FAQ Items -->
        <div class="space-y-4">
            <div 
                v-for="item in filteredFAQs" 
                :key="item.id"
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-secondary/20 dark:border-gray-700 hover:shadow-lg transition-all duration-300 overflow-hidden"
            >
                <button
                    @click="toggleItem(item.id)"
                    class="w-full px-6 py-5 text-left hover:bg-secondary/5 dark:hover:bg-gray-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-accent/50"
                >
                    <div class="flex items-center justify-between">
                        <h3 class="font-display text-lg font-semibold text-primary dark:text-gray-100 pr-4">
                            {{ item.question }}
                        </h3>
                        <div :class="[
                            'flex-shrink-0 w-8 h-8 bg-secondary/20 rounded-lg flex items-center justify-center transition-all duration-300',
                            isOpen(item.id) ? 'bg-accent text-white rotate-45' : 'text-secondary'
                        ]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </div>
                    </div>
                </button>
                
                <div 
                    v-show="isOpen(item.id)"
                    class="px-6 pb-5 border-t border-secondary/10 dark:border-gray-700"
                >
                    <div class="pt-4">
                        <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed">
                            {{ item.answer }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    
        <!-- No Results -->
        <div v-if="filteredFAQs.length === 0" class="text-center py-16">
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-12 border border-secondary/20 dark:border-gray-700 shadow-sm max-w-md mx-auto">
                <svg class="mx-auto h-16 w-16 text-secondary mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="font-display text-xl font-semibold text-primary dark:text-gray-100 mb-3">
                    No questions found
                </h3>
                <p class="font-body text-primary/60 dark:text-gray-400">
                    Try selecting a different category or contact us directly for assistance.
                </p>
            </div>
        </div>
    </div>
    </SupportPageLayout>
</template>