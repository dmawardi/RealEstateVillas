<script setup lang="ts">
import { User } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

interface Props {
    user?: User;
    roleOptions: Record<string, string>;
    submitRoute: string;
    cancelRoute: string;
    method: 'post' | 'put';
}

const { user, roleOptions, submitRoute, cancelRoute, method } = defineProps<Props>();

const isEditing = computed(() => !!user?.id);
const showPassword = ref(false);
const emailVerified = ref(!!user?.email_verified_at);

// Initialize form with user data or defaults
const form = useForm({
    name: user?.name || '',
    email: user?.email || '',
    password: '',
    password_confirmation: '',
    role: user?.role || 'user',
    email_verified: emailVerified.value,
});

const submit = () => {
    console.log('Submitting form with data:', form.data());
    const options = {
        onSuccess: () => {
            // Form will redirect automatically
        },
        onError: (errors: any) => {
            console.error('Form submission errors:', errors);
        }
    };

    if (method === 'put' && user?.id) {
        form.put(route(submitRoute, user.id), options);
    } else {
        form.post(route(submitRoute), options);
    }
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                {{ isEditing ? 'Edit User' : 'Create New User' }}
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ isEditing ? 'Update user information and settings.' : 'Add a new user to the system.' }}
            </p>
        </div>

        <form @submit.prevent="submit" class="p-6 space-y-6">
            <!-- Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Full Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Full Name *
                    </label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        placeholder="Enter full name"
                    />
                    <div v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                        {{ form.errors.name }}
                    </div>
                </div>

                <!-- Email Address -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Email Address *
                    </label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        placeholder="Enter email address"
                    />
                    <div v-if="form.errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400">
                        {{ form.errors.email }}
                    </div>
                </div>
            </div>

            <!-- Password Fields -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Password {{ isEditing ? '' : '*' }}
                    </label>
                    <div class="relative">
                        <input
                            id="password"
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            :required="!isEditing"
                            class="w-full px-3 py-2 pr-10 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            :placeholder="isEditing ? 'Leave blank to keep current password' : 'Enter password'"
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center"
                        >
                            <svg v-if="showPassword" class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.929 6.929l-3.172 3.171a10.05 10.05 0 00-.254 4.208M9.878 9.878L7 7"/>
                            </svg>
                            <svg v-else class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    <div v-if="form.errors.password" class="mt-1 text-sm text-red-600 dark:text-red-400">
                        {{ form.errors.password }}
                    </div>
                    <div v-if="!isEditing" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Password must be at least 8 characters long
                    </div>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Confirm Password {{ isEditing ? '' : '*' }}
                    </label>
                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        :type="showPassword ? 'text' : 'password'"
                        :required="!isEditing && !!form.password"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        placeholder="Confirm password"
                    />
                    <div v-if="form.errors.password_confirmation" class="mt-1 text-sm text-red-600 dark:text-red-400">
                        {{ form.errors.password_confirmation }}
                    </div>
                </div>
            </div>

            <!-- User Settings -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">User Settings</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Role -->
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            User Role *
                        </label>
                        <select
                            id="role"
                            v-model="form.role"
                            required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                        >
                            <option v-for="(label, value) in roleOptions" :key="value" :value="value">
                                {{ label }}
                            </option>
                        </select>
                        <div v-if="form.errors.role" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.role }}
                        </div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            <span v-if="form.role === 'admin'" class="text-yellow-600 dark:text-yellow-400">
                                ⚠️ Admin users have full system access
                            </span>
                            <span v-else>
                                Regular users can manage their own bookings and properties
                            </span>
                        </div>
                    </div>

                    <!-- Email Verification Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Email Verification
                        </label>
                        <div class="flex items-center space-x-3">
                            <label class="flex items-center">
                                <input
                                    v-model="form.email_verified"
                                    type="checkbox"
                                    class="rounded border-gray-300 dark:border-gray-600 text-blue-600 shadow-sm focus:ring-2 focus:ring-blue-500 dark:bg-gray-700"
                                />
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                    Mark email as verified
                                </span>
                            </label>
                        </div>
                        <div v-if="form.errors.email_verified" class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.email_verified }}
                        </div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ form.email_verified 
                                ? 'User will not receive email verification requests' 
                                : 'User will need to verify their email address'
                            }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <div class="flex justify-end space-x-3">
                    <Link
                        :href="route(cancelRoute)"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        Cancel
                    </Link>
                    
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed flex items-center"
                    >
                        <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ form.processing ? 'Saving...' : (isEditing ? 'Update User' : 'Create User') }}
                    </button>
                </div>
            </div>

            <!-- Form Error Summary -->
            <div v-if="Object.keys(form.errors).length > 0" class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.08 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                                Please fix the following errors:
                            </h3>
                            <ul class="mt-2 text-sm text-red-700 dark:text-red-300 list-disc list-inside">
                                <li v-for="(error, field) in form.errors" :key="field">
                                    {{ error }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>