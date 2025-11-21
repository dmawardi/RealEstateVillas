<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import BookingForm from '@/components/bookings/BookingForm.vue';

interface Props {
    properties: Array<{
        id: number;
        title: string;
        property_id: string;
        max_guests?: number;
        max_rooms?: number;
    }>;
}

defineProps<Props>();
</script>

<template>
    <Head title="Create Booking" />

    <AppLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <BookingForm
                    :properties="properties"
                    submit-route="admin.bookings.store"
                    cancel-route="admin.bookings.index"
                    method="post"
                />
            </div>
        </div>
    </AppLayout>
</template>