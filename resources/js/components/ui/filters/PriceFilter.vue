<script setup lang="ts">
import { computed } from 'vue';

// Define the structure of the price filter data
interface PriceFilterData {
    minPrice: string;
    maxPrice: string;
}

// Props interface
interface Props {
    modelValue: PriceFilterData;
}

// Emits interface for v-model
interface Emits {
    (e: 'update:modelValue', value: PriceFilterData): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

// Computed property to get price options based on selected rate
const priceOptions = computed(() => {
        return {
            min: [200000, 500000, 1000000, 2500000, 5000000, 10000000, 15000000, 20000000],
            max: [500000, 1000000, 2500000, 5000000, 10000000, 15000000, 20000000, 25000000]
        };
});

// Helper function to update the model value
const updateValue = (key: keyof PriceFilterData, value: any) => {
    emit('update:modelValue', {
        ...props.modelValue,
        [key]: value
    });
};

// Handle min price change
const handleMinPriceChange = (price: string) => {
    updateValue('minPrice', price);
};

// Handle max price change
const handleMaxPriceChange = (price: string) => {
    updateValue('maxPrice', price);
};
</script>

<template>
    <div>        
        <label class="block text-sm font-medium mb-2">Price Range (Per Day)</label>
        <div class="grid grid-cols-2 gap-2">
            <select
                :value="modelValue.minPrice"
                @change="handleMinPriceChange(($event.target as HTMLSelectElement).value)"
                class="form-select rounded-md py-1 px-2 border border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100"
            >
                <option value="">Min price</option>
                <option 
                    v-for="price in priceOptions.min" 
                    :key="`min-${price}`" 
                    :value="price"
                >
                    Rp {{ price.toLocaleString('id-ID') }}
                </option>
            </select>
            
            <select
                :value="modelValue.maxPrice"
                @change="handleMaxPriceChange(($event.target as HTMLSelectElement).value)"
                class="form-select rounded-md py-1 px-2 border border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100"
            >
                <option value="">Max price</option>
                <option 
                    v-for="price in priceOptions.max" 
                    :key="`max-${price}`" 
                    :value="price"
                >
                    Rp {{ price.toLocaleString('id-ID') }}
                </option>
            </select>
        </div>
    </div>
</template>