<!-- resources/js/components/bookings/BookingForm.vue -->
<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

interface Property {
    id: number;
    title: string;
    property_id: string;
    max_guests?: number;
    max_rooms?: number;
}

interface Props {
    booking?: {
        id?: number;
        property_id: number;
        first_name: string | null;
        last_name: string | null;
        email: string | null;
        phone: string | null;
        check_in_date: string;
        check_out_date: string;
        number_of_guests: number;
        number_of_rooms: number | null;
        status: string;
        source: string;
        booking_type: string;
        external_booking_id: string | null;
        total_price: number;
        commission_rate: number | null;
        commission_amount: number | null;
        commission_paid: boolean;
        flexible_dates: boolean;
        special_requests: string | null;
        notes: string | null;
    };
    properties: Property[];
    submitRoute: string;
    cancelRoute: string;
    method: 'post' | 'put';
}

const props = withDefaults(defineProps<Props>(), {
    method: 'post'
});

const isEditing = computed(() => props.booking?.id !== undefined);

// Form data
const form = useForm({
    // Property selection
    property_id: props.booking?.property_id || '',
    
    // Guest Information
    first_name: props.booking?.first_name || '',
    last_name: props.booking?.last_name || '',
    email: props.booking?.email || '',
    phone: props.booking?.phone || '',
    
    // Booking Details
    check_in_date: props.booking?.check_in_date || '',
    check_out_date: props.booking?.check_out_date || '',
    number_of_guests: props.booking?.number_of_guests || 1,
    number_of_rooms: props.booking?.number_of_rooms || null,
    
    // Booking Configuration
    status: props.booking?.status || 'pending',
    source: props.booking?.source || 'direct',
    booking_type: props.booking?.booking_type || 'booking',
    external_booking_id: props.booking?.external_booking_id || '',
    
    // Financial Details
    total_price: props.booking?.total_price || 0,
    commission_rate: props.booking?.commission_rate || null,
    commission_amount: props.booking?.commission_amount || null,
    commission_paid: props.booking?.commission_paid || false,
    
    // Additional Details
    flexible_dates: props.booking?.flexible_dates || false,
    special_requests: props.booking?.special_requests || '',
    notes: props.booking?.notes || '',
    
    // Admin options
    send_confirmation_email: true,
    override_availability_check: false,
});

// Status options from migration enum
const statusOptions = [
    { value: 'pending', label: 'Pending' },
    { value: 'confirmed', label: 'Confirmed' },
    { value: 'cancelled', label: 'Cancelled' },
    { value: 'completed', label: 'Completed' },
    { value: 'blocked', label: 'Blocked' },
    { value: 'withdrawn', label: 'Withdrawn' }
];

// Source options from migration enum
const sourceOptions = [
    { value: 'direct', label: 'Direct' },
    { value: 'airbnb', label: 'Airbnb' },
    { value: 'booking_com', label: 'Booking.com' },
    { value: 'agoda', label: 'Agoda' },
    { value: 'owner_blocked', label: 'Owner Blocked' },
    { value: 'maintenance', label: 'Maintenance' },
    { value: 'other', label: 'Other' }
];

// Booking type options from migration enum
const bookingTypeOptions = [
    { value: 'booking', label: 'Booking' },
    { value: 'inquiry', label: 'Inquiry' },
    { value: 'blocked', label: 'Blocked' },
    { value: 'maintenance', label: 'Maintenance' }
];

// Get selected property details
const selectedProperty = computed(() => {
    return props.properties.find(p => p.id === Number(form.property_id));
});

// Calculate nights
const nights = computed(() => {
    if (!form.check_in_date || !form.check_out_date) return 0;
    const checkIn = new Date(form.check_in_date);
    const checkOut = new Date(form.check_out_date);
    const diffTime = checkOut.getTime() - checkIn.getTime();
    return Math.max(0, Math.ceil(diffTime / (1000 * 60 * 60 * 24)));
});

// Auto-calculate commission amount when rate or total price changes
watch([() => form.commission_rate, () => form.total_price], ([rate, price]) => {
    if (rate && price && rate > 0) {
        form.commission_amount = Math.round((price * rate) / 100);
    } else {
        form.commission_amount = null;
    }
});

// Validate dates
const dateError = computed(() => {
    if (!form.check_in_date || !form.check_out_date) return '';
    if (new Date(form.check_out_date) <= new Date(form.check_in_date)) {
        return 'Check-out date must be after check-in date';
    }
    return '';
});

// Submit form
const submit = () => {
    const routeParams = isEditing.value ? [props.booking!.id] : [];
    
    if (isEditing.value) {
        form.put(route(props.submitRoute, routeParams));
    } else {
        form.post(route(props.submitRoute));
    }
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 lg:p-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ isEditing ? 'Edit Booking' : 'Create New Booking' }}
                    </h1>
                    <p class="mt-1 text-gray-600 dark:text-gray-400">
                        {{ isEditing ? 'Update booking details and configuration.' : 'Create a new booking reservation.' }}
                    </p>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-8">
                <!-- Property Selection -->
                <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Property & Dates</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Property -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Property <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.property_id"
                                required
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">Select a property...</option>
                                <option v-for="property in properties" :key="property.id" :value="property.id">
                                    {{ property.title }} ({{ property.property_id }})
                                </option>
                            </select>
                            <div v-if="form.errors.property_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.property_id }}
                            </div>
                        </div>

                        <!-- Check-in Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Check-in Date <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.check_in_date"
                                type="date"
                                required
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                            <div v-if="form.errors.check_in_date" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.check_in_date }}
                            </div>
                        </div>

                        <!-- Check-out Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Check-out Date <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.check_out_date"
                                type="date"
                                required
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                            <div v-if="form.errors.check_out_date || dateError" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.check_out_date || dateError }}
                            </div>
                            <div v-if="nights > 0" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ nights }} night{{ nights !== 1 ? 's' : '' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Guest Information -->
                <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Guest Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- First Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                First Name
                            </label>
                            <input
                                v-model="form.first_name"
                                type="text"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                            <div v-if="form.errors.first_name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.first_name }}
                            </div>
                        </div>

                        <!-- Last Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Last Name
                            </label>
                            <input
                                v-model="form.last_name"
                                type="text"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                            <div v-if="form.errors.last_name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.last_name }}
                            </div>
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Email
                            </label>
                            <input
                                v-model="form.email"
                                type="email"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                            <div v-if="form.errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.email }}
                            </div>
                        </div>

                        <!-- Phone -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Phone
                            </label>
                            <input
                                v-model="form.phone"
                                type="tel"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                            <div v-if="form.errors.phone" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.phone }}
                            </div>
                        </div>

                        <!-- Number of Guests -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Number of Guests <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model.number="form.number_of_guests"
                                type="number"
                                min="1"
                                :max="selectedProperty?.max_guests || 50"
                                required
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                            <div v-if="form.errors.number_of_guests" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.number_of_guests }}
                            </div>
                        </div>

                        <!-- Number of Rooms -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Number of Rooms
                            </label>
                            <input
                                v-model.number="form.number_of_rooms"
                                type="number"
                                min="1"
                                :max="selectedProperty?.max_rooms || 20"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                            <div v-if="form.errors.number_of_rooms" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.number_of_rooms }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Booking Configuration -->
                <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Booking Configuration</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.status"
                                required
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                            <div v-if="form.errors.status" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.status }}
                            </div>
                        </div>

                        <!-- Source -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Source <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.source"
                                required
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option v-for="option in sourceOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                            <div v-if="form.errors.source" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.source }}
                            </div>
                        </div>

                        <!-- Booking Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Booking Type <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.booking_type"
                                required
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option v-for="option in bookingTypeOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                            <div v-if="form.errors.booking_type" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.booking_type }}
                            </div>
                        </div>

                        <!-- External Booking ID -->
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                External Booking ID
                            </label>
                            <input
                                v-model="form.external_booking_id"
                                type="text"
                                placeholder="External platform booking reference"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                            <div v-if="form.errors.external_booking_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.external_booking_id }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial Details -->
                <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Financial Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Total Price -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Total Price <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model.number="form.total_price"
                                type="number"
                                min="0"
                                step="0.01"
                                required
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                            <div v-if="form.errors.total_price" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.total_price }}
                            </div>
                        </div>

                        <!-- Commission Rate -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Commission Rate (%)
                            </label>
                            <input
                                v-model.number="form.commission_rate"
                                type="number"
                                min="0"
                                max="100"
                                step="0.01"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                            <div v-if="form.errors.commission_rate" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.commission_rate }}
                            </div>
                        </div>

                        <!-- Commission Amount -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Commission Amount
                            </label>
                            <input
                                v-model.number="form.commission_amount"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                            <div v-if="form.errors.commission_amount" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.commission_amount }}
                            </div>
                            <div v-if="form.commission_rate && form.total_price" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Auto-calculated: ${{ Math.round((form.total_price * form.commission_rate) / 100) }}
                            </div>
                        </div>

                        <!-- Commission Paid -->
                        <div class="flex items-center">
                            <input
                                v-model="form.commission_paid"
                                type="checkbox"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded"
                            />
                            <label class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                Commission Paid
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Additional Information</h3>
                    <div class="space-y-6">
                        <!-- Special Requests -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Special Requests
                            </label>
                            <textarea
                                v-model="form.special_requests"
                                rows="3"
                                placeholder="Guest special requests or preferences..."
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            ></textarea>
                            <div v-if="form.errors.special_requests" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.special_requests }}
                            </div>
                        </div>

                        <!-- Admin Notes -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Internal Notes
                            </label>
                            <textarea
                                v-model="form.notes"
                                rows="3"
                                placeholder="Internal notes and administrative information..."
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            ></textarea>
                            <div v-if="form.errors.notes" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.notes }}
                            </div>
                        </div>

                        <!-- Options -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="flex items-center">
                                <input
                                    v-model="form.flexible_dates"
                                    type="checkbox"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded"
                                />
                                <label class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                    Flexible Dates
                                </label>
                            </div>

                            <div v-if="!isEditing" class="flex items-center">
                                <input
                                    v-model="form.send_confirmation_email"
                                    type="checkbox"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded"
                                />
                                <label class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                    Send Confirmation Email
                                </label>
                            </div>

                            <div v-if="!isEditing" class="md:col-span-2">
                                <div class="flex items-center">
                                    <input
                                        v-model="form.override_availability_check"
                                        type="checkbox"
                                        class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 dark:border-gray-600 rounded"
                                    />
                                    <label class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                        Override Availability Check
                                        <span class="text-red-600 text-xs">(Force booking even if dates are unavailable)</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200 dark:border-gray-600">
                    <Link
                        :href="route(cancelRoute)"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <span v-if="form.processing">
                            {{ isEditing ? 'Updating...' : 'Creating...' }}
                        </span>
                        <span v-else>
                            {{ isEditing ? 'Update Booking' : 'Create Booking' }}
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>