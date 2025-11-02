<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import FeatureForm from '@/components/features/FeatureForm.vue';
</script>

<template>
    <Head title="Create Feature" />

    <AppLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <FeatureForm
                    submit-route="admin.features.store"
                    cancel-route="admin.features.index"
                    method="post"
                />
            </div>
        </div>
    </AppLayout>
</template>