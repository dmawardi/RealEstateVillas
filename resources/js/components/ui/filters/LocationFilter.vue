<script setup lang="ts">
import { computed, watch } from 'vue';

// Define the structure of the location filter data
interface LocationFilterData {
    district: string;
    regency: string;
}

// Props interface
interface Props {
    modelValue: LocationFilterData;
}

// Emits interface for v-model
interface Emits {
    (e: 'update:modelValue', value: LocationFilterData): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

// Location mapping - district to regency relationships
const locationData = {
    regencies: [
        { value: 'Badung', label: 'Badung' },
        { value: 'Denpasar', label: 'Denpasar' },
        { value: 'Gianyar', label: 'Gianyar' },
        { value: 'Tabanan', label: 'Tabanan' },
        { value: 'Klungkung', label: 'Klungkung' },
    ],
    districts: {
        'Badung': [
            { value: 'Kuta', label: 'Kuta' },
            { value: 'Canggu', label: 'Canggu' },
            { value: 'Seminyak', label: 'Seminyak' },
            { value: 'Jimbaran', label: 'Jimbaran' },
            { value: 'Nusa Dua', label: 'Nusa Dua' },
        ],
        'Denpasar': [
            { value: 'Denpasar Selatan', label: 'Denpasar Selatan' },
            { value: 'Sanur', label: 'Sanur' },
        ],
        'Gianyar': [
            { value: 'Ubud', label: 'Ubud' },
        ],
        'Tabanan': [],
        'Klungkung': [],
    } as Record<string, Array<{value: string, label: string}>>
};

// Computed available districts based on selected regency
const availableDistricts = computed(() => {
    if (!props.modelValue.regency) {
        // If no regency selected, show all districts
        return Object.values(locationData.districts).flat();
    }
    return locationData.districts[props.modelValue.regency] || [];
});

// Function to get regency for a district
const getRegencyForDistrict = (district: string) => {
    for (const [regency, districts] of Object.entries(locationData.districts)) {
        if (districts.some(d => d.value === district)) {
            return regency;
        }
    }
    return '';
};

// Helper function to update the model value
const updateValue = (key: keyof LocationFilterData, value: string) => {
    emit('update:modelValue', {
        ...props.modelValue,
        [key]: value
    });
};

// Handle district change - auto-assign regency
const handleDistrictChange = (district: string) => {
    if (district) {
        const regency = getRegencyForDistrict(district);
        emit('update:modelValue', {
            district,
            regency: regency || props.modelValue.regency
        });
    } else {
        updateValue('district', district);
    }
};

// Handle regency change - clear invalid district
const handleRegencyChange = (regency: string) => {
    let newDistrict = props.modelValue.district;
    
    if (regency && newDistrict) {
        // Check if current district is valid for the new regency
        const validDistricts = locationData.districts[regency] || [];
        const isDistrictValid = validDistricts.some(d => d.value === newDistrict);
        if (!isDistrictValid) {
            newDistrict = '';
        }
    }
    
    emit('update:modelValue', {
        district: newDistrict,
        regency
    });
};
</script>

<template>
    <div>
        <label class="block text-sm font-medium mb-2">Location</label>
        <div class="grid grid-cols-2 gap-2">
            <label class="block text-sm font-medium mb-2">Regency</label>
            <select 
                :value="modelValue.regency"
                @change="handleRegencyChange(($event.target as HTMLSelectElement).value)"
                class="px-3 py-2 border border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <option value="">Any Regency</option>
                <option 
                    v-for="regency in locationData.regencies" 
                    :key="regency.value" 
                    :value="regency.value"
                >
                    {{ regency.label }}
                </option>
            </select>
            <label class="block text-sm font-medium mb-2">District</label>
            <select 
                :value="modelValue.district"
                @change="handleDistrictChange(($event.target as HTMLSelectElement).value)"
                class="px-3 py-2 border border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <option value="">Any District</option>
                <option 
                    v-for="district in availableDistricts" 
                    :key="district.value" 
                    :value="district.value"
                >
                    {{ district.label }}
                </option>
            </select>
        </div>
    </div>
</template>