<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { computed, ref } from 'vue';
import {  User } from '@/types';
import { formatDate, } from '@/utils/formatters';
import BookingCard from '@/components/bookings/BookingCard.vue';

interface UserStats {
    total_properties: number;
    active_properties: number;
    total_bookings: number;
    confirmed_bookings: number;
    pending_bookings: number;
    member_since: string;
    last_login?: string;
}

interface Props {
    user: User;
    stats: UserStats;
    roleOptions: Record<string, string>;
}

const { user, stats, roleOptions } = defineProps<Props>();

const showDeleteModal = ref(false);
const showEmailModal = ref(false);
const isImpersonating = ref(false);
const isResendingEmail = ref(false);

// Computed properties
const isEmailVerified = computed(() => !!user.email_verified_at);
const isAdmin = computed(() => user.role === 'admin');
const canImpersonate = computed(() => !isAdmin.value);

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

// Action handlers
const deleteUser = () => {
    if (confirm(`Are you sure you want to delete "${user.name}"? This action cannot be undone and will permanently delete their account and associated data.`)) {
        router.delete(route('admin.users.destroy', user.id));
    }
};

const toggleEmailVerification = () => {
    router.patch(route('admin.users.toggle-email-verification', user.id), {}, {
        preserveScroll: true
    });
};

const resendEmailVerification = () => {
    // Prevent multiple clicks
    isResendingEmail.value = true;

    // Send POST request to resend email verification
    router.post(route('admin.users.resend-email-verification', user.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            showEmailModal.value = false;
        },
        onError: () => {
            showEmailModal.value = false;
        },
        onFinish: () => {
            showEmailModal.value = false;
            isResendingEmail.value = false;
        }
    });
};

const impersonateUser = () => {
    if (confirm(`Are you sure you want to impersonate "${user.name}"? This will log you in as them.`)) {
        isImpersonating.value = true;
        router.post(route('admin.users.impersonate', user.id));
    }
};

const sendEmail = (email: string) => {
    window.location.href = `mailto:${email}`;
};

const printProfile = () => {
    window.print();
};
</script>

<template>
    <Head :title="`${user.name} - User Profile`" />

    <AppLayout>
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col xl:flex-row xl:justify-between items-center py-6">
                    <div class="min-w-0 flex-1">
                        <nav class="flex" aria-label="Breadcrumb">
                            <ol class="flex items-center space-x-4">
                                <li>
                                    <Link :href="route('admin.users.index')" class="text-gray-400 hover:text-gray-500">
                                        Users
                                    </Link>
                                </li>
                                <li>
                                    <div class="flex items-center">
                                        <svg class="flex-shrink-0 h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="ml-4 text-sm font-medium text-gray-500">{{ user.name }}</span>
                                    </div>
                                </li>
                            </ol>
                        </nav>
                        
                        <!-- User Header -->
                        <div class="mt-4 flex items-center">
                            <!-- Avatar -->
                            <div class="flex-shrink-0 h-20 w-20 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center mr-6">
                                <span class="text-2xl font-medium text-gray-700 dark:text-gray-300">
                                    {{ user.name.charAt(0).toUpperCase() }}
                                </span>
                            </div>
                            
                            <!-- User Info -->
                            <div>
                                <h1 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl">
                                    {{ user.name }}
                                </h1>
                                <div class="mt-1 flex items-center space-x-4">
                                    <span class="text-gray-600 dark:text-gray-400">{{ user.email }}</span>
                                    <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getRoleBadgeClass(user.role)]">
                                        {{ roleOptions[user.role] || user.role }}
                                    </span>
                                    <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getVerificationBadgeClass(isEmailVerified)]">
                                        {{ isEmailVerified ? 'Email Verified' : 'Email Unverified' }}
                                    </span>
                                </div>
                                <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    Member since {{ stats.member_since }} • User ID: {{ user.id }}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex space-x-3">
                        <button
                            @click="sendEmail(user.email)"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Email User
                        </button>
                        
                        <button
                            v-if="canImpersonate"
                            @click="impersonateUser"
                            :disabled="isImpersonating"
                            class="bg-purple-600 hover:bg-purple-700 disabled:bg-purple-400 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            {{ isImpersonating ? 'Impersonating...' : 'Impersonate' }}
                        </button>

                        <Link 
                            :href="route('admin.users.edit', user.id)"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit User
                        </Link>

                        <button
                            v-if="!isAdmin"
                            @click="showDeleteModal = true"
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content - 2 columns -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- User Statistics Overview -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Account Overview</h2>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
                                <!-- Bookings Stats -->
                                <div class="text-center">
                                    <div class="text-3xl font-bold text-green-600 dark:text-green-400">{{ stats.total_bookings }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Total Bookings</div>
                                    <div v-if="stats.confirmed_bookings" class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                                        {{ stats.confirmed_bookings }} confirmed
                                    </div>
                                </div>

                                <!-- Pending Bookings -->
                                <div class="text-center">
                                    <div class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ stats.pending_bookings }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Pending Bookings</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Bookings -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recent Bookings</h2>
                            <Link 
                                v-if="user.bookings.length > 0"
                                :href="route('admin.bookings.index', { user_id: user.id })"
                                class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 text-sm"
                            >
                                View All Bookings →
                            </Link>
                        </div>
                        <div class="p-6">
                            <div v-if="user.bookings.length === 0" class="text-center text-gray-500 dark:text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p>No bookings made yet</p>
                            </div>
                            <div v-else class="space-y-4">
                                <BookingCard 
                                    v-for="booking in user.bookings" 
                                    :key="booking.id" 
                                    :booking="booking"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar - 1 column -->
                <div class="space-y-8">
                    <!-- User Details -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">User Details</h2>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Full Name</span>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ user.name }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Email Address</span>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    <a :href="`mailto:${user.email}`" class="text-blue-600 dark:text-blue-400 hover:underline">
                                        {{ user.email }}
                                    </a>
                                </p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Role</span>
                                <p class="mt-1">
                                    <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getRoleBadgeClass(user.role)]">
                                        {{ roleOptions[user.role] || user.role }}
                                    </span>
                                </p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Email Verification</span>
                                <div class="mt-1 flex items-center space-x-2">
                                    <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getVerificationBadgeClass(isEmailVerified)]">
                                        {{ isEmailVerified ? 'Verified' : 'Unverified' }}
                                    </span>
                                    <button 
                                        @click="toggleEmailVerification"
                                        class="text-xs text-blue-600 hover:text-blue-500 font-medium"
                                        :title="isEmailVerified ? 'Mark as unverified' : 'Mark as verified'"
                                    >
                                        Toggle
                                    </button>
                                    <button 
                                        v-if="!isEmailVerified"
                                        @click="showEmailModal = true"
                                        class="text-xs text-green-600 hover:text-green-500 font-medium"
                                        title="Resend verification email"
                                    >
                                        Resend
                                    </button>
                                </div>
                                <p v-if="isEmailVerified" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Verified {{ formatDate(new Date(user.email_verified_at!)) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Account Information -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Account Information</h2>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">User ID</span>
                                <p class="mt-1 text-sm font-mono text-gray-900 dark:text-gray-100">{{ user.id }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Member Since</span>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ formatDate(new Date(user.created_at)) }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated</span>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ formatDate(new Date(user.updated_at)) }}</p>
                            </div>
                            <div v-if="stats.last_login">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Login</span>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ formatDate(new Date(stats.last_login)) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Quick Actions</h2>
                        </div>
                        <div class="p-6 space-y-2">
                            <button
                                @click="sendEmail(user.email)"
                                class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors flex items-center"
                            >
                                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Send Email
                            </button>
                            
                            <button
                                v-if="canImpersonate"
                                @click="impersonateUser"
                                :disabled="isImpersonating"
                                class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors flex items-center disabled:opacity-50"
                            >
                                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                {{ isImpersonating ? 'Impersonating...' : 'Login as User' }}
                            </button>

                            <button
                                @click="printProfile"
                                class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors flex items-center"
                            >
                                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Print Profile
                            </button>

                            <Link
                                :href="route('admin.users.edit', user.id)"
                                class="block w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors"
                            >
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit Profile
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="showDeleteModal = false">
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
                            Are you sure you want to delete <strong>{{ user.name }}</strong>? 
                            This action cannot be undone and will permanently delete their account, properties, and booking history.
                        </p>
                        <div class="mt-3 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-md">
                            <div class="text-xs text-yellow-800 dark:text-yellow-200">
                                <strong>Warning:</strong> This user has {{ stats.total_bookings ?? 0 }} bookings.
                            </div>
                        </div>
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
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                        >
                            Delete User
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Email Verification Modal -->
        <div v-if="showEmailModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="showEmailModal = false">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
                <div class="mt-3">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900">
                        <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="mt-2 px-7 py-3">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Resend Email Verification</h3>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            Send a new email verification link to <strong>{{ user.email }}</strong>?
                        </p>
                    </div>
                    <div class="flex justify-end space-x-3 px-7 py-3">
                        <button
                            @click="showEmailModal = false"
                            type="button"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 dark:bg-gray-600 dark:text-gray-300 dark:hover:bg-gray-500"
                        >
                            Cancel
                        </button>
                        <button
                            @click="resendEmailVerification"
                            type="button"
                            :disabled="isResendingEmail"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-blue-400 disabled:cursor-not-allowed flex items-center"
                        >
                            <svg v-if="isResendingEmail" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ isResendingEmail ? 'Sending...' : 'Send Email' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>