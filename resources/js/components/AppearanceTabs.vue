<script setup lang="ts">
import { Sun } from 'lucide-vue-next';
</script>

<template>
    <div class="inline-flex gap-1 rounded-lg bg-neutral-100 p-1">
        <div class="flex items-center rounded-md px-3.5 py-1.5 bg-white shadow-xs">
            <Sun class="-ml-1 h-4 w-4" />
            <span class="ml-1.5 text-sm">Light Mode Only</span>
        </div>
    </div>
    <p class="mt-3 text-sm text-neutral-600">
        This application is configured to use light mode only for the best user experience.
    </p>
</template>
