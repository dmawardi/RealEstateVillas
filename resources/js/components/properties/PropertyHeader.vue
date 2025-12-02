<script setup lang="ts">
import type { DetailedPricing, Property } from '@/types';
import { computed, defineProps, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { formatPrice, formatPropertyType } from '@/utils/formatters'; // Importing formatPrice utility
import { MapPin, Heart } from 'lucide-vue-next';
import { calculateRates } from '@/utils';

const page = usePage();
const user = computed(() => page.props.auth?.user);

interface Props {
    property: Property;
}

const { property } = defineProps<Props>();

// Favorite state
const isFavorited = ref(property.is_favorited || false);
const isToggling = ref(false);

// Toggle favorite
const toggleFavorite = async () => {
    if (user.value == null) {
        // Redirect to login if not authenticated
        router.visit('/login');
        return;
    }

    isToggling.value = true;
    try {
        await router.post(route('properties.toggle-favorite', property.slug), {}, {
            preserveScroll: true,
            onSuccess: () => {
                isFavorited.value = !isFavorited.value;
            },
            onError: (errors: any) => {
                console.error('Failed to toggle favorite:', errors);
            },
            onFinish: () => {
                isToggling.value = false;
            }
        });
    } catch (error) {
        console.error('Error toggling favorite:', error);
        isToggling.value = false;
    }
};

// Computed property to show detailed pricing info
const detailedPricing = computed((): DetailedPricing | null => {
    if (property.listing_type !== 'for_rent') return null;
    
    const currentPricing = property.pricing && property.pricing.length > 0 
        ? property.pricing[0] 
        : null;
        
    if (!currentPricing) return null;
    
    const rates = calculateRates(currentPricing);
    if (!rates) return null;

    return {
        nightly: {
            rate: rates.nightly,
            display: formatPrice(rates.nightly)
        },
        weekly: {
            rate: rates.weekly,
            display: formatPrice(rates.weekly),
            discount: rates.weeklyDiscount,
            hasDiscount: rates.weeklyDiscount > 0
        },
        monthly: {
            rate: rates.monthly,
            display: formatPrice(rates.monthly),
            discount: rates.monthlyDiscount,
            hasDiscount: rates.monthlyDiscount > 0
        },
        periodName: currentPricing.name ?? undefined
    };
});
</script>

<template>
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex-1">
                <!-- Title with Favorite Button -->
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                            {{ property.title }}
                        </h1>
                        <div class="flex text-lg text-gray-600 dark:text-gray-400 mb-3 h-6 align-middle">
                            <component v-if="MapPin" :is="MapPin" class="h-4 w-4 mr-2 flex-shrink-0 my-auto" />
                            <p class="text-gray-600 dark:text-gray-400 truncate">
                                {{ property.village }}, {{ property.district }}
                            </p>
                        </div>
                    </div>
                    
                    <!-- Favorite Button -->
                    <button
                        @click="toggleFavorite"
                        :disabled="isToggling"
                        class="flex-shrink-0 p-2 rounded-full transition-all duration-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50"
                        :title="isFavorited ? 'Remove from favorites' : 'Add to favorites'"
                    >
                        <Heart 
                            :class="[
                                'w-6 h-6 transition-all duration-200',
                                isFavorited 
                                    ? 'text-red-500 fill-current' 
                                    : 'text-gray-400 hover:text-red-400'
                            ]"
                        />
                    </button>
                </div>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                    <!-- Show pricing period name and additional rates for rentals -->
                    <div v-if="detailedPricing" class="mt-1 space-y-1">
                        <div class="text-lg text-gray-600 dark:text-gray-300">
                            <p class="truncate">
                                Nightly: {{ detailedPricing.nightly.display }}
                            </p>
                            <p v-if="detailedPricing.weekly.hasDiscount && (!detailedPricing.monthly.hasDiscount || detailedPricing.weekly.discount >= detailedPricing.monthly.discount)" class="truncate">
                                Weekly: {{ detailedPricing.weekly.display }}
                                <span class="text-green-600">({{ detailedPricing.weekly.discount }}% off)</span>
                            </p>
                            <p v-else-if="detailedPricing.monthly.hasDiscount && (!detailedPricing.weekly.hasDiscount || detailedPricing.monthly.discount > detailedPricing.weekly.discount)" class="truncate">
                                Monthly: {{ detailedPricing.monthly.display }}
                                <span class="text-green-600">({{ detailedPricing.monthly.discount }}% off)</span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="text-sm text-gray-500 capitalize">
                    {{ formatPropertyType(property.listing_type) }}
                </div>
            </div>
        </div>
    </div>
</template>