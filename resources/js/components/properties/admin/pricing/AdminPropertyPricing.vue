<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { formatPrice, formatDate, formatDateForInput } from '@/utils';
import { Property, PropertyPricing } from '@/types';

interface Props {
    property: Property;
    pricing: PropertyPricing[];
}

const { property, pricing } = defineProps<Props>();

// Component state
const showPricingForm = ref(false);
const editingPricing = ref<PropertyPricing | null>(null);
const processing = ref(false);
const errors = ref<Record<string, string>>({});
const deleting = ref<number | null>(null);
const activeTab = ref<'all' | 'current' | 'upcoming' | 'past'>('all');

const filteredPricing = computed(() => {
    switch (activeTab.value) {
        case 'current':
            return currentActivePricing.value ? [currentActivePricing.value] : [];
        case 'upcoming':
            return upcomingPricing.value;
        case 'past':
            return pastPricing.value;
        default:
            return sortedPricing.value;
    }
});

// Form data
const pricingForm = ref({
    name: '',
    nightly_rate: 0,
    weekly_discount_percent: 0,
    monthly_discount_percent: 0,
    weekend_premium_percent: 0,
    weekly_discount_active: false,
    monthly_discount_active: false,
    weekend_premium_active: false,
    currency: 'IDR',
    start_date: '',
    end_date: '',
    min_days_for_weekly: 7,
    min_days_for_monthly: 30,
});

// Computed properties
const isEditing = computed(() => !!editingPricing.value);

const sortedPricing = computed(() => {
    return [...pricing].sort((a, b) => {
        // ensure start_date is never undefined when passed to Date
        const dateA = new Date(a.start_date ?? 0);
        const dateB = new Date(b.start_date ?? 0);
        return dateA.getTime() - dateB.getTime();
    });
});

const currentActivePricing = computed(() => {
    const today = new Date().toISOString().split('T')[0];
    return pricing.find(p => 
        p.start_date && p.end_date && p.start_date <= today && p.end_date >= today
    );
});

const upcomingPricing = computed(() => {
    const today = new Date().toISOString().split('T')[0];
    return pricing.filter(p => typeof p.start_date === 'string' && p.start_date > today);
});

const pastPricing = computed(() => {
    const today = new Date().toISOString().split('T')[0];
    return pricing.filter(p => typeof p.end_date === 'string' && p.end_date < today);
});

// Form validation
const isFormValid = computed(() => {
    return (
        pricingForm.value.nightly_rate > 0 &&
        pricingForm.value.start_date &&
        pricingForm.value.end_date &&
        new Date(pricingForm.value.end_date) > new Date(pricingForm.value.start_date)
    );
});

// Methods
const resetForm = () => {
    pricingForm.value = {
        name: '',
        nightly_rate: 0,
        weekly_discount_percent: 0,
        monthly_discount_percent: 0,
        weekend_premium_percent: 0,
        weekly_discount_active: false,
        monthly_discount_active: false,
        weekend_premium_active: false,
        currency: 'IDR',
        start_date: '',
        end_date: '',
        min_days_for_weekly: 7,
        min_days_for_monthly: 30,
    };
    errors.value = {};
};

const openPricingForm = () => {
    resetForm();
    editingPricing.value = null;
    
    // Set default dates (next month for 30 days)
    const today = new Date();
    const endDate = new Date(today);
    endDate.setDate(endDate.getDate() + 180); // 180 days from today
    
    pricingForm.value.start_date = today.toISOString().split('T')[0];
    pricingForm.value.end_date = endDate.toISOString().split('T')[0];
    
    showPricingForm.value = true;
};

const editPricing = (pricing: PropertyPricing) => {
    editingPricing.value = pricing;
    pricingForm.value = {
        name: pricing.name || '',
        nightly_rate: pricing.nightly_rate || 0,
        weekly_discount_percent: pricing.weekly_discount_percent || 0,
        monthly_discount_percent: pricing.monthly_discount_percent || 0,
        weekend_premium_percent: pricing.weekend_premium_percent || 0,
        weekly_discount_active: Boolean(pricing.weekly_discount_active) || false,
        monthly_discount_active: Boolean(pricing.monthly_discount_active) || false,
        weekend_premium_active: Boolean(pricing.weekend_premium_active) || false,
        currency: pricing.currency || 'IDR',
        start_date: pricing.start_date ? formatDateForInput(pricing.start_date) : '',
        end_date: pricing.end_date ? formatDateForInput(pricing.end_date) : '',
        min_days_for_weekly: pricing.min_days_for_weekly || 7,
        min_days_for_monthly: pricing.min_days_for_monthly || 30,
    };
    console.log('Editing pricing:', pricingForm.value);
    showPricingForm.value = true;
};

const closePricingForm = () => {
    showPricingForm.value = false;
    editingPricing.value = null;
    resetForm();
    processing.value = false;
};

const submitPricing = async () => {
    if (!isFormValid.value) return;

    processing.value = true;
    errors.value = {};

    const onSuccess = () => {
        console.log('✅ Pricing saved successfully');
        closePricingForm();
        // No need to reload, Inertia will handle the update
    };

    const onError = (errorData: any) => {
        console.log('❌ Error:', errorData);
        
        if (errorData.errors) {
            errors.value = errorData.errors;
            console.log('❌ Validation errors set:', errors.value);
        } else if (errorData.message) {
            alert(`Error: ${errorData.message}`);
        } else {
            alert('An error occurred while saving the pricing.');
        }
    };

    const onFinish = () => {
        processing.value = false;
    };

    try {
        if (isEditing.value && editingPricing.value) {
            router.put(route('admin.pricing.update', editingPricing.value.id), pricingForm.value, {
                onSuccess,
                onError,
                onFinish
            });
        } else {
            router.post(route('admin.properties.pricing.store', property.slug), pricingForm.value, {
                onSuccess,
                onError,
                onFinish
            });
        }
    } catch (error) {
        console.error('❌ Unexpected error:', error);
        alert('An unexpected error occurred. Please try again.');
        processing.value = false;
    }
};

const deletePricing = async (pricing: PropertyPricing) => {
    if (!confirm(`Are you sure you want to delete the pricing period "${pricing.name || 'Unnamed'}"?`)) {
        return;
    }

    deleting.value = pricing.id;

    try {
        router.delete(route('admin.pricing.destroy', pricing.id), {
            onSuccess: () => {
                console.log('✅ Pricing deleted successfully');
                // No need to reload, Inertia will handle the update
            },
            onError: (errors: any) => {
                console.error('❌ Failed to delete pricing:', errors);
                alert('Failed to delete pricing. Please try again.');
            },
            onFinish: () => {
                deleting.value = null;
            }
        });
    } catch (error: any) {
        console.error('❌ Unexpected error deleting pricing:', error);
        alert('An unexpected error occurred while deleting.');
        deleting.value = null;
    }
};

const getPricingStatusClass = (pricing: PropertyPricing) => {
    const today = new Date().toISOString().split('T')[0];
    // Ensure start_date and end_date are defined
    if (pricing.start_date != null && pricing.end_date != null) {
        // Determine status class
        if (pricing.start_date <= today && pricing.end_date >= today) {
            return 'bg-green-100 text-green-800 border-green-200';
        } else if (pricing.start_date > today) {
            return 'bg-blue-100 text-blue-800 border-blue-200';
        } else {
            return 'bg-gray-100 text-gray-800 border-gray-200';
        }

    }
};

const getPricingStatus = (pricing: PropertyPricing) => {
    const today = new Date().toISOString().split('T')[0];
    // Ensure start_date and end_date are defined
    if (pricing.start_date != null && pricing.end_date != null) {
        // Determine status text
        if (pricing.start_date <= today && pricing.end_date >= today) {
            return 'Active';
        } else if (pricing.start_date > today) {
            return 'Upcoming';
        } else {
            return 'Expired';
        }
    }
};

// Helper functions for calculated rates (for display purposes)
const calculateWeeklyRate = (nightlyRate: number, discountPercent: number) => {
    const weeklyBase = nightlyRate * 7;
    return weeklyBase * (1 - discountPercent / 100);
};

const calculateMonthlyRate = (nightlyRate: number, discountPercent: number) => {
    const monthlyBase = nightlyRate * 30;
    return monthlyBase * (1 - discountPercent / 100);
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    Property Pricing
                    <span class="text-sm font-normal text-gray-500 ml-2">({{ pricing.length }} periods)</span>
                </h2>
                <button
                    @click="openPricingForm"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center space-x-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <span>Add Pricing Period</span>
                </button>
            </div>
        </div>

        <!-- Current Active Pricing Highlight -->
        <div v-if="currentActivePricing" class="px-6 py-4 bg-green-50 dark:bg-green-900/20 border-b border-gray-200 dark:border-gray-600">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-green-800 dark:text-green-200">Currently Active Pricing</h3>
                    <p class="text-sm text-green-600 dark:text-green-300">
                        {{ currentActivePricing.name || 'Unnamed Period' }} - 
                        {{ formatPrice(currentActivePricing.nightly_rate ?? 0) }}/night
                    </p>
                </div>
                <button
                    @click="editPricing(currentActivePricing)"
                    class="text-sm text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300"
                >
                    Edit Current
                </button>
            </div>
        </div>

        <div class="p-6">
            <!-- Pricing Periods List -->
            <!-- Tabs -->
        <div class="border-b border-gray-200 dark:border-gray-600 mb-6">
            <nav class="-mb-px flex space-x-8">
                <button
                    @click="activeTab = 'all'"
                    :class="[
                        activeTab === 'all' 
                            ? 'border-blue-500 text-blue-600 dark:text-blue-400' 
                            : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300',
                        'py-2 px-1 border-b-2 font-medium text-sm transition-colors'
                    ]"
                >
                    All ({{ sortedPricing.length }})
                </button>
                <button
                    v-if="currentActivePricing"
                    @click="activeTab = 'current'"
                    :class="[
                        activeTab === 'current' 
                            ? 'border-green-500 text-green-600 dark:text-green-400' 
                            : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300',
                        'py-2 px-1 border-b-2 font-medium text-sm transition-colors'
                    ]"
                >
                    <div class="flex items-center space-x-1">
                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                        <span>Active (1)</span>
                    </div>
                </button>
                <button
                    v-if="upcomingPricing.length > 0"
                    @click="activeTab = 'upcoming'"
                    :class="[
                        activeTab === 'upcoming' 
                            ? 'border-blue-500 text-blue-600 dark:text-blue-400' 
                            : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300',
                        'py-2 px-1 border-b-2 font-medium text-sm transition-colors'
                    ]"
                >
                    Upcoming ({{ upcomingPricing.length }})
                </button>
                <button
                    v-if="pastPricing.length > 0"
                    @click="activeTab = 'past'"
                    :class="[
                        activeTab === 'past' 
                            ? 'border-gray-500 text-gray-600 dark:text-gray-400' 
                            : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300',
                        'py-2 px-1 border-b-2 font-medium text-sm transition-colors'
                    ]"
                >
                    Past ({{ pastPricing.length }})
                </button>
            </nav>
        </div>

        <!-- Pricing Periods List (use filteredPricing instead of sortedPricing) -->
        <div v-if="filteredPricing.length > 0" class="space-y-4">
            <div
                v-for="pricing in filteredPricing"
                :key="pricing.id"
                class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg hover:shadow-sm transition-shadow"
                :class="{ 'opacity-60': activeTab === 'past' }"
            >
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3 mb-3">
                                <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ pricing.name || 'Unnamed Pricing Period' }}
                                </h4>
                                <span :class="getPricingStatusClass(pricing)" class="px-2 py-1 rounded-full text-xs font-medium border">
                                    {{ getPricingStatus(pricing) }}
                                </span>
                            </div>
                            
                            <!-- Date Range -->
                            <div class="flex items-center space-x-2 mb-3 text-sm text-gray-600 dark:text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>
                                    {{
                                        pricing.start_date && pricing.end_date
                                            ? formatDate(new Date(String(pricing.start_date))) + ' - ' + formatDate(new Date(String(pricing.end_date)))
                                            : 'Dates not set'
                                    }}
                                </span>
                            </div>

                            <!-- Pricing Details -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div class="space-y-1">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Nightly Rate</p>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ formatPrice(pricing.nightly_rate ?? 0) }}
                                    </p>
                                </div>
                                <div v-if="pricing.weekly_discount_active && (pricing.weekly_discount_percent ?? 0) > 0" class="space-y-1">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Weekly Rate</p>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ formatPrice(calculateWeeklyRate(pricing.nightly_rate ?? 0, pricing.weekly_discount_percent ?? 0)) }}
                                    </p>
                                    <p class="text-xs text-green-600">
                                        {{ pricing.weekly_discount_percent }}% discount
                                    </p>
                                </div>
                                <div v-if="pricing.monthly_discount_active && (pricing.monthly_discount_percent ?? 0) > 0" class="space-y-1">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Monthly Rate</p>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ formatPrice(calculateMonthlyRate(pricing.nightly_rate ?? 0, pricing.monthly_discount_percent ?? 0)) }}
                                    </p>
                                    <p class="text-xs text-green-600">
                                        {{ pricing.monthly_discount_percent }}% discount
                                    </p>
                                </div>
                                <div v-if="pricing.weekend_premium_active && (pricing.weekend_premium_percent ?? 0) > 0" class="space-y-1">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Weekend Premium</p>
                                    <p class="text-sm font-semibold text-orange-600 dark:text-orange-400">
                                        +{{ pricing.weekend_premium_percent }}%
                                    </p>
                                    <p class="text-xs text-orange-500">
                                        {{ formatPrice((pricing.nightly_rate ?? 0) * (1 + (pricing.weekend_premium_percent ?? 0) / 100)) }}/night
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center space-x-2 ml-4">
                            <button
                                @click="editPricing(pricing)"
                                class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                title="Edit pricing"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <button
                                @click="deletePricing(pricing)"
                                :disabled="deleting === pricing.id"
                                class="p-2 text-gray-400 hover:text-red-600 disabled:opacity-50"
                                title="Delete pricing"
                            >
                                <svg v-if="deleting === pricing.id" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- No Pricing State -->
            <!-- Empty state for filtered views -->
        <div v-else-if="activeTab !== 'all'" class="text-center py-8">
            <div class="text-gray-400 mb-2">
                <svg class="mx-auto h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ 
                    activeTab === 'current' ? 'No active pricing period' :
                    activeTab === 'upcoming' ? 'No upcoming pricing periods' :
                    activeTab === 'past' ? 'No past pricing periods' :
                    'No pricing periods'
                }}
            </p>
        </div>
        </div>

        <!-- Pricing Form Modal -->
        <Teleport to="body">
            <div
                v-if="showPricingForm"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
                @click="closePricingForm"
            >
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg max-w-2xl w-full max-h-full overflow-y-auto"
                    @click.stop
                >
                    <!-- Modal Header -->
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ isEditing ? 'Edit Pricing Period' : 'Add New Pricing Period' }}
                        </h3>
                        <button @click="closePricingForm" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Content -->
                    <form @submit.prevent="submitPricing" class="p-6 space-y-6">
                        <!-- Basic Information -->
                        <div>
                            <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">Basic Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Period Name
                                    </label>
                                    <input
                                        v-model="pricingForm.name"
                                        type="text"
                                        placeholder="e.g., Peak Season, Holiday Rates"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        :class="{ 'border-red-500': errors.name }"
                                    />
                                    <div v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Currency
                                    </label>
                                    <select
                                        v-model="pricingForm.currency"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="IDR">IDR</option>
                                        <option value="USD">USD</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Date Range -->
                        <div>
                            <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">Date Range</h4>
                            <div v-if="errors.dates" class="mt-1 text-sm text-red-600">{{ errors.dates }}</div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Start Date <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="pricingForm.start_date"
                                        type="date"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        :class="{ 'border-red-500': errors.start_date }"
                                    />
                                    <div v-if="errors.start_date" class="mt-1 text-sm text-red-600">{{ errors.start_date }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        End Date <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="pricingForm.end_date"
                                        type="date"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        :class="{ 'border-red-500': errors.end_date }"
                                    />
                                    <div v-if="errors.end_date" class="mt-1 text-sm text-red-600">{{ errors.end_date }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Pricing -->
                        <div>
                            <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">Base Rate</h4>
                            <div class="space-y-4">
                                <!-- Nightly Rate -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Nightly Rate <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model.number="pricingForm.nightly_rate"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        :class="{ 'border-red-500': errors.nightly_rate }"
                                    />
                                    <div v-if="errors.nightly_rate" class="mt-1 text-sm text-red-600">{{ errors.nightly_rate }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Discounts & Premiums -->
                        <div>
                            <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">Discounts & Premiums</h4>
                            <div class="space-y-6">
                                <!-- Weekly Discount -->
                                <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Weekly Stay Discount
                                        </label>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input v-model="pricingForm.weekly_discount_active" type="checkbox" class="sr-only peer">
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                        </label>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">
                                                Discount Percentage
                                            </label>
                                            <input
                                                v-model.number="pricingForm.weekly_discount_percent"
                                                type="number"
                                                min="0"
                                                max="100"
                                                step="0.01"
                                                :disabled="!pricingForm.weekly_discount_active"
                                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                                            />
                                        </div>
                                        <div v-if="pricingForm.weekly_discount_active && pricingForm.weekly_discount_percent > 0 && pricingForm.nightly_rate > 0">
                                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">
                                                Effective Weekly Rate
                                            </label>
                                            <div class="px-3 py-2 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md text-green-700 dark:text-green-300 font-medium">
                                                {{ formatPrice(calculateWeeklyRate(pricingForm.nightly_rate, pricingForm.weekly_discount_percent)) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Monthly Discount -->
                                <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Monthly Stay Discount
                                        </label>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input v-model="pricingForm.monthly_discount_active" type="checkbox" class="sr-only peer">
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                        </label>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">
                                                Discount Percentage
                                            </label>
                                            <input
                                                v-model.number="pricingForm.monthly_discount_percent"
                                                type="number"
                                                min="0"
                                                max="100"
                                                step="0.01"
                                                :disabled="!pricingForm.monthly_discount_active"
                                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                                            />
                                        </div>
                                        <div v-if="pricingForm.monthly_discount_active && pricingForm.monthly_discount_percent > 0 && pricingForm.nightly_rate > 0">
                                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">
                                                Effective Monthly Rate
                                            </label>
                                            <div class="px-3 py-2 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md text-green-700 dark:text-green-300 font-medium">
                                                {{ formatPrice(calculateMonthlyRate(pricingForm.nightly_rate, pricingForm.monthly_discount_percent)) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Weekend Premium -->
                                <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Weekend Premium
                                        </label>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input v-model="pricingForm.weekend_premium_active" type="checkbox" class="sr-only peer">
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                        </label>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">
                                                Premium Percentage
                                            </label>
                                            <input
                                                v-model.number="pricingForm.weekend_premium_percent"
                                                type="number"
                                                min="0"
                                                max="100"
                                                step="0.01"
                                                :disabled="!pricingForm.weekend_premium_active"
                                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                                            />
                                        </div>
                                        <div v-if="pricingForm.weekend_premium_active && pricingForm.weekend_premium_percent > 0 && pricingForm.nightly_rate > 0">
                                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">
                                                Weekend Rate
                                            </label>
                                            <div class="px-3 py-2 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-md text-orange-700 dark:text-orange-300 font-medium">
                                                {{ formatPrice(pricingForm.nightly_rate * (1 + pricingForm.weekend_premium_percent / 100)) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Minimum Stay Requirements -->
                        <div>
                            <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">Minimum Stay Requirements</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Min Days for Weekly Rate
                                    </label>
                                    <input
                                        v-model.number="pricingForm.min_days_for_weekly"
                                        type="number"
                                        min="1"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Min Days for Monthly Rate
                                    </label>
                                    <input
                                        v-model.number="pricingForm.min_days_for_monthly"
                                        type="number"
                                        min="1"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                            <button
                                type="button"
                                @click="closePricingForm"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="processing || !isFormValid"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-2"
                            >
                                <svg v-if="processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>{{ processing ? (isEditing ? 'Updating...' : 'Creating...') : (isEditing ? 'Update Pricing' : 'Create Pricing') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </div>
</template>