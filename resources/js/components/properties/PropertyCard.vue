<script setup lang="ts">
import { defineProps, computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import type { DetailedPricing, Property } from '@/types';
import { formatPrice } from '@/utils/formatters';
import CardImageGallery from '@/components/properties/CardImageGallery.vue';
import { MapPin, BedSingleIcon, BathIcon, LandPlotIcon, Heart } from 'lucide-vue-next';
import { calculateRates, getPriceDisplay, propertyTypeLabels } from '@/utils';

interface Props {
    property: Property;
}
const { property } = defineProps<Props>();

// Get current user
const page = usePage();
const user = computed(() => page.props.auth?.user);

// Favorite state
const isFavorited = ref(property.is_favorited || false);
const isToggling = ref(false);

const toggleFavorite = async (event: Event) => {
    // Prevent Link navigation of property card
    event?.stopPropagation();
    event?.preventDefault()

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

// Property type badge color
const propertyTypeBadge = computed(() => {
    const type = property.property_type?.toLowerCase();
    switch (type) {
        case 'villa':
            return 'bg-accent/90 text-white border border-accent';
        case 'land':
            return 'bg-secondary/90 text-white border border-secondary';
        case 'apartment':
            return 'bg-primary/90 text-white border border-primary';
        case 'commercial':
            return 'bg-orange-500 text-white border border-orange-500';
        case 'house':
            return 'bg-emerald-600 text-white border border-emerald-500';
        case 'guest_house':
            return 'bg-violet-600 text-white border border-violet-500';
        case 'townhouse':
            return 'bg-rose-600 text-white border border-rose-500';
        default:
            return 'bg-gray-600 text-white border border-gray-500';
    }
});

// Listing type indicator
const listingTypeColor = computed(() => {
    return property.listing_type === 'for_rent' 
        ? 'text-accent-600' 
        : 'text-secondary-600';
});
</script>

<template>
    <Link :href="route('properties.show', { slug: property.slug })">
        <div class="group bg-base dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden transition-all duration-300 hover:shadow-2xl hover:shadow-primary/20 hover:-translate-y-2 h-full flex flex-col border border-secondary/20 hover:border-accent/30">
            <!-- Property Image - Fixed aspect ratio -->
            <div class="flex-shrink-0 relative overflow-hidden">
                <CardImageGallery :property="property" :detailedPricing="detailedPricing" />
                
                <!-- Property Type Badge -->
                <div class="absolute top-3 left-3">
                    <span :class="[
                        'inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium font-display border backdrop-blur-sm transform transition-transform duration-200 group-hover:scale-105',
                        propertyTypeBadge
                    ]">
                        {{ propertyTypeLabels[property.property_type] }}
                    </span>
                </div>
                
                <!-- Listing Type Badge -->
                <div class="absolute top-3 right-3">
                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium font-display bg-white/90 border border-secondary/30 backdrop-blur-sm text-primary transform transition-transform duration-200 group-hover:scale-105">
                        {{ property.listing_type === 'for_rent' ? 'For Rent' : 'For Sale' }}
                    </span>
                </div>

                <!-- Favorite Heart Button -->
                <div class="absolute bottom-3 right-3">
                    <button
                        @click="toggleFavorite"
                        :disabled="isToggling"
                        class="p-2 rounded-full bg-white/90 backdrop-blur-sm border border-gray-200/50 shadow-sm hover:bg-white hover:scale-110 disabled:opacity-50 transition-all duration-200 group/heart"
                        :title="isFavorited ? 'Remove from favorites' : 'Add to favorites'"
                    >
                        <Heart
                            :class="[
                                'w-5 h-5 transition-all duration-200',
                                isFavorited 
                                    ? 'text-red-500 fill-current' 
                                    : 'text-gray-400 group-hover/heart:text-red-400'
                            ]"
                        />
                    </button>
                </div>
            </div>

            <!-- Property Content - Flexible height -->
            <div class="p-6 flex flex-col flex-grow">
                <!-- Title - Fixed height with line clamping -->
                <div class="mb-4">
                    <h3 class="font-display text-xl font-bold text-primary dark:text-primary-200 leading-tight group-hover:text-accent transition-colors duration-300 line-clamp-2 min-h-[3.5rem] flex items-start">
                        {{ property.title }}
                    </h3>
                </div>

                <!-- Location - Fixed height -->
                <div class="flex items-center text-sm text-primary/70 dark:text-gray-400 mb-5 h-6">
                    <MapPin class="h-4 w-4 mr-2 flex-shrink-0 text-secondary transition-colors duration-200 group-hover:text-accent" />
                    <p class="text-primary/70 dark:text-gray-400 truncate font-body">
                        {{ property.village }}, {{ property.district }}
                    </p>
                </div>

                <!-- Price - Minimum height container -->
                <div class="mb-5">
                    <div :class="[
                        'text-2xl font-bold font-display transition-all duration-300 group-hover:scale-105',
                        listingTypeColor
                    ]" v-html="getPriceDisplay(property)">
                    </div>
                    
                    <!-- Show pricing period name and additional rates for rentals -->
                    <div v-if="detailedPricing" class="mt-3 space-y-2">
                        <div class="text-sm font-body text-primary/60 dark:text-gray-400 space-y-1">
                            <p v-if="detailedPricing.weekly.hasDiscount" class="flex items-center gap-2 truncate">
                                <span class="font-medium text-primary/80">Weekly:</span> 
                                <span class="font-semibold">{{ detailedPricing.weekly.display }}</span>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-green-100 text-green-700 font-medium border border-green-200">
                                    {{ detailedPricing.weekly.discount }}% off
                                </span>
                            </p>
                            <p v-if="detailedPricing.monthly.hasDiscount" class="flex items-center gap-2 truncate">
                                <span class="font-medium text-primary/80">Monthly:</span> 
                                <span class="font-semibold">{{ detailedPricing.monthly.display }}</span>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-green-100 text-green-700 font-medium border border-green-200">
                                    {{ detailedPricing.monthly.discount }}% off
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Features Preview - Flexible but contained -->
                <div v-if="property.features && property.features.length > 0" class="mb-6">
                    <div class="flex flex-wrap gap-2">
                        <span 
                            v-for="feature in property.features.slice(0, 3)" 
                            :key="feature.id"
                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium font-body bg-secondary/10 text-secondary-600 border border-secondary/20 hover:bg-secondary/20 hover:scale-105 transition-all duration-200"
                        >
                            {{ feature.name }}
                        </span>
                        <span 
                            v-if="property.features.length > 3"
                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium font-body bg-primary/10 text-primary-600 border border-primary/20 hover:scale-105 transition-transform duration-200"
                        >
                            +{{ property.features.length - 3 }} more
                        </span>
                    </div>
                </div>

                <!-- Spacer to push stats to bottom -->
                <div class="flex-grow"></div>

                <!-- Property Stats - Fixed at bottom -->
                <div class="border-t border-secondary/20 dark:border-gray-700 pt-5 mt-auto">
                    <div class="grid grid-cols-3 gap-4">
                        <div v-if="property.land_size" class="text-center group/stat">
                            <LandPlotIcon class="h-6 w-6 mx-auto mb-2 text-secondary group-hover/stat:text-accent group-hover/stat:scale-110 transition-all duration-200" />
                            <div class="text-base font-semibold font-display text-primary dark:text-gray-100 group-hover/stat:text-accent transition-colors duration-200">
                                {{ property.land_size }}m²
                            </div>
                            <div class="text-xs font-body text-primary/60 mt-1">Land</div>
                        </div>
                        <div v-if="property.bedrooms" class="text-center group/stat">
                            <BedSingleIcon class="h-6 w-6 mx-auto mb-2 text-secondary group-hover/stat:text-accent group-hover/stat:scale-110 transition-all duration-200" />
                            <div class="text-base font-semibold font-display text-primary dark:text-gray-100 group-hover/stat:text-accent transition-colors duration-200">
                                {{ property.bedrooms }}
                            </div>
                            <div class="text-xs font-body text-primary/60 mt-1">
                                Bedroom{{ property.bedrooms > 1 ? 's' : '' }}
                            </div>
                        </div>
                        <div v-if="property.bathrooms" class="text-center group/stat">
                            <BathIcon class="h-6 w-6 mx-auto mb-2 text-secondary group-hover/stat:text-accent group-hover/stat:scale-110 transition-all duration-200" />
                            <div class="text-base font-semibold font-display text-primary dark:text-gray-100 group-hover/stat:text-accent transition-colors duration-200">
                                {{ property.bathrooms }}
                            </div>
                            <div class="text-xs font-body text-primary/60 mt-1">
                                Bathroom{{ property.bathrooms > 1 ? 's' : '' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hover Call-to-Action -->
                <div class="mt-5 pt-4 border-t border-secondary/20 opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-y-3 group-hover:translate-y-0">
                    <div class="flex items-center justify-between bg-gradient-to-r from-accent/5 to-secondary/5 rounded-lg px-4 py-3 border border-accent/20">
                        <span class="text-sm font-medium font-body text-primary/80">
                            {{ property.listing_type === 'for_rent' ? 'View Details & Book' : 'View Details & Inquire' }}
                        </span>
                        <svg class="w-5 h-5 text-accent transform group-hover:translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </Link>
</template>

