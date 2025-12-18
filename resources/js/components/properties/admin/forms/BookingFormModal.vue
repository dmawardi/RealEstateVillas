<script setup lang="ts">
import { computed, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Booking, Property } from '@/types';
import { formatDateForInput } from '@/utils/formatters';

interface Props {
    show: boolean;
    property: Property;
    booking?: Booking | null
}

interface Emits {
    (e: 'close'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

// Computed to determine if editing
const isEditing = computed(() => !!props.booking);

// Form processing state
const processing = ref(false);
const errors = ref<Record<string, string>>({});

// Booking form data - updated to match nullable fields
const bookingForm = useForm({
    property_id: props.property.id, // For updates
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    check_in_date: '',
    check_out_date: '',
    number_of_guests: 1,
    number_of_rooms: null as number | null,
    flexible_dates: false as boolean,
    status: 'pending',
    total_price: 0,
    commission_rate: null as number | null,
    commission_amount: null as number | null,
    commission_paid: false as boolean,
    source: 'direct',
    booking_type: 'booking',
    special_requests: '',
    external_booking_id: '',
    notes: '' // Internal notes
});

// Updated form validation - only check_in_date and check_out_date are truly required
const isFormValid = computed(() => {
    const checkInDate = new Date(bookingForm.check_in_date);
    const checkOutDate = new Date(bookingForm.check_out_date);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    return (
        bookingForm.check_in_date &&
        bookingForm.check_out_date &&
        (isEditing.value || checkInDate >= today) && // Allow past dates when editing
        checkOutDate > checkInDate &&
        bookingForm.number_of_guests > 0 &&
        bookingForm.number_of_guests <= (props.property.bedrooms ? props.property.bedrooms * 2 : 20) &&
        bookingForm.total_price >= 0
    );
});

// Methods
const resetForm = () => {
    bookingForm.first_name = '';
    bookingForm.last_name = '';
    bookingForm.email = '';
    bookingForm.phone = '';
    bookingForm.check_in_date = '';
    bookingForm.check_out_date = '';
    bookingForm.number_of_guests = 1;
    bookingForm.number_of_rooms = null;
    bookingForm.flexible_dates = false;
    bookingForm.status = 'pending';
    bookingForm.total_price = 0;
    bookingForm.commission_rate = null;
    bookingForm.commission_amount = null;
    bookingForm.commission_paid = false;
    bookingForm.source = 'direct';
    bookingForm.booking_type = 'booking';
    bookingForm.special_requests = '';
    bookingForm.external_booking_id = '';
    bookingForm.notes = '';
    errors.value = {};
};

const openForm = () => {
    if (isEditing.value && props.booking) {
        // Pre-populate form with booking data for editing
        bookingForm.first_name = props.booking.first_name || '';
        bookingForm.last_name = props.booking.last_name || '';
        bookingForm.email = props.booking.email || '';
        bookingForm.phone = props.booking.phone || '';
        bookingForm.check_in_date = formatDateForInput(props.booking.check_in_date);
        bookingForm.check_out_date = formatDateForInput(props.booking.check_out_date);
        bookingForm.number_of_guests = props.booking.number_of_guests || 1;
        bookingForm.number_of_rooms = props.booking.number_of_rooms || null;
        bookingForm.flexible_dates = props.booking.flexible_dates || false;
        bookingForm.status = props.booking.status || 'pending';
        bookingForm.total_price = props.booking.total_price || 0;
        bookingForm.commission_rate = props.booking.commission_rate || null;
        bookingForm.commission_amount = props.booking.commission_amount || null;
        bookingForm.commission_paid = props.booking.commission_paid || false;
        bookingForm.source = props.booking.source || 'direct';
        bookingForm.booking_type = props.booking.booking_type || 'booking';
        bookingForm.special_requests = props.booking.special_requests || '';
        bookingForm.external_booking_id = props.booking.external_booking_id || '';
        bookingForm.notes = props.booking.notes || '';
    } else {
        // Reset form for new booking
        resetForm();
        // Set property_id to current property
        bookingForm.property_id = props.property.id;
        
        // Set default dates (today + 1 for check-in, today + 2 for check-out)
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        const dayAfter = new Date();
        dayAfter.setDate(dayAfter.getDate() + 2);
        
        bookingForm.check_in_date = formatDateForInput(tomorrow.toISOString());
        bookingForm.check_out_date = formatDateForInput(dayAfter.toISOString());
    }
};

const closeForm = () => {
    resetForm();
    processing.value = false;
    emit('close');
};

const submitBooking = async () => {
    // Additional client-side validation
    if (!isFormValid.value) {
        return;
    }
    
    // Validate dates
    const checkInDate = new Date(bookingForm.check_in_date);
    const checkOutDate = new Date(bookingForm.check_out_date);
    
    if (checkInDate >= checkOutDate) {
        alert('Check-out date must be after check-in date');
        return;
    }
    
    // Only check past dates for new bookings
    if (!isEditing.value && checkInDate < new Date()) {
        alert('Check-in date cannot be in the past');
        return;
    }
    
    // Calculate commission amount if rate is provided
    if (bookingForm.commission_rate && bookingForm.total_price > 0) {
        bookingForm.commission_amount = Math.round((bookingForm.total_price * bookingForm.commission_rate) / 100);
    }
    
    processing.value = true;
    errors.value = {};
    
    // Submit to appropriate route using router
    if (isEditing.value && props.booking) {
        // Update existing booking
        bookingForm.put(route('admin.bookings.update', props.booking.id), {
            preserveScroll: true,
            onSuccess: () => {
                closeForm();
            },
            onError: (validationErrors) => {
                console.error('Booking update errors:', validationErrors);
                errors.value = validationErrors;
            },
            onFinish: () => {
                processing.value = false;
            }
        });
        
    } else {
        // Create new booking
        bookingForm.post(route('admin.bookings.store'), {
            preserveScroll: true,
            onSuccess: () => {
            closeForm();
            },
            onError: (validationErrors) => {
            console.error('Booking creation errors:', validationErrors);
            errors.value = validationErrors;
            },
            onFinish: () => {
            processing.value = false;
            }
        });
    }
};

// Watch for show prop changes to initialize form
import { watch } from 'vue';
watch(() => props.show, (newShow) => {
    if (newShow) {
        openForm();
    }
});

// Watch for booking changes to reinitialize form when switching between create/edit
watch(() => props.booking, () => {
    if (props.show) {
        openForm();
    }
});
</script>

<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click="closeForm"
        >
            <div
                class="bg-white dark:bg-gray-800 rounded-lg max-w-4xl w-full max-h-full overflow-y-auto"
                @click.stop
            >
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ isEditing ? 'Edit Booking' : 'Create New Booking' }}
                    </h3>
                    <button @click="closeForm" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <form @submit.prevent="submitBooking" class="p-6 space-y-6">
                    <!-- Guest Information (All Optional) -->
                    <div>
                        <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">Guest Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    First Name
                                </label>
                                <input
                                    v-model="bookingForm.first_name"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    :class="{ 'border-red-500': errors.first_name }"
                                />
                                <div v-if="errors.first_name" class="mt-1 text-sm text-red-600">
                                    {{ errors.first_name }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Last Name
                                </label>
                                <input
                                    v-model="bookingForm.last_name"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    :class="{ 'border-red-500': errors.last_name }"
                                />
                                <div v-if="errors.last_name" class="mt-1 text-sm text-red-600">
                                    {{ errors.last_name }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Email
                                </label>
                                <input
                                    v-model="bookingForm.email"
                                    type="email"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    :class="{ 'border-red-500': errors.email }"
                                />
                                <div v-if="errors.email" class="mt-1 text-sm text-red-600">
                                    {{ errors.email }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Phone
                                </label>
                                <input
                                    v-model="bookingForm.phone"
                                    type="tel"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    :class="{ 'border-red-500': errors.phone }"
                                />
                                <div v-if="errors.phone" class="mt-1 text-sm text-red-600">
                                    {{ errors.phone }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Booking Details -->
                    <div>
                        <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">Booking Details</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Check-in Date <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="bookingForm.check_in_date"
                                    type="date"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    :class="{ 'border-red-500': errors.check_in_date }"
                                />
                                <div v-if="errors.check_in_date" class="mt-1 text-sm text-red-600">
                                    {{ errors.check_in_date }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Check-out Date <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="bookingForm.check_out_date"
                                    type="date"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    :class="{ 'border-red-500': errors.check_out_date }"
                                />
                                <div v-if="errors.check_out_date" class="mt-1 text-sm text-red-600">
                                    {{ errors.check_out_date }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Number of Guests
                                </label>
                                <input
                                    v-model.number="bookingForm.number_of_guests"
                                    type="number"
                                    min="1"
                                    :max="property.bedrooms ? property.bedrooms * 2 : 20"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    :class="{ 'border-red-500': errors.number_of_guests }"
                                />
                                <div v-if="errors.number_of_guests" class="mt-1 text-sm text-red-600">
                                    {{ errors.number_of_guests }}
                                </div>
                            </div>
                        </div>

                        <!-- Additional Booking Fields -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Number of Rooms
                                </label>
                                <input
                                    v-model.number="bookingForm.number_of_rooms"
                                    type="number"
                                    min="1"
                                    placeholder="Optional"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    :class="{ 'border-red-500': errors.number_of_rooms }"
                                />
                                <div v-if="errors.number_of_rooms" class="mt-1 text-sm text-red-600">
                                    {{ errors.number_of_rooms }}
                                </div>
                            </div>
                            <div class="flex items-center">
                                <input
                                    v-model="bookingForm.flexible_dates"
                                    type="checkbox"
                                    id="flexible_dates"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                />
                                <label for="flexible_dates" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                    Flexible dates
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Booking Source, Type and Status -->
                    <div>
                        <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">Source, Type & Status</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Source
                                </label>
                                <select
                                    v-model="bookingForm.source"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    :class="{ 'border-red-500': errors.source }"
                                >
                                    <option value="direct">Direct</option>
                                    <option value="airbnb">Airbnb</option>
                                    <option value="booking_com">Booking.com</option>
                                    <option value="agoda">Agoda</option>
                                    <option value="owner_blocked">Owner Blocked</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="other">Other</option>
                                </select>
                                <div v-if="errors.source" class="mt-1 text-sm text-red-600">
                                    {{ errors.source }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Booking Type
                                </label>
                                <select
                                    v-model="bookingForm.booking_type"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    :class="{ 'border-red-500': errors.booking_type }"
                                >
                                    <option value="booking">Booking</option>
                                    <option value="inquiry">Inquiry</option>
                                    <option value="blocked">Blocked</option>
                                    <option value="maintenance">Maintenance</option>
                                </select>
                                <div v-if="errors.booking_type" class="mt-1 text-sm text-red-600">
                                    {{ errors.booking_type }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Status
                                </label>
                                <select
                                    v-model="bookingForm.status"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    :class="{ 'border-red-500': errors.status }"
                                >
                                    <option value="pending">Pending</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="cancelled">Cancelled</option>
                                    <option value="completed">Completed</option>
                                    <option value="blocked">Blocked</option>
                                    <option value="withdrawn">Withdrawn</option>
                                </select>
                                <div v-if="errors.status" class="mt-1 text-sm text-red-600">
                                    {{ errors.status }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Information -->
                    <div>
                        <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">Financial Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Total Price
                                </label>
                                <input
                                    v-model.number="bookingForm.total_price"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    :class="{ 'border-red-500': errors.total_price }"
                                />
                                <div v-if="errors.total_price" class="mt-1 text-sm text-red-600">
                                    {{ errors.total_price }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Commission Rate (%)
                                </label>
                                <input
                                    v-model.number="bookingForm.commission_rate"
                                    type="number"
                                    min="0"
                                    max="100"
                                    step="0.01"
                                    placeholder="Optional"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    :class="{ 'border-red-500': errors.commission_rate }"
                                />
                                <div v-if="errors.commission_rate" class="mt-1 text-sm text-red-600">
                                    {{ errors.commission_rate }}
                                </div>
                            </div>
                            <div class="flex items-center">
                                <input
                                    v-model="bookingForm.commission_paid"
                                    type="checkbox"
                                    id="commission_paid"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                />
                                <label for="commission_paid" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                    Commission paid
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- External ID (for platform bookings) -->
                    <div v-if="bookingForm.source !== 'direct'">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            External Booking ID
                        </label>
                        <input
                            v-model="bookingForm.external_booking_id"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            :class="{ 'border-red-500': errors.external_booking_id }"
                            placeholder="Platform booking reference number"
                        />
                        <div v-if="errors.external_booking_id" class="mt-1 text-sm text-red-600">
                            {{ errors.external_booking_id }}
                        </div>
                    </div>

                    <!-- Special Requests and Notes -->
                    <div>
                        <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">Additional Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Special Requests
                                </label>
                                <textarea
                                    v-model="bookingForm.special_requests"
                                    rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    :class="{ 'border-red-500': errors.special_requests }"
                                    placeholder="Any special requests or notes from the guest..."
                                ></textarea>
                                <div v-if="errors.special_requests" class="mt-1 text-sm text-red-600">
                                    {{ errors.special_requests }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Internal Notes
                                </label>
                                <textarea
                                    v-model="bookingForm.notes"
                                    rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    :class="{ 'border-red-500': errors.notes }"
                                    placeholder="Internal notes (not visible to guests)..."
                                ></textarea>
                                <div v-if="errors.notes" class="mt-1 text-sm text-red-600">
                                    {{ errors.notes }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                        <button
                            type="button"
                            @click="closeForm"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="processing || !isFormValid"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="processing">{{ isEditing ? 'Updating...' : 'Creating...' }}</span>
                            <span v-else>{{ isEditing ? 'Update Booking' : 'Create Booking' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>
</template>