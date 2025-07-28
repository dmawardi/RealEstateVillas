<script setup lang="ts">
interface Props {
    modelValue?: string | number;
    type?: string;
    placeholder?: string;
    disabled?: boolean;
    class?: string;
}

interface Emits {
    (e: 'update:modelValue', value: string | number): void;
}

const props = withDefaults(defineProps<Props>(), {
    type: 'text',
    placeholder: '',
    disabled: false,
    class: '',
});

const emit = defineEmits<Emits>();

const handleInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const value = props.type === 'number' ? Number(target.value) : target.value;
    emit('update:modelValue', value);
};

// Base classes that can be extended
const baseClasses = 'w-full px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50 disabled:cursor-not-allowed';
</script>

<template>
    <input
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :class="[baseClasses, props.class]"
        @input="handleInput"
        v-bind="$attrs"
    />
</template>