<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import BookingForm from '@/components/bookings/BookingForm.vue';

interface Property {
    id: number;
    title: string;
    property_id: string;
    max_guests?: number;
    max_rooms?: number;
}

interface Booking {
    id: number;
    property_id: number;
    user_id: number;
    first_name: string | null;
    last_name: string | null;
    email: string | null;
    phone: string | null;
    check_in_date: string;
    check_out_date: string;
    number_of_guests: number;
    number_of_rooms: number | null;
    status: 'pending' | 'confirmed' | 'cancelled' | 'completed' | 'blocked' | 'withdrawn';
    source: 'direct' | 'airbnb' | 'booking_com' | 'agoda' | 'owner_blocked' | 'maintenance' | 'other';
    booking_type: 'booking' | 'inquiry' | 'blocked' | 'maintenance';
    external_booking_id: string | null;
    total_price: number;
    commission_rate: number | null;
    commission_amount: number | null;
    commission_paid: boolean;
    flexible_dates: boolean;
    special_requests: string | null;
    notes: string | null;
    created_at: string;
    updated_at: string;
}

interface Props {
    booking: Booking;
    properties: Property[];
}

const { booking, properties } = defineProps<Props>();

// Get guest name for title
const guestName = (() => {
    const name = `${booking.first_name || ''} ${booking.last_name || ''}`.trim();
    return name || `Booking #${booking.id}`;
})();
</script>

<template>
    <Head :title="`Edit ${guestName}`" />

    <AppLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <BookingForm
                    :booking="booking"
                    :properties="properties"
                    submit-route="admin.bookings.update"
                    cancel-route="admin.bookings.index"
                    method="put"
                />
            </div>
        </div>
    </AppLayout>
</template>