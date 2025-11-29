<script setup lang="ts">
import SupportContentCard from '@/components/support/SupportContentCard.vue';
import SupportPageLayout from '@/components/support/SupportPageLayout.vue';
import type { SEO } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Props {
    businessEmail?: string;
    businessPhone?: string;
    seoData?: SEO;
}
const { businessEmail, businessPhone, seoData } = defineProps<Props>();

const contactEmail = businessEmail || 'contact@balivillaspot.com';
// Contact form
// Required fields - always present
const form = useForm({
    name: '',
    email: '',
    phone: '',
    subject: '',
    inquiry_type: 'general',
    message: '',
    
    // Optional fields - based on inquiry type
    property_interest: '',
    budget: '',
    travel_dates: '',
    guests: '',
});

const isSubmitting = ref(false);
const submitMessage = ref('');

const inquiryTypes = [
    { value: 'general', label: 'General Inquiry' },
    { value: 'villa_rental', label: 'Villa Rental' },
    { value: 'property_sale', label: 'Property Purchase' },
    { value: 'property_listing', label: 'List My Property' },
    { value: 'investment', label: 'Investment Consultation' },
    { value: 'management', label: 'Property Management' },
];

const budgetRanges = [
    { value: '', label: 'Select Budget Range' },
    { value: 'under_100', label: 'Under $100/night' },
    { value: '100_300', label: '$100 - $300/night' },
    { value: '300_500', label: '$300 - $500/night' },
    { value: '500_1000', label: '$500 - $1,000/night' },
    { value: 'over_1000', label: 'Over $1,000/night' },
    { value: 'purchase_under_100k', label: 'Under $100k (Purchase)' },
    { value: 'purchase_100k_500k', label: '$100k - $500k (Purchase)' },
    { value: 'purchase_over_500k', label: 'Over $500k (Purchase)' },
];

const submitForm = async () => {
    isSubmitting.value = true;
    submitMessage.value = '';
    
    try {
        await form.post(route('contact.submit'), {
            onSuccess: () => {
                submitMessage.value = 'Thank you for your message! We\'ll get back to you within 24 hours.';
                form.reset();
            },
            onError: (errors) => {
                submitMessage.value = 'There was an error sending your message. Please try again.';
                console.error('Form errors:', errors);
            },
            onFinish: () => {
                isSubmitting.value = false;
            }
        });
    } catch (error) {
        submitMessage.value = 'There was an error sending your message. Please try again.';
        isSubmitting.value = false;
        console.error('Submit error:', error);
    }
};

const contactMethods = [
    {
        icon: 'phone',
        title: 'Call Us',
        description: 'Speak with our team directly',
        contact: businessPhone || '+62 123 456 7890',
        href: `tel:${businessPhone || '+62123456789'}`,
        hours: 'Mon-Sun: 8:00 AM - 10:00 PM (WITA)'
    },
    {
        icon: 'email',
        title: 'Email Us',
        description: 'Send us a detailed inquiry',
        contact: contactEmail,
        href: `mailto:${contactEmail}`,
        hours: 'Response within 24 hours'
    },
    {
        icon: 'whatsapp',
        title: 'WhatsApp',
        description: 'Quick messages and support',
        contact: businessPhone || '+62 123 456 7890',
        href: `https://wa.me/${businessPhone?.replace(/\D/g, '') || '62123456789'}`,
        hours: 'Available 24/7'
    }
];
</script>

<template>
    <SupportPageLayout
        :businessPhone="businessPhone"
        :businessEmail="businessEmail" 
        :seoData="seoData"
        heroTitle="Contact Us"
        heroDescription="We're here to help! Reach out to us with any questions or concerns."
        contactTitle="Get in Touch"
        contactDescription="Feel free to contact us for any inquiries or support."
    >
        <div class="space-y-8">
            <!-- Contact Methods -->
            <SupportContentCard title="Get In Touch">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div 
                        v-for="method in contactMethods"
                        :key="method.title"
                        class="group text-center p-6 bg-secondary/5 rounded-xl border border-secondary/20 hover:bg-secondary/10 hover:border-accent/30 transition-all duration-300 transform hover:-translate-y-1"
                    >
                        <div class="w-16 h-16 bg-accent/10 rounded-xl flex items-center justify-center mx-auto mb-4 group-hover:bg-accent group-hover:text-white transition-all duration-300">
                            <!-- Phone Icon -->
                            <svg v-if="method.icon === 'phone'" class="w-8 h-8 text-accent group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.037 11.037 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            
                            <!-- Email Icon -->
                            <svg v-else-if="method.icon === 'email'" class="w-8 h-8 text-accent group-hover:text-white transition-colors duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M4 7.00005L10.2 11.65C11.2667 12.45 12.7333 12.45 13.8 11.65L20 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            
                            <!-- WhatsApp Icon -->
                            <svg v-else-if="method.icon === 'whatsapp'" class="w-8 h-8 text-accent group-hover:text-white transition-colors duration-300" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893A11.821 11.821 0 0020.473 3.488"/>
                            </svg>
                            
                        </div>
                        <h3 class="font-display text-xl font-semibold text-primary dark:text-gray-100 mb-2">
                            {{ method.title }}
                        </h3>
                        <p class="font-body text-primary/70 dark:text-gray-400 mb-3">
                            {{ method.description }}
                        </p>
                        <a 
                            :href="method.href"
                            class="font-body text-accent hover:text-accent-dark font-medium transition-colors duration-200 block mb-2"
                        >
                            {{ method.contact }}
                        </a>
                        <p class="font-body text-sm text-primary/60 dark:text-gray-500">
                            {{ method.hours }}
                        </p>
                    </div>
                </div>
            </SupportContentCard>

            <!-- Contact Form -->
            <SupportContentCard title="Send Us a Message">
                <form @submit.prevent="submitForm" class="space-y-6">
                    <!-- Basic Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block font-display font-medium text-primary dark:text-gray-100 mb-2">
                                Full Name *
                            </label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-secondary/30 dark:border-gray-600 rounded-lg font-body text-primary dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-accent focus:border-accent transition-all duration-200"
                                placeholder="Enter your full name"
                            />
                            <div v-if="form.errors.name" class="mt-1 text-red-500 text-sm font-body">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label for="email" class="block font-display font-medium text-primary dark:text-gray-100 mb-2">
                                Email Address *
                            </label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-secondary/30 dark:border-gray-600 rounded-lg font-body text-primary dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-accent focus:border-accent transition-all duration-200"
                                placeholder="Enter your email address"
                            />
                            <div v-if="form.errors.email" class="mt-1 text-red-500 text-sm font-body">{{ form.errors.email }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="phone" class="block font-display font-medium text-primary dark:text-gray-100 mb-2">
                                Phone Number
                            </label>
                            <input
                                id="phone"
                                v-model="form.phone"
                                type="tel"
                                class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-secondary/30 dark:border-gray-600 rounded-lg font-body text-primary dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-accent focus:border-accent transition-all duration-200"
                                placeholder="Enter your phone number"
                            />
                            <div v-if="form.errors.phone" class="mt-1 text-red-500 text-sm font-body">{{ form.errors.phone }}</div>
                        </div>

                        <div>
                            <label for="inquiry_type" class="block font-display font-medium text-primary dark:text-gray-100 mb-2">
                                Inquiry Type *
                            </label>
                            <select
                                id="inquiry_type"
                                v-model="form.inquiry_type"
                                required
                                class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-secondary/30 dark:border-gray-600 rounded-lg font-body text-primary dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-accent focus:border-accent transition-all duration-200"
                            >
                                <option v-for="type in inquiryTypes" :key="type.value" :value="type.value">
                                    {{ type.label }}
                                </option>
                            </select>
                            <div v-if="form.errors.inquiry_type" class="mt-1 text-red-500 text-sm font-body">{{ form.errors.inquiry_type }}</div>
                        </div>
                    </div>

                    <!-- Subject -->
                    <div>
                        <label for="subject" class="block font-display font-medium text-primary dark:text-gray-100 mb-2">
                            Subject *
                        </label>
                        <input
                            id="subject"
                            v-model="form.subject"
                            type="text"
                            required
                            class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-secondary/30 dark:border-gray-600 rounded-lg font-body text-primary dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-accent focus:border-accent transition-all duration-200"
                            placeholder="Brief description of your inquiry"
                        />
                        <div v-if="form.errors.subject" class="mt-1 text-red-500 text-sm font-body">{{ form.errors.subject }}</div>
                    </div>

                    <!-- Property-specific fields -->
                    <div v-if="form.inquiry_type === 'villa_rental' || form.inquiry_type === 'property_sale'" class="space-y-6 p-6 bg-secondary/5 rounded-xl border border-secondary/20">
                        <h3 class="font-display text-lg font-semibold text-primary dark:text-gray-100">
                            {{ form.inquiry_type === 'villa_rental' ? 'Villa Rental Details' : 'Property Purchase Details' }}
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="property_interest" class="block font-display font-medium text-primary dark:text-gray-100 mb-2">
                                    {{ form.inquiry_type === 'villa_rental' ? 'Preferred Area' : 'Property Location Interest' }}
                                </label>
                                <input
                                    id="property_interest"
                                    v-model="form.property_interest"
                                    type="text"
                                    class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-secondary/30 dark:border-gray-600 rounded-lg font-body text-primary dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-accent focus:border-accent transition-all duration-200"
                                    placeholder="e.g., Canggu, Seminyak, Uluwatu"
                                />
                            </div>

                            <div>
                                <label for="budget" class="block font-display font-medium text-primary dark:text-gray-100 mb-2">
                                    Budget Range
                                </label>
                                <select
                                    id="budget"
                                    v-model="form.budget"
                                    class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-secondary/30 dark:border-gray-600 rounded-lg font-body text-primary dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-accent focus:border-accent transition-all duration-200"
                                >
                                    <option v-for="range in budgetRanges" :key="range.value" :value="range.value">
                                        {{ range.label }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div v-if="form.inquiry_type === 'villa_rental'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="travel_dates" class="block font-display font-medium text-primary dark:text-gray-100 mb-2">
                                    Travel Dates
                                </label>
                                <input
                                    id="travel_dates"
                                    v-model="form.travel_dates"
                                    type="text"
                                    class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-secondary/30 dark:border-gray-600 rounded-lg font-body text-primary dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-accent focus:border-accent transition-all duration-200"
                                    placeholder="e.g., Dec 15-25, 2024 or Flexible"
                                />
                            </div>

                            <div>
                                <label for="guests" class="block font-display font-medium text-primary dark:text-gray-100 mb-2">
                                    Number of Guests
                                </label>
                                <input
                                    id="guests"
                                    v-model="form.guests"
                                    type="number"
                                    min="1"
                                    max="20"
                                    class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-secondary/30 dark:border-gray-600 rounded-lg font-body text-primary dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-accent focus:border-accent transition-all duration-200"
                                    placeholder="Number of guests"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Message -->
                    <div>
                        <label for="message" class="block font-display font-medium text-primary dark:text-gray-100 mb-2">
                            Message *
                        </label>
                        <textarea
                            id="message"
                            v-model="form.message"
                            required
                            rows="6"
                            class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-secondary/30 dark:border-gray-600 rounded-lg font-body text-primary dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-accent focus:border-accent transition-all duration-200 resize-vertical"
                            placeholder="Tell us more about what you're looking for, any specific requirements, or questions you have..."
                        ></textarea>
                        <div v-if="form.errors.message" class="mt-1 text-red-500 text-sm font-body">{{ form.errors.message }}</div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex flex-col items-center space-y-4">
                        <button
                            type="submit"
                            :disabled="isSubmitting || form.processing"
                            class="w-full md:w-auto px-8 py-4 bg-accent text-white font-display font-medium rounded-xl hover:bg-accent-dark transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed transform hover:-translate-y-1 shadow-lg hover:shadow-xl"
                        >
                            <span v-if="!isSubmitting && !form.processing" class="flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                                Send Message
                            </span>
                            <span v-else class="flex items-center justify-center">
                                <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Sending...
                            </span>
                        </button>

                        <!-- Success/Error Message -->
                        <div v-if="submitMessage" class="text-center">
                            <p :class="submitMessage.includes('Thank you') ? 'text-green-600 bg-green-50 border-green-200' : 'text-red-600 bg-red-50 border-red-200'" 
                               class="px-4 py-2 rounded-lg border font-body">
                                {{ submitMessage }}
                            </p>
                        </div>
                    </div>
                </form>
            </SupportContentCard>

            <!-- FAQ Quick Links -->
            <SupportContentCard title="Quick Help">
                <div class="prose prose-lg max-w-none">
                    <p class="font-body text-primary/80 dark:text-gray-300 leading-relaxed mb-4">
                        Looking for quick answers? Check out our frequently asked questions or browse our support resources:
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <a 
                            href="/support/faq"
                            class="inline-flex items-center px-6 py-3 bg-white dark:bg-gray-800 text-primary dark:text-gray-100 font-display font-medium rounded-lg border border-secondary/30 dark:border-gray-600 hover:bg-secondary/10 hover:border-accent/50 dark:hover:bg-gray-700 transition-all duration-300 transform hover:-translate-y-1"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            FAQ
                        </a>
                        <a 
                            href="/properties"
                            class="inline-flex items-center px-6 py-3 bg-white dark:bg-gray-800 text-primary dark:text-gray-100 font-display font-medium rounded-lg border border-secondary/30 dark:border-gray-600 hover:bg-secondary/10 hover:border-accent/50 dark:hover:bg-gray-700 transition-all duration-300 transform hover:-translate-y-1"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            Browse Properties
                        </a>
                        <a 
                            href="/support/privacy-policy"
                            class="inline-flex items-center px-6 py-3 bg-white dark:bg-gray-800 text-primary dark:text-gray-100 font-display font-medium rounded-lg border border-secondary/30 dark:border-gray-600 hover:bg-secondary/10 hover:border-accent/50 dark:hover:bg-gray-700 transition-all duration-300 transform hover:-translate-y-1"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            Privacy & Terms
                        </a>
                    </div>
                </div>
            </SupportContentCard>
        </div>
    </SupportPageLayout>
</template>