<script setup lang="ts">
interface Props {
    title: string;
    description: string;
}
defineProps<Props>();
</script>

<template>
    <section class="py-16 bg-gradient-to-r from-primary to-primary/90">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="font-display text-4xl font-bold text-white sm:text-5xl mb-6">
                    {{ title }}
                </h1>
                <p class="font-body text-xl text-white/90 max-w-3xl mx-auto leading-relaxed">
                    {{ description }}
                </p>
            </div>
        </div>
    </section>
</template>