<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import FeatureForm from '@/components/features/FeatureForm.vue';
import { Feature } from '@/types';

interface Props {
    feature: Feature;
}

const { feature } = defineProps<Props>();
</script>

<template>
    <Head :title="`Edit ${feature.name}`" />

    <AppLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <FeatureForm
                    :feature="feature"
                    submit-route="admin.features.update"
                    cancel-route="admin.features.index"
                    method="put"
                />
            </div>
        </div>
    </AppLayout>
</template>