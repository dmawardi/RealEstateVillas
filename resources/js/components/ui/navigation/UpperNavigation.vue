<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const page = usePage();
const isMobileMenuOpen = ref(false);

const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

const closeMobileMenu = () => {
    isMobileMenuOpen.value = false;
};
</script>

<template>
    <nav class="sticky top-0 z-50 w-full bg-background backdrop-blur-md dark:bg-background/80 border-b border-border/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo Section -->
                <div class="flex items-center flex-shrink-0">
                    <Link :href="route('home')" class="flex items-center space-x-2 group">
                        <img 
                            src="/images/logo/Logo.png" 
                            alt="Bali Villa Spot Logo" 
                            class="h-8 w-auto sm:h-10 transition-transform group-hover:scale-105" 
                        />
                        <span class="hidden sm:block text-lg font-semibold text-secondary dark:text-secondary group-hover:text-primary dark:group-hover:text-accent transition-colors">
                            Bali Villa Spot
                        </span>
                    </Link>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-1">
                    <Link
                        :href="route('properties.index')"
                        class="px-4 py-2 text-sm font-medium text-gray-900 hover:text-accent hover:bg-secondary/10 rounded-lg transition-all duration-200"
                    >
                        Properties
                    </Link>
                    <Link
                        :href="route('contact')"
                        class="px-4 py-2 text-sm font-medium text-gray-900 hover:text-accent hover:bg-secondary/10 rounded-lg transition-all duration-200"
                    >
                        Contact
                    </Link>
                    <template v-if="$page.props.auth.user">
                        <Link
                            :href="route('dashboard')"
                            class="px-4 py-2 text-sm font-medium text-gray-900 hover:text-accent hover:bg-secondary/10 rounded-lg transition-all duration-200"
                        >
                            Dashboard
                        </Link>
                        <Link
                            :href="route('my.bookings')"
                            class="px-4 py-2 text-sm font-medium text-gray-900 hover:text-accent hover:bg-secondary/10 rounded-lg transition-all duration-200"
                        >
                            My Bookings
                        </Link>
                        <Link
                            :href="route('my.favorites')"
                            class="px-4 py-2 text-sm font-medium text-gray-900 hover:text-accent hover:bg-secondary/10 rounded-lg transition-all duration-200"
                        >
                            Favorites
                        </Link>
                        <Link
                            :href="route('logout')"
                            method="post"
                            class="px-4 py-2 text-sm font-medium text-white bg-primary hover:bg-primary-600 dark:bg-primary dark:hover:bg-primary-600 rounded-lg transition-all duration-200 shadow-sm"
                        >
                            Log out
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="px-4 py-2 text-sm font-medium text-gray-900 hover:text-accent hover:bg-secondary/10 rounded-lg transition-all duration-200"
                        >
                            Log in
                        </Link>
                        <Link
                            :href="route('register')"
                            class="px-4 py-2 text-sm font-medium text-white bg-accent hover:bg-accent-600 dark:bg-accent dark:hover:bg-accent-600 rounded-lg transition-all duration-200 shadow-sm"
                        >
                            Register
                        </Link>
                    </template>
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden">
                    <button
                        @click="toggleMobileMenu"
                        class="inline-flex items-center justify-center p-2 rounded-lg text-foreground hover:text-accent hover:bg-secondary/10 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-accent transition-all duration-200"
                        aria-expanded="false"
                    >
                        <span class="sr-only">Open main menu</span>
                        <!-- Hamburger Icon -->
                        <svg 
                            v-if="!isMobileMenuOpen"
                            class="block h-6 w-6" 
                            fill="none" 
                            viewBox="0 0 24 24" 
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <!-- Close Icon -->
                        <svg 
                            v-else
                            class="block h-6 w-6" 
                            fill="none" 
                            viewBox="0 0 24 24" 
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div v-show="isMobileMenuOpen" class="md:hidden">
            <div class="px-2 pt-2 pb-3 space-y-1 bg-background/95 backdrop-blur-md border-t border-border/20">
                <Link
                    :href="route('properties.index')"
                    @click="closeMobileMenu"
                    class="block px-3 py-2 font-medium text-gray-900 hover:text-accent hover:bg-secondary/10 rounded-lg transition-all duration-200"
                >
                    Properties
                </Link>
                <Link
                    :href="route('contact')"
                    @click="closeMobileMenu"
                    class="block px-3 py-2 font-medium text-gray-900 hover:text-accent hover:bg-secondary/10 rounded-lg transition-all duration-200"
                >
                    Contact
                </Link>
                <template v-if="$page.props.auth.user">
                    <Link
                        :href="route('dashboard')"
                        @click="closeMobileMenu"
                        class="block px-3 py-2 font-medium text-gray-900 hover:text-accent hover:bg-secondary/10 rounded-lg transition-all duration-200"
                    >
                        Dashboard
                    </Link>
                    <Link
                        :href="route('my.bookings')"
                        @click="closeMobileMenu"
                        class="block px-3 py-2 font-medium text-gray-900 hover:text-accent hover:bg-secondary/10 rounded-lg transition-all duration-200"
                    >
                        My Bookings
                    </Link>
                    <Link
                        :href="route('my.favorites')"
                        @click="closeMobileMenu"
                        class="block px-3 py-2 text-base font-medium text-gray-900 hover:text-accent hover:bg-secondary/10 rounded-lg transition-all duration-200"
                    >
                        Favorites
                    </Link>
                    <Link
                        :href="route('logout')"
                        method="post"
                        @click="closeMobileMenu"
                        class="block px-3 py-2 text-base font-medium bg-primary hover:bg-primary-600 dark:bg-primary dark:hover:bg-primary-600 rounded-lg transition-all duration-200 text-center"
                    >
                        Log out
                    </Link>
                </template>
                <template v-else>
                    <Link
                        :href="route('login')"
                        @click="closeMobileMenu"
                        class="block px-3 py-2 text-base font-medium text-gray-900 hover:text-accent hover:bg-secondary/10 rounded-lg transition-all duration-200"
                    >
                        Log in
                    </Link>
                    <Link
                        :href="route('register')"
                        @click="closeMobileMenu"
                        class="block px-3 py-2 text-base font-medium bg-accent hover:bg-accent-600 dark:bg-accent dark:hover:bg-accent-600 rounded-lg transition-all duration-200 text-center"
                    >
                        Register
                    </Link>
                </template>
            </div>
        </div>
    </nav>
</template>