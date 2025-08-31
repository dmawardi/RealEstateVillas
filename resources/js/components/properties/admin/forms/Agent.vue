<script setup lang="ts">
// filepath: /Users/d/Web Development/projects/RealEstate/resources/js/components/properties/admin/forms/Agent.vue
import { computed } from 'vue';

interface AgentFormData {
    agent_name: string | null;
    agent_phone: string | null;
    agent_email: string | null;
    agency_name: string | null;
}

interface Props {
    modelValue: AgentFormData;
    errors?: Record<string, string | undefined>;
}

interface Emits {
    (e: 'update:modelValue', value: AgentFormData): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

// Single computed property for two-way binding
const formData = computed({
    get: () => props.modelValue,
    set: (value: AgentFormData) => emit('update:modelValue', value)
});

// Helper functions for error handling
const getFieldError = (field: string) => {
    return props.errors?.[`agent.${field}`] || props.errors?.[field];
};

const hasFieldError = (field: string) => {
    return !!getFieldError(field);
};

// Simple helper function to update individual fields
const updateField = (field: keyof AgentFormData, value: any) => {
    formData.value = { ...formData.value, [field]: value };
};

// Validation helpers
const isValidEmail = (email: string): boolean => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
};

const isValidPhone = (phone: string): boolean => {
    // Simple phone validation - adjust based on your requirements
    const phoneRegex = /^[\+]?[0-9\s\-\(\)]{8,}$/;
    return phoneRegex.test(phone);
};

const formatPhoneNumber = (phone: string): string => {
    // Remove all non-numeric characters except +
    const cleaned = phone.replace(/[^\d\+]/g, '');
    
    // Format Indonesian phone numbers
    if (cleaned.startsWith('0')) {
        return '+62' + cleaned.substring(1);
    } else if (cleaned.startsWith('62')) {
        return '+' + cleaned;
    } else if (!cleaned.startsWith('+')) {
        return '+62' + cleaned;
    }
    return cleaned;
};

// Handle phone number formatting on blur
const handlePhoneBlur = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const value = target.value.trim();
    if (value && value.length > 0) {
        const formatted = formatPhoneNumber(value);
        updateField('agent_phone', formatted);
    }
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Agent Information</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Contact details for the agent or agency responsible for this property
            </p>
        </div>
        
        <div class="p-6 space-y-6">
            <!-- Agent Details -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Primary Contact</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="agent_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Agent Name *
                        </label>
                        <input
                            id="agent_name"
                            :value="formData.agent_name || ''"
                            @input="updateField('agent_name', ($event.target as HTMLInputElement).value || null)"
                            type="text"
                            :class="[
                                'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100',
                                hasFieldError('agent_name') 
                                    ? 'border-red-300 focus:border-red-500 focus:ring-red-500' 
                                    : 'border-gray-300 dark:border-gray-600'
                            ]"
                            placeholder="John Smith"
                            required
                        />
                        <p v-if="getFieldError('agent_name')" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ getFieldError('agent_name') }}
                        </p>
                        <p v-else class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Full name of the property agent or contact person
                        </p>
                    </div>

                    <div>
                        <label for="agency_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Agency Name
                        </label>
                        <input
                            id="agency_name"
                            :value="formData.agency_name || ''"
                            @input="updateField('agency_name', ($event.target as HTMLInputElement).value || null)"
                            type="text"
                            :class="[
                                'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100',
                                hasFieldError('agency_name') 
                                    ? 'border-red-300 focus:border-red-500 focus:ring-red-500' 
                                    : 'border-gray-300 dark:border-gray-600'
                            ]"
                            placeholder="Bali Real Estate Co."
                        />
                        <p v-if="getFieldError('agency_name')" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ getFieldError('agency_name') }}
                        </p>
                        <p v-else class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Real estate agency or company name (optional)
                        </p>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Contact Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="agent_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Phone Number
                        </label>
                        <input
                            id="agent_phone"
                            :value="formData.agent_phone || ''"
                            @input="updateField('agent_phone', ($event.target as HTMLInputElement).value || null)"
                            @blur="handlePhoneBlur"
                            type="tel"
                            :class="[
                                'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100',
                                hasFieldError('agent_phone') 
                                    ? 'border-red-300 focus:border-red-500 focus:ring-red-500' 
                                    : 'border-gray-300 dark:border-gray-600'
                            ]"
                            placeholder="+62 812 3456 7890"
                        />
                        <p v-if="getFieldError('agent_phone')" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ getFieldError('agent_phone') }}
                        </p>
                        <p v-else-if="formData.agent_phone && !isValidPhone(formData.agent_phone)" class="mt-1 text-sm text-orange-600 dark:text-orange-400">
                            Please enter a valid phone number
                        </p>
                        <p v-else class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Mobile or office phone number with country code
                        </p>
                    </div>

                    <div>
                        <label for="agent_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Email Address
                        </label>
                        <input
                            id="agent_email"
                            :value="formData.agent_email || ''"
                            @input="updateField('agent_email', ($event.target as HTMLInputElement).value || null)"
                            type="email"
                            :class="[
                                'mt-1 block w-full rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100',
                                hasFieldError('agent_email') 
                                    ? 'border-red-300 focus:border-red-500 focus:ring-red-500' 
                                    : 'border-gray-300 dark:border-gray-600'
                            ]"
                            placeholder="agent@example.com"
                        />
                        <p v-if="getFieldError('agent_email')" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ getFieldError('agent_email') }}
                        </p>
                        <p v-else-if="formData.agent_email && !isValidEmail(formData.agent_email)" class="mt-1 text-sm text-orange-600 dark:text-orange-400">
                            Please enter a valid email address
                        </p>
                        <p v-else class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Primary email for property inquiries
                        </p>
                    </div>
                </div>
            </div>

            <!-- Contact Summary Card -->
            <div v-if="formData.agent_name || formData.agent_phone || formData.agent_email" class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">Contact Summary</h4>
                <div class="space-y-2">
                    <div v-if="formData.agent_name" class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span class="text-sm text-gray-700 dark:text-gray-300">
                            {{ formData.agent_name }}
                            <span v-if="formData.agency_name" class="text-gray-500 dark:text-gray-400">
                                ({{ formData.agency_name }})
                            </span>
                        </span>
                    </div>
                    
                    <div v-if="formData.agent_phone" class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ formData.agent_phone }}</span>
                    </div>
                    
                    <div v-if="formData.agent_email" class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ formData.agent_email }}</span>
                    </div>
                </div>
            </div>

            <!-- Agent Guidelines -->
            <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-indigo-800 dark:text-indigo-300">
                            Agent Information Guidelines
                        </h3>
                        <div class="mt-2 text-sm text-indigo-700 dark:text-indigo-400">
                            <ul class="list-disc list-inside space-y-1">
                                <li><strong>Agent name:</strong> Required field - this will be displayed to potential buyers/renters</li>
                                <li><strong>Contact details:</strong> Ensure phone and email are current and monitored regularly</li>
                                <li><strong>Phone format:</strong> International format recommended (+62 for Indonesia)</li>
                                <li><strong>Professional image:</strong> Complete contact information builds trust with clients</li>
                                <li><strong>Response time:</strong> Quick responses to inquiries improve conversion rates</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>