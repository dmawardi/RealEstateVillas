<script setup lang="ts">
import { ref, computed } from 'vue';

interface ProcessStep {
    id: number;
    title: string;
    description: string;
    icon: string;
    detail?: string;
}

const activeProcess = ref<'rental' | 'sale'>('rental');

const rentalSteps: ProcessStep[] = [
    {
        id: 1,
        title: 'Find Your Dream Property',
        description: 'Browse our curated selection of premium vacation rentals in Bali',
        icon: 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z',
        detail: 'Use our advanced search filters to find properties by location, dates, amenities, and price range. Save your favorites for easy comparison.'
    },
    {
        id: 2,
        title: 'Send Booking Enquiry',
        description: 'Submit your details and preferred dates for availability confirmation',
        icon: 'M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        detail: 'Provide your travel dates, guest count, and any special requirements. Our team will check availability and get back to you within 24 hours.'
    },
    {
        id: 3,
        title: 'Receive Confirmation',
        description: 'Get your booking confirmation with all the important details',
        icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        detail: 'Once confirmed, you\'ll receive detailed information about check-in procedures, house rules, and local recommendations.'
    },
    {
        id: 4,
        title: 'Make Payment',
        description: 'Complete payment via secure bank transfer',
        icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
        detail: 'Transfer the deposit to our account. Once received, your booking is confirmed and protected by our cancellation policy.'
    },
    {
        id: 5,
        title: 'Enjoy Your Stay!',
        description: 'Arrive at your vacation home and start creating memories',
        icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        detail: 'Check-in details will be provided 24 hours before arrival. Our local team is available for support throughout your stay.'
    }
];

const saleSteps: ProcessStep[] = [
    {
        id: 1,
        title: 'Discover Properties',
        description: 'Explore our portfolio of premium properties for sale in Bali',
        icon: 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z',
        detail: 'Browse by location, property type, price range, and investment potential. Schedule virtual or in-person viewings.'
    },
    {
        id: 2,
        title: 'Property Inspection',
        description: 'Conduct thorough inspections and due diligence',
        icon: 'M9 5H7a2 2 0 00-2 2v6a2 2 0 002 2h2m0-8h2a2 2 0 012 2v6a2 2 0 01-2 2H9m0-8V5a2 2 0 012-2h2a2 2 0 012 2v14l-5-2-5 2V5z',
        detail: 'Professional property inspection, legal document review, and verification of all permits and certificates.'
    },
    {
        id: 3,
        title: 'Legal Process',
        description: 'Navigate the legal requirements with expert assistance',
        icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        detail: 'Our legal team handles contracts, permits, tax obligations, and ensures compliance with Indonesian property laws.'
    },
    {
        id: 4,
        title: 'Property Transfer',
        description: 'Complete the purchase and receive your property ownership',
        icon: 'M15 7a2 2 0 012 2m0 0a2 2 0 012 2 2 2 0 01-2 2 2 2 0 01-2-2m2-2H9a2 2 0 00-2 2v10m0 0V9a2 2 0 012-2h2',
        detail: 'Final payment, property handover, and transfer of ownership documents. Welcome to your new Bali property!'
    }
];

const currentSteps = computed(() => {
    return activeProcess.value === 'rental' ? rentalSteps : saleSteps;
});

const processTitle = computed(() => {
    return activeProcess.value === 'rental' ? 'Your Rental Journey' : 'Your Purchase Journey';
});

const processSubtitle = computed(() => {
    return activeProcess.value === 'rental' 
        ? 'From search to stay - we make it simple'
        : 'From search to ownership - guided every step';
});

const switchProcess = (process: 'rental' | 'sale') => {
    activeProcess.value = process;
};
</script>

<template>
    <section class="py-16 bg-base dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center mb-12">
                <h2 class="font-display text-3xl font-bold text-primary dark:text-gray-100 sm:text-4xl mb-4">
                    How It Works
                </h2>
                <p class="font-body text-lg text-primary/70 dark:text-gray-400 max-w-2xl mx-auto mb-8">
                    We've streamlined the process to make your property journey as smooth as possible
                </p>

                <!-- Process Toggle -->
                <div class="flex justify-center mb-8">
                    <div class="flex bg-white dark:bg-gray-800 rounded-xl p-1.5 shadow-lg border border-secondary/30 dark:border-gray-700">
                        <button
                            @click="switchProcess('rental')"
                            :class="[
                                'flex items-center px-6 py-3 rounded-lg font-display font-medium transition-all duration-300',
                                activeProcess === 'rental'
                                    ? 'bg-accent text-white shadow-md transform scale-105'
                                    : 'text-primary/70 dark:text-gray-300 hover:text-primary hover:bg-secondary/10 dark:hover:text-gray-100'
                            ]"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Vacation Rentals
                        </button>
                        <button
                            @click="switchProcess('sale')"
                            :class="[
                                'flex items-center px-6 py-3 rounded-lg font-display font-medium transition-all duration-300',
                                activeProcess === 'sale'
                                    ? 'bg-accent text-white shadow-md transform scale-105'
                                    : 'text-primary/70 dark:text-gray-300 hover:text-primary hover:bg-secondary/10 dark:hover:text-gray-100'
                            ]"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Property Sales
                        </button>
                    </div>
                </div>

                <!-- Dynamic Title -->
                <div class="text-center mb-8">
                    <h3 class="font-display text-2xl font-semibold text-primary dark:text-gray-100 mb-2">
                        {{ processTitle }}
                    </h3>
                    <p class="font-body text-primary/60 dark:text-gray-400">
                        {{ processSubtitle }}
                    </p>
                </div>
            </div>

            <!-- Process Steps -->
            <div class="relative">
                <!-- Connection Line -->
                <div class="hidden md:block absolute top-16 left-1/2 transform -translate-x-1/2 w-1 bg-gradient-to-b from-secondary/40 to-accent/60 dark:from-secondary/30 dark:to-accent/40" 
                     :style="{ height: `${(currentSteps.length - 1) * 200}px` }">
                </div>

                <!-- Steps Grid -->
                <div class="space-y-8 md:space-y-16">
                    <div 
                        v-for="(step, index) in currentSteps" 
                        :key="`${activeProcess}-${step.id}`"
                        :class="[
                            'relative flex flex-col md:flex-row items-center',
                            index % 2 === 0 ? 'md:flex-row' : 'md:flex-row-reverse'
                        ]"
                    >
                        <!-- Step Number Circle -->
                        <div class="flex-shrink-0 relative z-10 mb-4 md:mb-0">
                            <div class="w-16 h-16 bg-gradient-to-br from-accent to-accent-dark rounded-full flex items-center justify-center shadow-lg ring-4 ring-base ring-opacity-50 dark:ring-gray-800">
                                <span class="text-2xl font-bold font-display text-white">{{ step.id }}</span>
                            </div>
                            <!-- Step Icon Background -->
                            <div class="absolute -top-2 -right-2 w-8 h-8 bg-secondary rounded-full flex items-center justify-center shadow-md border-2 border-base dark:border-gray-800">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="step.icon" />
                                </svg>
                            </div>
                        </div>

                        <!-- Step Content -->
                        <div :class="[
                            'flex-1 max-w-lg',
                            index % 2 === 0 ? 'md:ml-12 md:text-left' : 'md:mr-12 md:text-right',
                            'text-center md:text-inherit'
                        ]">
                            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-secondary/20 dark:border-gray-700 hover:shadow-lg hover:border-accent/30 transition-all duration-300 transform hover:-translate-y-1">
                                <h4 class="font-display text-xl font-semibold text-primary dark:text-gray-100 mb-3">
                                    {{ step.title }}
                                </h4>
                                <p class="font-body text-primary/80 dark:text-gray-300 mb-4">
                                    {{ step.description }}
                                </p>
                                <p class="font-body text-sm text-primary/60 dark:text-gray-400 leading-relaxed">
                                    {{ step.detail }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Call to Action -->
            <div class="text-center mt-16">
                <div class="bg-gradient-to-r from-secondary/10 to-accent/10 dark:from-secondary/5 dark:to-accent/5 rounded-2xl p-8 border border-secondary/20 dark:border-gray-700 shadow-sm">
                    <h3 class="font-display text-2xl font-semibold text-primary dark:text-gray-100 mb-4">
                        Ready to Get Started?
                    </h3>
                    <p class="font-body text-primary/70 dark:text-gray-400 mb-6 max-w-2xl mx-auto text-lg">
                        <span v-if="activeProcess === 'rental'">
                            Browse our collection of premium vacation rentals and start planning your perfect Bali getaway.
                        </span>
                        <span v-else>
                            Explore investment opportunities and find your dream property in one of the world's most desirable destinations.
                        </span>
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a 
                            :href="activeProcess === 'rental' ? '/properties?listing_type=for_rent' : '/properties?listing_type=for_sale'"
                            class="inline-flex items-center px-8 py-4 bg-accent text-white font-display font-medium rounded-xl hover:bg-accent-dark hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 shadow-sm"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <span v-if="activeProcess === 'rental'">Browse Rentals</span>
                            <span v-else>View Properties</span>
                        </a>
                        
                        <a 
                            :href="route('contact')"
                            class="inline-flex items-center px-8 py-4 bg-white dark:bg-gray-800 text-primary dark:text-gray-100 font-display font-medium rounded-xl border border-secondary/30 dark:border-gray-600 hover:bg-secondary/10 hover:border-accent/50 dark:hover:bg-gray-700 transition-all duration-300 transform hover:-translate-y-1 shadow-sm"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            Get Expert Help
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>