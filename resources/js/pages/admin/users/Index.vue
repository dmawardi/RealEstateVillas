
<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref } from 'vue';
import { formatPaginationLabel } from '@/utils';
import type { User, PaginatedUsers } from '@/types';

interface UserFilters {
    search?: string;
    role?: string;
    email_verified?: string;
    created_from?: string;
    created_to?: string;
    [key: string]: string | undefined;
}

interface Props {
    users: PaginatedUsers;
    filters?: UserFilters;
    roleOptions: Record<string, string>;
    emailVerificationOptions: Record<string, string>;
}

const { users, filters, roleOptions, emailVerificationOptions } = defineProps<Props>();

const showDeleteModal = ref(false);
const userToDelete = ref<User | null>(null);
const deleteForm = useForm({});

const confirmDelete = (user: User) => {
    userToDelete.value = user;
    showDeleteModal.value = true;
};

const deleteUser = () => {
    if (userToDelete.value) {
        deleteForm.delete(route('admin.users.destroy', userToDelete.value.id), {
            onSuccess: () => {
                showDeleteModal.value = false;
                userToDelete.value = null;
            },
            onError: () => {
                // Error handling is done via flash messages
            }
        });
    }
};

const handleRoleChange = (e: Event) => {
    const value = (e.target as HTMLSelectElement)?.value || undefined;
    router.get(route('admin.users.index'), { ...(filters || {}), role: value }, { preserveState: true });
};

const handleEmailVerifiedChange = (e: Event) => {
    const value = (e.target as HTMLSelectElement)?.value || undefined;
    router.get(route('admin.users.index'), { ...(filters || {}), email_verified: value }, { preserveState: true });
};

const handleCreatedFromChange = (e: Event) => {
    const value = (e.target as HTMLInputElement)?.value || undefined;
    router.get(route('admin.users.index'), { ...(filters || {}), created_from: value }, { preserveState: true });
};

const handleCreatedToChange = (e: Event) => {
    const value = (e.target as HTMLInputElement)?.value || undefined;
    router.get(route('admin.users.index'), { ...(filters || {}), created_to: value }, { preserveState: true });
};

const toggleEmailVerification = (user: User) => {
    const form = useForm({});
    form.patch(route('admin.users.toggle-email-verification', user.id));
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const getRoleBadgeClass = (role: string) => {
    return role === 'admin' 
        ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300'
        : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
};

const getVerificationBadgeClass = (verified: boolean) => {
    return verified
        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
        : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
};
</script>

<template>
    <Head title="Users Management" />

    <AppLayout>
        <header class="my-4 mx-4">
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Users Management
                </h2>
                <div class="flex space-x-3">
                    <!-- Export Button -->
                    <Link 
                        :href="route('admin.users.export')"
                        :data="filters"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export CSV
                    </Link>
                    
                    <!-- Add User Button -->
                    <Link 
                        :href="route('admin.users.create')"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add User
                    </Link>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap gap-4 mt-4">
                <!-- Role Filter -->
                <div class="flex-1 min-w-48">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
                    <select 
                        :value="filters?.role || ''"
                        @change="handleRoleChange"
                        class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">All Roles</option>
                        <option v-for="(label, value) in roleOptions" :key="value" :value="value">
                            {{ label }}
                        </option>
                    </select>
                </div>

                <!-- Email Verification Filter -->
                <div class="flex-1 min-w-48">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email Status</label>
                    <select 
                        :value="filters?.email_verified || ''"
                        @change="handleEmailVerifiedChange"
                        class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option v-for="(label, value) in emailVerificationOptions" :key="value" :value="value">
                            {{ label }}
                        </option>
                    </select>
                </div>

                <!-- Date Range Filters -->
                <div class="flex-1 min-w-48">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Created From</label>
                    <input 
                        type="date"
                        :value="filters?.created_from || ''"
                        @change="handleCreatedFromChange"
                        class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                </div>

                <div class="flex-1 min-w-48">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Created To</label>
                    <input 
                        type="date"
                        :value="filters?.created_to || ''"
                        @change="handleCreatedToChange"
                        class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                </div>
            </div>
        </header>

        <!-- Users Table -->
        <div class="mx-4 bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-md">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                ID
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Actions
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                User
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Role
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Email Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Bookings
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Member Since
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Last Updated
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ user.id }}
                            </td>
                            
                            <!-- Actions -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <div class="flex space-x-2">
                                    <Link 
                                        :href="route('admin.users.show', user.id)" 
                                        class="text-green-600 hover:text-green-500 font-medium"
                                    >
                                        View
                                    </Link>
                                    <Link 
                                        :href="route('admin.users.edit', user.id)" 
                                        class="text-blue-600 hover:text-blue-500 font-medium"
                                    >
                                        Edit
                                    </Link>
                                    <button 
                                        v-if="user.role !== 'admin'"
                                        @click="confirmDelete(user)" 
                                        class="text-red-600 hover:text-red-500 font-medium"
                                    >
                                        Delete
                                    </button>
                                    <button 
                                        v-if="user.role !== 'admin'"
                                        @click="$inertia.post(route('admin.users.impersonate', user.id))"
                                        class="text-purple-600 hover:text-purple-500 font-medium"
                                        title="Login as this user"
                                    >
                                        Impersonate
                                    </button>
                                </div>
                            </td>
                            
                            <!-- User Info -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                {{ user.name.charAt(0).toUpperCase() }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ user.name }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ user.email }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            
                            <!-- Role -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="getRoleBadgeClass(user.role)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                    {{ roleOptions[user.role] || user.role }}
                                </span>
                            </td>
                            
                            <!-- Email Verification Status -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    <span :class="getVerificationBadgeClass(!!user.email_verified_at)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                        {{ user.email_verified_at ? 'Verified' : 'Unverified' }}
                                    </span>
                                    <button 
                                        @click="toggleEmailVerification(user)"
                                        class="text-xs text-blue-600 hover:text-blue-500 font-medium"
                                        :title="user.email_verified_at ? 'Mark as unverified' : 'Mark as verified'"
                                    >
                                        Toggle
                                    </button>
                                </div>
                            </td>
                            
                            <!-- Bookings Count -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <div class="flex items-center">
                                    <span class="font-medium">{{ user.bookings.length || 0 }}</span>
                                    <svg class="w-4 h-4 ml-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            </td>
                            
                            <!-- Created Date -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ formatDate(user.created_at) }}
                            </td>
                            
                            <!-- Updated Date -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ formatDate(user.updated_at) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div v-if="users.data.length === 0" class="text-center py-12">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9.45s1.5-1.5 1.5-1.5"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                    No users found
                </h3>
                <p class="text-gray-600 dark:text-gray-400 mb-4">
                    {{ filters?.search ? 'No users match your search criteria.' : 'Get started by adding your first user.' }}
                </p>
                <Link 
                    v-if="!filters?.search"
                    :href="route('admin.users.create')"
                    class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Your First User
                </Link>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="users.data.length > 0 && users.last_page > 1" class="mx-4 mt-6">
            <nav class="flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                    <Link
                        v-if="users.current_page > 1"
                        :href="users.links[0].url || ''"
                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        Previous
                    </Link>
                    <Link
                        v-if="users.current_page < users.last_page"
                        :href="users.links[users.links.length - 1].url || ''"
                        class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        Next
                    </Link>
                </div>
                
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            Showing
                            <span class="font-medium">{{ users.from }}</span>
                            to
                            <span class="font-medium">{{ users.to }}</span>
                            of
                            <span class="font-medium">{{ users.total }}</span>
                            results
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                            <Link
                                v-for="link in users.links"
                                :key="link.label"
                                :href="link.url || ''"
                                :class="[
                                    link.active
                                        ? 'z-10 bg-blue-50 border-blue-500 text-blue-600 dark:bg-blue-900 dark:border-blue-400 dark:text-blue-300'
                                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700',
                                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                                ]"
                            >
                                {{ formatPaginationLabel(link.label) }}
                            </Link>
                        </nav>
                    </div>
                </div>
            </nav>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
                <div class="mt-3">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900">
                        <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.08 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                    <div class="mt-2 px-7 py-3">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Delete User</h3>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            Are you sure you want to delete <strong>{{ userToDelete?.name }}</strong>? 
                            This action cannot be undone and will permanently delete their account and associated data.
                        </p>
                    </div>
                    <div class="flex justify-end space-x-3 px-7 py-3">
                        <button
                            @click="showDeleteModal = false"
                            type="button"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 dark:bg-gray-600 dark:text-gray-300 dark:hover:bg-gray-500"
                        >
                            Cancel
                        </button>
                        <button
                            @click="deleteUser"
                            type="button"
                            :disabled="deleteForm.processing"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50"
                        >
                            <span v-if="deleteForm.processing">Deleting...</span>
                            <span v-else>Delete</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>