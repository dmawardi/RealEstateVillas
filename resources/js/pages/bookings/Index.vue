<script setup lang="ts">
import { Booking } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';
import BookingList from '@/components/BookingList.vue';

interface Props {
    bookings: Booking[];
}

const { bookings } = defineProps<Props>();
</script>

<template>
    <Head title="My Bookings" />

    <AppLayout>
        <template v-if="bookings.length">
            <BookingList :bookings="bookings" />
        </template>
        <template v-else>
            <div class="text-center my-auto">
                <p>No bookings found.</p>
            </div>
        </template>
    </AppLayout>
</template>