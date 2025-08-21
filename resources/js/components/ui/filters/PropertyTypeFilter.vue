<script setup lang="ts">
import { computed } from 'vue';

// Props interface
interface Props {
    modelValue: string[];
}

// Emits interface for v-model
interface Emits {
    (e: 'update:modelValue', value: string[]): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

// Two-way binding computed property for v-model functionality
const selectedTypes = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
});
</script>

<template>
    <div>
        <label class="block text-sm font-medium mb-2">Property Type</label>
        <div class="grid grid-cols-2 gap-2">
            <label class="inline-flex items-center">
                <input 
                    type="checkbox" 
                    value="house" 
                    v-model="selectedTypes" 
                    class="form-checkbox text-blue-600" 
                />
                <span class="ml-2">House</span>
            </label>
            <label class="inline-flex items-center">
                <input 
                    type="checkbox" 
                    value="apartment" 
                    v-model="selectedTypes" 
                    class="form-checkbox text-blue-600" 
                />
                <span class="ml-2">Apartment</span>
            </label>
            <label class="inline-flex items-center">
                <input 
                    type="checkbox" 
                    value="townhouse" 
                    v-model="selectedTypes" 
                    class="form-checkbox text-blue-600" 
                />
                <span class="ml-2">Townhouse</span>
            </label>
            <label class="inline-flex items-center">
                <input 
                    type="checkbox" 
                    value="villa" 
                    v-model="selectedTypes" 
                    class="form-checkbox text-blue-600" 
                />
                <span class="ml-2">Villa</span>
            </label>
            <label class="inline-flex items-center">
                <input 
                    type="checkbox" 
                    value="land" 
                    v-model="selectedTypes" 
                    class="form-checkbox text-blue-600" 
                />
                <span class="ml-2">Land</span>
            </label>
            <label class="inline-flex items-center">
                <input 
                    type="checkbox" 
                    value="commercial" 
                    v-model="selectedTypes" 
                    class="form-checkbox text-blue-600" 
                />
                <span class="ml-2">Commercial</span>
            </label>
            <label class="inline-flex items-center">
                <input 
                    type="checkbox" 
                    value="guest_house" 
                    v-model="selectedTypes" 
                    class="form-checkbox text-blue-600" 
                />
                <span class="ml-2">Guest House</span>
            </label>
            <label class="inline-flex items-center">
                <input 
                    type="checkbox" 
                    value="other" 
                    v-model="selectedTypes" 
                    class="form-checkbox text-blue-600" 
                />
                <span class="ml-2">Other</span>
            </label>
        </div>
    </div>
</template>