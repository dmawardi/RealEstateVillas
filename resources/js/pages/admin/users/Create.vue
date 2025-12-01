<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import UserForm from '@/components/users/UserForm.vue';

interface Props {
    roleOptions: Record<string, string>;
}

const { roleOptions } = defineProps<Props>();
</script>

<template>
    <Head title="Create User" />

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
                                    <span class="ml-4 text-sm font-medium text-gray-500">Create User</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                    
                    <div>
                        <h1 class="text-3xl font-bold leading-tight text-gray-900 dark:text-gray-100">
                            Create New User
                        </h1>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">
                            Add a new user to the system with appropriate permissions and settings.
                        </p>
                    </div>
                </div>

                <!-- Form -->
                <UserForm
                    :role-options="roleOptions"
                    submit-route="admin.users.store"
                    cancel-route="admin.users.index"
                    method="post"
                />
            </div>
        </div>
    </AppLayout>
</template>