<script setup lang="ts">
interface Props {
    title?: string;
    subtitle?: string;
}

defineProps<Props>();
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-secondary/20 dark:border-gray-700 overflow-hidden">
        <div v-if="title || subtitle" class="px-8 pt-8 pb-4 border-b border-secondary/10 dark:border-gray-700">
            <h2 v-if="title" class="font-display text-2xl font-semibold text-primary dark:text-gray-100 mb-2">
                {{ title }}
            </h2>
            <p v-if="subtitle" class="font-body text-primary/70 dark:text-gray-400">
                {{ subtitle }}
            </p>
        </div>
        
        <div class="p-8">
            <slot />
        </div>
    </div>
</template>