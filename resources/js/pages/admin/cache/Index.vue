<script setup lang="ts">
// filepath: /Users/d/Web Development/projects/RealEstate/resources/js/pages/cache/Index.vue
import { SEO } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';
import SEOHead from '@/components/SEOHead.vue';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { RotateCcw, Server, Zap, AlertCircle } from 'lucide-vue-next';

interface AppInfo {
    env?: string;
    version?: string;
    cache_driver?: string;
    debug?: boolean;
}

interface Props {
    seoData?: SEO;
    app?: AppInfo;
}

const { seoData } = defineProps<Props>();

// Loading state
const isClearing = ref(false);

// Clear cache function
const clearCache = async () => {
    isClearing.value = true;
    
    try {
        await router.post('/admin/cache/clear', {}, {
            preserveScroll: true,
            onSuccess: () => {
                // Success is handled by flash message from controller
                console.log('Cache cleared successfully');
            },
            onError: (errors) => {
                console.error('Failed to clear cache:', errors);
            },
            onFinish: () => {
                isClearing.value = false;
            }
        });
    } catch (error) {
        console.error('Error clearing cache:', error);
        isClearing.value = false;
    }
};
</script>

<template>
    <SEOHead v-if="seoData" :seoData="seoData" />

    <AppLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-4">
                    <Server class="h-8 w-8 text-blue-600 dark:text-blue-400" />
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Cache Management</h1>
                </div>
                <p class="text-gray-600 dark:text-gray-400">
                    Manage application cache to improve performance and resolve potential issues
                </p>
            </div>

            <!-- Cache Management Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <!-- Card Header -->
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <RotateCcw class="h-6 w-6 text-gray-600 dark:text-gray-400" />
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                            Clear Application Cache
                        </h2>
                    </div>
                </div>

                <!-- Card Content -->
                <div class="px-6 py-8">
                    <div class="max-w-2xl">
                        <!-- Info Section -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">
                                What does cache clearing do?
                            </h3>
                            <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                <p class="flex items-start gap-2">
                                    <span class="inline-block w-1.5 h-1.5 bg-blue-600 rounded-full mt-2 flex-shrink-0"></span>
                                    <span><strong>Application Cache:</strong> Clears cached configuration, routes, and compiled views</span>
                                </p>
                                <p class="flex items-start gap-2">
                                    <span class="inline-block w-1.5 h-1.5 bg-blue-600 rounded-full mt-2 flex-shrink-0"></span>
                                    <span><strong>Config Cache:</strong> Removes cached configuration files</span>
                                </p>
                                <p class="flex items-start gap-2">
                                    <span class="inline-block w-1.5 h-1.5 bg-blue-600 rounded-full mt-2 flex-shrink-0"></span>
                                    <span><strong>Route Cache:</strong> Clears cached routing information</span>
                                </p>
                                <p class="flex items-start gap-2">
                                    <span class="inline-block w-1.5 h-1.5 bg-blue-600 rounded-full mt-2 flex-shrink-0"></span>
                                    <span><strong>View Cache:</strong> Removes compiled Blade view files</span>
                                </p>
                            </div>
                        </div>

                        <!-- Warning Notice -->
                        <div class="mb-8 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg">
                            <div class="flex items-start gap-3">
                                <AlertCircle class="h-5 w-5 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" />
                                <div>
                                    <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-300 mb-1">
                                        Important Notice
                                    </h4>
                                    <p class="text-sm text-yellow-700 dark:text-yellow-400">
                                        Clearing cache may temporarily slow down the application while caches rebuild. 
                                        This is normal and performance will return to optimal levels after a few requests.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="flex items-center gap-4">
                            <button
                                @click="clearCache"
                                :disabled="isClearing"
                                :class="[
                                    'flex items-center gap-3 px-6 py-3 rounded-lg font-medium text-white transition-all duration-200 transform',
                                    isClearing
                                        ? 'bg-gray-400 dark:bg-gray-600 cursor-not-allowed'
                                        : 'bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-800 hover:scale-105 focus:ring-4 focus:ring-red-200 dark:focus:ring-red-900'
                                ]"
                            >
                                <Zap 
                                    :class="[
                                        'h-5 w-5',
                                        isClearing ? 'animate-pulse' : ''
                                    ]" 
                                />
                                <span>
                                    {{ isClearing ? 'Clearing Cache...' : 'Clear All Cache' }}
                                </span>
                            </button>

                            <!-- Status Text -->
                            <div v-if="isClearing" class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                                <div class="animate-spin rounded-full h-4 w-4 border-2 border-gray-300 border-t-blue-600"></div>
                                <span>Processing cache clearance...</span>
                            </div>
                        </div>

                        <!-- Additional Info -->
                        <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                                When to clear cache:
                            </h4>
                            <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                <li>• After making configuration changes</li>
                                <li>• When experiencing unexpected application behavior</li>
                                <li>• After deploying new code updates</li>
                                <li>• When troubleshooting performance issues</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>