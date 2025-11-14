<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Props {
    businessPhone?: string;
    businessEmail?: string;
}

const { businessPhone, businessEmail } = defineProps<Props>();
// Newsletter subscription state
const email = ref('');
const isSubmitting = ref(false);
const subscriptionMessage = ref('');


const subscribeToNewsletter = async () => {
    if (!email.value) return;
    
    isSubmitting.value = true;
    
    try {
        // Replace with your actual newsletter subscription logic
        await new Promise(resolve => setTimeout(resolve, 1000)); // Simulate API call
        subscriptionMessage.value = 'Thank you for subscribing!';
        email.value = '';
    } catch (error) {
        subscriptionMessage.value = 'Something went wrong. Please try again.';
        console.error(error);
    } finally {
        isSubmitting.value = false;
        setTimeout(() => {
            subscriptionMessage.value = '';
        }, 3000);
    }
};

const currentYear = new Date().getFullYear();

const socialLinks = [
    { name: 'Facebook', icon: 'facebook', href: '#' },
    { name: 'Twitter', icon: 'twitter', href: '#' },
    { name: 'Instagram', icon: 'instagram', href: '#' },
    { name: 'LinkedIn', icon: 'linkedin', href: '#' },
];

const quickLinks = [
    { name: 'Properties', href: '/properties' },
    { name: 'Buy', href: '/properties?type=sale' },
    { name: 'Rent', href: '/properties?type=rent' },
    { name: 'Commercial', href: '/properties?category=commercial' },
    { name: 'New Projects', href: '/projects' },
];

const companyLinks = [
    { name: 'About Us', href: '/about' },
    { name: 'Our Team', href: '/team' },
    { name: 'News & Blog', href: '/blog' },
    { name: 'Contact', href: '/contact' },
];

const supportLinks = [
    { name: 'Privacy Policy', href: '/privacy' },
    { name: 'Terms of Service', href: '/terms' },
];

const getSocialIcon = (iconName: string) => {
    const icons = {
        facebook: 'M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z',
        twitter: 'M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z',
        instagram: 'M12.017 0C5.396 0 .029 5.367.029 11.987c0 6.62 5.367 11.987 11.988 11.987s11.987-5.367 11.987-11.987C24.014 5.367 18.647.001 12.017.001zM12 16.624c-2.548 0-4.623-2.075-4.623-4.624S9.452 7.376 12 7.376s4.623 2.075 4.623 4.624S14.548 16.624 12 16.624zm4.804-8.306c-.596 0-1.08-.484-1.08-1.08s.484-1.08 1.08-1.08 1.08.484 1.08 1.08-.484 1.08-1.08 1.08z',
        linkedin: 'M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z'
    };
    return icons[iconName as keyof typeof icons] || '';
};
</script>

<template>
    <footer class="bg-gray-900 text-white mt-4">
        <!-- Main Footer Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8">
                <!-- Company Info & Newsletter -->
                <div class="lg:col-span-2">
                    <!-- Logo & Description -->
                    <div class="mb-8">
                        <Link href="/" class="flex items-center mb-4">
                            <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-6a1 1 0 00-1-1H9a1 1 0 00-1 1v6a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <span class="text-xl font-bold">RealEstate</span>
                        </Link>
                        <p class="text-gray-300 leading-relaxed max-w-md">
                            Your trusted partner in finding the perfect Bali property for your needs.
                        </p>
                    </div>

                    <!-- Newsletter Subscription -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4">Stay Updated</h3>
                        <p class="text-gray-300 text-sm mb-4">
                            Get the latest property listings and market insights delivered to your inbox.
                        </p>
                        <form @submit.prevent="subscribeToNewsletter" class="flex flex-col sm:flex-row gap-3">
                            <div class="flex-1">
                                <input
                                    v-model="email"
                                    type="email"
                                    placeholder="Enter your email"
                                    required
                                    class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                            </div>
                            <button
                                type="submit"
                                :disabled="isSubmitting"
                                class="px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed whitespace-nowrap"
                            >
                                <span v-if="!isSubmitting">Subscribe</span>
                                <span v-else class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Subscribing...
                                </span>
                            </button>
                        </form>
                        <div v-if="subscriptionMessage" class="mt-3 text-sm" :class="subscriptionMessage.includes('Thank you') ? 'text-green-400' : 'text-red-400'">
                            {{ subscriptionMessage }}
                        </div>
                    </div>

                    <!-- Social Media Links -->
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Follow Us</h3>
                        <div class="flex space-x-4">
                            <a
                                v-for="social in socialLinks"
                                :key="social.name"
                                :href="social.href"
                                :aria-label="social.name"
                                class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center text-gray-400 hover:text-white hover:bg-blue-600 transition-all duration-200"
                            >
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path :d="getSocialIcon(social.icon)" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-semibold mb-6">Properties</h3>
                    <ul class="space-y-3">
                        <li v-for="link in quickLinks" :key="link.name">
                            <Link 
                                :href="link.href"
                                class="text-gray-300 hover:text-white transition-colors duration-200 text-sm"
                            >
                                {{ link.name }}
                            </Link>
                        </li>
                    </ul>
                </div>

                <!-- Company Links -->
                <div>
                    <h3 class="text-lg font-semibold mb-6">Company</h3>
                    <ul class="space-y-3">
                        <li v-for="link in companyLinks" :key="link.name">
                            <Link 
                                :href="link.href"
                                class="text-gray-300 hover:text-white transition-colors duration-200 text-sm"
                            >
                                {{ link.name }}
                            </Link>
                        </li>
                    </ul>
                </div>

                <!-- Support Links -->
                <div>
                    <h3 class="text-lg font-semibold mb-6">Support</h3>
                    <ul class="space-y-3">
                        <li v-for="link in supportLinks" :key="link.name">
                            <Link 
                                :href="link.href"
                                class="text-gray-300 hover:text-white transition-colors duration-200 text-sm"
                            >
                                {{ link.name }}
                            </Link>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Contact Info Bar -->
        <div class="border-t border-gray-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Phone -->
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gray-800 rounded-lg flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.037 11.037 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm text-gray-400 mb-1">Call Us</div>
                            <a :href="`tel:${businessPhone}`" class="text-white hover:text-blue-400 transition-colors">
                                {{ businessPhone }}
                            </a>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gray-800 rounded-lg flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm text-gray-400 mb-1">Email Us</div>
                            <a :href="`mailto:${businessEmail}`" class="text-white hover:text-blue-400 transition-colors">
                                {{ businessEmail }}
                            </a>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gray-800 rounded-lg flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm text-gray-400 mb-1">Service Area</div>
                            <div class="text-white">
                                Canggu, Seminyak, Pererenan, Umalas<br>
                                <span class="text-sm text-gray-300">Bali, Indonesia</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="border-t border-gray-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="text-sm text-gray-400 mb-4 md:mb-0">
                        © {{ currentYear }} RealEstate. All rights reserved.
                    </div>
                    <div class="flex flex-wrap justify-center md:justify-end gap-6 text-sm">
                        <Link href="/privacy" class="text-gray-400 hover:text-white transition-colors">
                            Privacy Policy
                        </Link>
                        <Link href="/terms" class="text-gray-400 hover:text-white transition-colors">
                            Terms of Service
                        </Link>
                        <Link href="/cookies" class="text-gray-400 hover:text-white transition-colors">
                            Cookie Policy
                        </Link>
                        <Link href="/accessibility" class="text-gray-400 hover:text-white transition-colors">
                            Accessibility
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</template>