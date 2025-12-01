<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import UserForm from '@/components/users/UserForm.vue';
import { User } from '@/types';

interface Props {
    user: User;
    roleOptions: Record<string, string>;
}

const { user, roleOptions } = defineProps<Props>();
</script>

<template>
    <Head :title="`Edit ${user.name}`" />

    <AppLayout>
        <div class="py-8">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <nav class="flex mb-4" aria-label="Breadcrumb">
                        <ol class="flex items-center space-x-4">
                            <li>
                                <Link :href="route('admin.users.index')" class="text-gray-400 hover:text-gray-500 text-sm">
                                    Users
                                </Link>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="flex-shrink-0 h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    <Link :href="route('admin.users.show', user.id)" class="ml-4 text-sm text-gray-400 hover:text-gray-500">
                                        {{ user.name }}
                                    </Link>
                                </div>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="flex-shrink-0 h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="ml-4 text-sm font-medium text-gray-500">Edit</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                    
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold leading-tight text-gray-900 dark:text-gray-100">
                                Edit User
                            </h1>
                            <p class="mt-2 text-gray-600 dark:text-gray-400">
                                Update <strong>{{ user.name }}</strong>'s profile information and settings.
                            </p>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="flex space-x-3">
                            <Link
                                :href="route('admin.users.show', user.id)"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                View Profile
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- User Info Card -->
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-800 flex items-center justify-center">
                            <span class="text-blue-600 dark:text-blue-300 font-medium">
                                {{ user.name.charAt(0).toUpperCase() }}
                            </span>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-blue-900 dark:text-blue-100">
                                Currently editing: {{ user.name }}
                            </div>
                            <div class="text-sm text-blue-700 dark:text-blue-300">
                                {{ user.email }} • {{ user.role === 'admin' ? 'Administrator' : 'User' }} • 
                                Member since {{ new Date(user.created_at).toLocaleDateString() }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <UserForm
                    :user="user"
                    :role-options="roleOptions"
                    submit-route="admin.users.update"
                    cancel-route="admin.users.index"
                    method="put"
                />
            </div>
        </div>
    </AppLayout>
</template>