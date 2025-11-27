// resources/js/components/support/SupportContactCTA.vue
<script setup lang="ts">
interface Props {
    businessPhone?: string;
    title?: string;
    description?: string;
}

const { 
    businessPhone,
    title = "Need Help?",
    description = "Our team is here to assist you with any questions or concerns."
} = defineProps<Props>();
</script>

<template>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
        <div class="bg-gradient-to-r from-secondary/10 to-accent/10 dark:from-secondary/5 dark:to-accent/5 rounded-2xl p-8 border border-secondary/20 dark:border-gray-700 shadow-sm">
            <div class="text-center">
                <h3 class="font-display text-2xl font-semibold text-primary dark:text-gray-100 mb-4">
                    {{ title }}
                </h3>
                <p class="font-body text-primary/70 dark:text-gray-400 mb-6 max-w-2xl mx-auto text-lg">
                    {{ description }}
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a 
                        href="/contact"
                        class="inline-flex items-center px-8 py-4 bg-accent text-white font-display font-medium rounded-xl hover:bg-accent-dark hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 shadow-sm"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        Contact Our Team
                    </a>
                    
                    <a 
                        v-if="businessPhone"
                        :href="`tel:${businessPhone}`"
                        class="inline-flex items-center px-8 py-4 bg-white dark:bg-gray-800 text-primary dark:text-gray-100 font-display font-medium rounded-xl border border-secondary/30 dark:border-gray-600 hover:bg-secondary/10 hover:border-accent/50 dark:hover:bg-gray-700 transition-all duration-300 transform hover:-translate-y-1 shadow-sm"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.037 11.037 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        Call {{ businessPhone }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>