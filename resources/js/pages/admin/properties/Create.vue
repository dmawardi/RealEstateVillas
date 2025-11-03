<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, computed } from 'vue';
import BasicInformation from '@/components/properties/admin/forms/BasicInformation.vue';
import Location from '@/components/properties/admin/forms/Location.vue';
import Specifications from '@/components/properties/admin/forms/Specifications.vue';
import Pricing from '@/components/properties/admin/forms/Pricing.vue';
import Agent from '@/components/properties/admin/forms/Agent.vue';

interface Props {
    propertyTypes: Record<string, string>;
    listingTypes: Record<string, string>;
    priceTypes: Record<string, string>;
    statusOptions: Record<string, string>;
}

const { propertyTypes, listingTypes, priceTypes, statusOptions } = defineProps<Props>();

// Form setup with empty initial values
const form = useForm({
    // Basic Information
    title: '',
    slug: '',
    description: '',
    property_type: 'house',
    listing_type: 'for_rent',
    status: 'active',
    is_featured: false,
    is_premium: false,
    
    // Location
    street_number: null as string | null,
    street_name: '',
    village: null as string | null,
    district: '',
    regency: '',
    state: 'Bali',
    postcode: '',
    country: 'Indonesia',
    latitude: null as number | null,
    longitude: null as number | null,
    
    // Specifications
    bedrooms: null as number | null,
    bathrooms: null as number | null,
    car_spaces: null as number | null,
    land_size: null as number | null,
    floor_area: null as number | null,
    year_built: null as number | null,
    zoning: null as string | null,
    amenities: {
        schools_nearby: [] as string[],
        transport: [] as string[],
        shopping: [] as string[],
        parks: [] as string[],
        medical: [] as string[]
    },
    
    // Pricing
    price: null as number | null,
    price_type: 'fixed',
    nightly_rate: null as number | null,
    weekly_rate: null as number | null,
    monthly_rate: null as number | null,
    available_date: null as string | null,
    inspection_times: null as string | null,
    
    // Agent
    agent_name: '',
    agent_phone: null as string | null,
    agent_email: null as string | null,
    agency_name: null as string | null,
});

// Create computed properties for component v-models
const basicInformation = computed({
    get: () => ({
        title: form.title,
        slug: form.slug,
        description: form.description,
        property_type: form.property_type,
        listing_type: form.listing_type,
        status: form.status,
        is_featured: form.is_featured,
        is_premium: form.is_premium,
    }),
    set: (value) => {
        form.title = value.title;
        form.slug = value.slug;
        form.description = value.description;
        form.property_type = value.property_type;
        form.listing_type = value.listing_type;
        form.status = value.status;
        form.is_featured = value.is_featured;
        form.is_premium = value.is_premium;
    }
});

const location = computed({
    get: () => ({
        street_number: form.street_number,
        street_name: form.street_name,
        village: form.village,
        district: form.district,
        regency: form.regency,
        state: form.state,
        postcode: form.postcode,
        country: form.country,
        latitude: form.latitude,
        longitude: form.longitude,
    }),
    set: (value) => {
        form.street_number = value.street_number;
        form.street_name = value.street_name;
        form.village = value.village;
        form.district = value.district;
        form.regency = value.regency;
        form.state = value.state;
        form.postcode = value.postcode;
        form.country = value.country;
        form.latitude = value.latitude;
        form.longitude = value.longitude;
    }
});

const specifications = computed({
    get: () => ({
        bedrooms: form.bedrooms,
        bathrooms: form.bathrooms,
        car_spaces: form.car_spaces,
        land_size: form.land_size,
        floor_area: form.floor_area,
        year_built: form.year_built,
        zoning: form.zoning,
        amenities: form.amenities,
    }),
    set: (value) => {
        form.bedrooms = value.bedrooms;
        form.bathrooms = value.bathrooms;
        form.car_spaces = value.car_spaces;
        form.land_size = value.land_size;
        form.floor_area = value.floor_area;
        form.year_built = value.year_built;
        form.zoning = value.zoning;
        form.amenities = value.amenities;
    }
});

const pricing = computed({
    get: () => ({
        price: form.price,
        price_type: form.price_type,
        nightly_rate: form.nightly_rate,
        weekly_rate: form.weekly_rate,
        monthly_rate: form.monthly_rate,
        available_date: form.available_date,
        inspection_times: form.inspection_times,
    }),
    set: (value) => {
        form.price = value.price;
        form.price_type = value.price_type;
        form.nightly_rate = value.nightly_rate;
        form.weekly_rate = value.weekly_rate;
        form.monthly_rate = value.monthly_rate;
        form.available_date = value.available_date;
        form.inspection_times = value.inspection_times;
    }
});

const agent = computed({
    get: () => ({
        agent_name: form.agent_name,
        agent_phone: form.agent_phone,
        agent_email: form.agent_email,
        agency_name: form.agency_name,
    }),
    set: (value) => {
        form.agent_name = value.agent_name;
        form.agent_phone = value.agent_phone;
        form.agent_email = value.agent_email;
        form.agency_name = value.agency_name;
    }
});

// Form sections state
const activeTab = ref('basic');
const tabs = [
    { id: 'basic', name: 'Basic Info', icon: '🏠' },
    { id: 'location', name: 'Location', icon: '📍' },
    { id: 'specifications', name: 'Specifications', icon: '📐' },
    { id: 'pricing', name: 'Pricing', icon: '💰' },
    { id: 'agent', name: 'Agent Info', icon: '👤' },
];

// Navigation helper
const canNavigateToNext = computed(() => {
    switch (activeTab.value) {
        case 'basic':
            return form.title && form.description && form.property_type && form.listing_type;
        case 'location':
            return form.street_name && form.district && form.regency && form.postcode;
        case 'specifications':
            return true; // Specifications are optional
        case 'pricing':
            return form.price_type; // Basic pricing validation
        case 'agent':
            return form.agent_name;
        default:
            return true;
    }
});

const nextTab = () => {
    const currentIndex = tabs.findIndex(tab => tab.id === activeTab.value);
    if (currentIndex < tabs.length - 1 && canNavigateToNext.value) {
        activeTab.value = tabs[currentIndex + 1].id;
    }
};

const prevTab = () => {
    const currentIndex = tabs.findIndex(tab => tab.id === activeTab.value);
    if (currentIndex > 0) {
        activeTab.value = tabs[currentIndex - 1].id;
    }
};

// Submit function
const submit = () => {
    console.log("Form data being sent:", form.data());

    form.post(route('admin.properties.store'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            console.log('Property created successfully');
        },
        onError: (errors) => {
            console.error('Validation errors:', errors);
        },
        onFinish: () => {
            console.log('Request completed');
        }
    });
};

// Form completion progress
const completionProgress = computed(() => {
    let completed = 0;
    const total = 5;
    
    // Basic info
    if (form.title && form.description && form.property_type && form.listing_type) {
        completed++;
    }
    
    // Location
    if (form.street_name && form.district && form.regency && form.postcode) {
        completed++;
    }
    
    // Specifications (always count as complete since optional)
    completed++;
    
    // Pricing
    if (form.price_type) {
        completed++;
    }
    
    // Agent
    if (form.agent_name) {
        completed++;
    }
    
    return Math.round((completed / total) * 100);
});
</script>

<template>
    <Head title="Create Property" />

    <AppLayout>
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div>
                        <nav class="flex" aria-label="Breadcrumb">
                            <ol class="flex items-center space-x-4">
                                <li>
                                    <Link :href="route('admin.properties.index')" class="text-gray-400 hover:text-gray-500">
                                        Properties
                                    </Link>
                                </li>
                                <li>
                                    <div class="flex items-center">
                                        <svg class="flex-shrink-0 h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="ml-4 text-sm font-medium text-gray-500">Create New</span>
                                    </div>
                                </li>
                            </ol>
                        </nav>
                        <h1 class="mt-2 text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate">
                            Create New Property
                        </h1>
                        <p class="mt-1 text-sm text-gray-500">
                            Add a new property to your portfolio
                        </p>
                    </div>
                    
                    <!-- Progress & Action Buttons -->
                    <div class="flex items-center space-x-4">
                        <!-- Progress Indicator -->
                        <div class="flex items-center space-x-2">
                            <div class="flex items-center">
                                <div class="w-32 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div 
                                        class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                                        :style="{ width: `${completionProgress}%` }"
                                    ></div>
                                </div>
                                <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">
                                    {{ completionProgress }}%
                                </span>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="flex space-x-3">
                            <Link 
                                :href="route('admin.properties.index')"
                                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors duration-200"
                            >
                                Cancel
                            </Link>
                            <button
                                @click="submit"
                                :disabled="form.processing || completionProgress < 80"
                                :class="[
                                    'px-4 py-2 rounded-lg transition-colors duration-200',
                                    form.processing || completionProgress < 80
                                        ? 'bg-gray-400 text-gray-200 cursor-not-allowed'
                                        : 'bg-blue-600 hover:bg-blue-700 text-white'
                                ]"
                            >
                                {{ form.processing ? 'Creating...' : 'Create Property' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <!-- Navigation Sidebar -->
                <div class="lg:col-span-1">
                    <nav class="space-y-1 sticky top-8">
                        <button
                            v-for="(tab, index) in tabs"
                            :key="tab.id"
                            @click="activeTab = tab.id"
                            :class="[
                                'w-full text-left px-4 py-3 rounded-lg transition-colors flex items-center justify-between group',
                                activeTab === tab.id 
                                    ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300 border border-blue-200 dark:border-blue-700' 
                                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                            ]"
                        >
                            <div class="flex items-center space-x-3">
                                <span class="text-lg">{{ tab.icon }}</span>
                                <span class="font-medium">{{ tab.name }}</span>
                            </div>
                            
                            <!-- Completion Indicator -->
                            <div v-if="index === 0 && form.title && form.description" class="w-2 h-2 bg-green-500 rounded-full"></div>
                            <div v-else-if="index === 1 && form.street_name && form.district" class="w-2 h-2 bg-green-500 rounded-full"></div>
                            <div v-else-if="index === 2" class="w-2 h-2 bg-green-500 rounded-full"></div>
                            <div v-else-if="index === 3 && form.price_type" class="w-2 h-2 bg-green-500 rounded-full"></div>
                            <div v-else-if="index === 4 && form.agent_name" class="w-2 h-2 bg-green-500 rounded-full"></div>
                        </button>
                    </nav>
                </div>

                <!-- Form Content -->
                <div class="lg:col-span-3">
                    <form @submit.prevent="submit" class="space-y-8">
                        <!-- Basic Information Tab -->
                        <div v-show="activeTab === 'basic'">
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                                <div class="flex justify-between items-center mb-6">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Basic Information</h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Essential details about your property</p>
                                    </div>
                                </div>
                                
                                <BasicInformation
                                    v-model="basicInformation"
                                    :property-types="propertyTypes"
                                    :listing-types="listingTypes"
                                    :status-options="statusOptions"
                                    :errors="form.errors"
                                />
                                
                                <!-- Navigation -->
                                <div class="flex justify-end mt-6">
                                    <button
                                        type="button"
                                        @click="nextTab"
                                        :disabled="!canNavigateToNext"
                                        :class="[
                                            'px-4 py-2 rounded-lg transition-colors',
                                            canNavigateToNext 
                                                ? 'bg-blue-600 hover:bg-blue-700 text-white' 
                                                : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                                        ]"
                                    >
                                        Next Step →
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Location Tab -->
                        <div v-show="activeTab === 'location'">
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                                <div class="flex justify-between items-center mb-6">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Location Details</h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Where is your property located?</p>
                                    </div>
                                </div>
                                
                                <Location 
                                    v-model="location" 
                                    :errors="form.errors" 
                                />
                                
                                <!-- Navigation -->
                                <div class="flex justify-between mt-6">
                                    <button
                                        type="button"
                                        @click="prevTab"
                                        class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors"
                                    >
                                        ← Previous
                                    </button>
                                    <button
                                        type="button"
                                        @click="nextTab"
                                        :disabled="!canNavigateToNext"
                                        :class="[
                                            'px-4 py-2 rounded-lg transition-colors',
                                            canNavigateToNext 
                                                ? 'bg-blue-600 hover:bg-blue-700 text-white' 
                                                : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                                        ]"
                                    >
                                        Next Step →
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Specifications Tab -->
                        <div v-show="activeTab === 'specifications'">
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                                <div class="flex justify-between items-center mb-6">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Property Specifications</h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Physical characteristics and features (optional)</p>
                                    </div>
                                </div>
                                
                                <Specifications
                                    v-model="specifications"
                                    :errors="form.errors"
                                />
                                
                                <!-- Navigation -->
                                <div class="flex justify-between mt-6">
                                    <button
                                        type="button"
                                        @click="prevTab"
                                        class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors"
                                    >
                                        ← Previous
                                    </button>
                                    <button
                                        type="button"
                                        @click="nextTab"
                                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors"
                                    >
                                        Next Step →
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Pricing Tab -->
                        <div v-show="activeTab === 'pricing'">
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                                <div class="flex justify-between items-center mb-6">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Pricing Information</h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Set your property prices and availability</p>
                                    </div>
                                </div>
                                
                                <Pricing
                                    v-model="pricing"
                                    :price-types="priceTypes"
                                    :listing-type="form.listing_type"
                                    :errors="form.errors"
                                />
                                
                                <!-- Navigation -->
                                <div class="flex justify-between mt-6">
                                    <button
                                        type="button"
                                        @click="prevTab"
                                        class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors"
                                    >
                                        ← Previous
                                    </button>
                                    <button
                                        type="button"
                                        @click="nextTab"
                                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors"
                                    >
                                        Next Step →
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Agent Information Tab -->
                        <div v-show="activeTab === 'agent'">
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                                <div class="flex justify-between items-center mb-6">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Agent Information</h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Contact details for this property</p>
                                    </div>
                                </div>
                                
                                <Agent
                                    v-model="agent"
                                    :errors="form.errors"
                                />
                                
                                <!-- Final Navigation -->
                                <div class="flex justify-between items-center mt-6">
                                    <button
                                        type="button"
                                        @click="prevTab"
                                        class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors"
                                    >
                                        ← Previous
                                    </button>
                                    
                                    <div class="flex items-center space-x-4">
                                        <div class="text-sm text-gray-500">
                                            Ready to create property
                                        </div>
                                        <button
                                            type="submit"
                                            :disabled="form.processing || completionProgress < 80"
                                            :class="[
                                                'px-6 py-2 rounded-lg transition-colors font-medium',
                                                form.processing || completionProgress < 80
                                                    ? 'bg-gray-400 text-gray-200 cursor-not-allowed'
                                                    : 'bg-green-600 hover:bg-green-700 text-white'
                                            ]"
                                        >
                                            {{ form.processing ? 'Creating Property...' : 'Create Property' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>