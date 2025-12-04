<script setup lang="ts">
import { ref, computed, watch, nextTick, onMounted } from 'vue';
import { Eye, EyeOff, Bold, Italic, List, ListOrdered, Link2, Quote } from 'lucide-vue-next';

interface Props {
    modelValue: string;
    placeholder?: string;
    showPreview?: boolean;
    minHeight?: string;
    maxHeight?: string;
    readonly?: boolean;
    errors?: string;
    label?: string;
}

interface Emits {
    (e: 'update:modelValue', value: string): void;
}

const props = withDefaults(defineProps<Props>(), {
    placeholder: 'Enter your text here...',
    showPreview: true,
    minHeight: '200px',
    maxHeight: '400px',
    readonly: false,
    label: 'Content'
});

const emit = defineEmits<Emits>();

// Component state
const editorRef = ref<HTMLTextAreaElement>();
const isPreviewMode = ref(false);
const textareaValue = ref(props.modelValue);

// Character count
const characterCount = computed(() => textareaValue.value.length);

// Simple markdown-style formatting functions
const insertFormatting = (before: string, after: string = '') => {
    if (!editorRef.value) return;
    
    const start = editorRef.value.selectionStart;
    const end = editorRef.value.selectionEnd;
    const selectedText = textareaValue.value.substring(start, end);
    
    const newText = 
        textareaValue.value.substring(0, start) + 
        before + selectedText + after + 
        textareaValue.value.substring(end);
    
    textareaValue.value = newText;
    emit('update:modelValue', newText);
    
    // Restore cursor position
    nextTick(() => {
        if (editorRef.value) {
            const newPos = start + before.length + selectedText.length + after.length;
            editorRef.value.focus();
            editorRef.value.setSelectionRange(newPos, newPos);
        }
    });
};

const insertAtCursor = (text: string) => {
    if (!editorRef.value) return;
    
    const start = editorRef.value.selectionStart;
    const end = editorRef.value.selectionEnd;
    
    const newText = 
        textareaValue.value.substring(0, start) + 
        text + 
        textareaValue.value.substring(end);
    
    textareaValue.value = newText;
    emit('update:modelValue', newText);
    
    // Position cursor after inserted text
    nextTick(() => {
        if (editorRef.value) {
            const newPos = start + text.length;
            editorRef.value.focus();
            editorRef.value.setSelectionRange(newPos, newPos);
        }
    });
};

// Toolbar actions
const makeBold = () => insertFormatting('**', '**');
const makeItalic = () => insertFormatting('*', '*');
const insertBulletList = () => {
    const start = editorRef.value?.selectionStart || 0;
    const lineStart = textareaValue.value.lastIndexOf('\n', start - 1) + 1;
    const needsNewline = lineStart > 0 && textareaValue.value.substring(lineStart, start).trim() !== '';
    insertAtCursor(needsNewline ? '\n- ' : '- ');
};
const insertNumberedList = () => {
    const start = editorRef.value?.selectionStart || 0;
    const lineStart = textareaValue.value.lastIndexOf('\n', start - 1) + 1;
    const needsNewline = lineStart > 0 && textareaValue.value.substring(lineStart, start).trim() !== '';
    insertAtCursor(needsNewline ? '\n1. ' : '1. ');
};
const insertQuote = () => {
    const start = editorRef.value?.selectionStart || 0;
    const lineStart = textareaValue.value.lastIndexOf('\n', start - 1) + 1;
    const needsNewline = lineStart > 0 && textareaValue.value.substring(lineStart, start).trim() !== '';
    insertAtCursor(needsNewline ? '\n> ' : '> ');
};
const insertLink = () => {
    const url = prompt('Enter URL:');
    if (url) {
        if (editorRef.value) {
            const start = editorRef.value.selectionStart;
            const end = editorRef.value.selectionEnd;
            const selectedText = textareaValue.value.substring(start, end);
            const linkText = selectedText || 'Link text';
            insertFormatting(`[${linkText}](`, ')');
        }
    }
};

// Handle keyboard shortcuts
const onKeydown = (event: KeyboardEvent) => {
    if (event.ctrlKey || event.metaKey) {
        switch (event.key.toLowerCase()) {
            case 'b':
                event.preventDefault();
                makeBold();
                break;
            case 'i':
                event.preventDefault();
                makeItalic();
                break;
        }
    }
};

// Handle input changes
const onInput = () => {
    emit('update:modelValue', textareaValue.value);
};

// Toggle preview mode
const togglePreview = () => {
    isPreviewMode.value = !isPreviewMode.value;
};

// Watch for external changes
watch(() => props.modelValue, (newValue) => {
    if (textareaValue.value !== newValue) {
        textareaValue.value = newValue;
    }
});

// Initialize
onMounted(() => {
    textareaValue.value = props.modelValue;
});
</script>

<template>
    <div class="rich-text-editor">
        <!-- Header with preview toggle -->
        <div v-if="showPreview" class="flex items-center justify-between mb-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ label }} <span class="text-red-500">*</span>
            </label>
            <button
                type="button"
                @click="togglePreview"
                class="flex items-center gap-2 px-3 py-1 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-colors"
            >
                <Eye v-if="!isPreviewMode" class="w-4 h-4" />
                <EyeOff v-else class="w-4 h-4" />
                {{ isPreviewMode ? 'Edit' : 'Preview' }}
            </button>
        </div>
        
        <!-- Single label when no preview toggle -->
        <label v-else class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ label }} <span class="text-red-500">*</span>
        </label>

        <div
            :class="[
                'border rounded-lg overflow-hidden bg-white dark:bg-gray-800 transition-colors',
                errors ? 'border-red-300 focus-within:border-red-500 focus-within:ring-red-500' : 'border-gray-300 dark:border-gray-600 focus-within:border-blue-500 focus-within:ring-blue-500',
                readonly ? 'bg-gray-50 dark:bg-gray-700' : ''
            ]"
        >
            <!-- Simple Toolbar -->
            <div v-if="!isPreviewMode && !readonly" class="border-b border-gray-200 dark:border-gray-600 p-2">
                <div class="flex items-center gap-1">
                    <button
                        type="button"
                        @click="makeBold"
                        class="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-gray-600 dark:text-gray-400"
                        title="Bold (**text**)"
                    >
                        <Bold class="w-4 h-4" />
                    </button>
                    <button
                        type="button"
                        @click="makeItalic"
                        class="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-gray-600 dark:text-gray-400"
                        title="Italic (*text*)"
                    >
                        <Italic class="w-4 h-4" />
                    </button>
                    
                    <div class="w-px h-6 bg-gray-200 dark:bg-gray-600 mx-1"></div>
                    
                    <button
                        type="button"
                        @click="insertBulletList"
                        class="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-gray-600 dark:text-gray-400"
                        title="Bullet List (- item)"
                    >
                        <List class="w-4 h-4" />
                    </button>
                    <button
                        type="button"
                        @click="insertNumberedList"
                        class="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-gray-600 dark:text-gray-400"
                        title="Numbered List (1. item)"
                    >
                        <ListOrdered class="w-4 h-4" />
                    </button>
                    
                    <div class="w-px h-6 bg-gray-200 dark:bg-gray-600 mx-1"></div>
                    
                    <button
                        type="button"
                        @click="insertQuote"
                        class="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-gray-600 dark:text-gray-400"
                        title="Quote (> text)"
                    >
                        <Quote class="w-4 h-4" />
                    </button>
                    <button
                        type="button"
                        @click="insertLink"
                        class="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-gray-600 dark:text-gray-400"
                        title="Link ([text](url))"
                    >
                        <Link2 class="w-4 h-4" />
                    </button>
                </div>
            </div>

            <!-- Editor Area -->
            <div class="relative">
                <!-- Textarea Editor -->
                <textarea
                    v-show="!isPreviewMode"
                    ref="editorRef"
                    v-model="textareaValue"
                    :placeholder="placeholder"
                    :readonly="readonly"
                    :style="{ minHeight: minHeight, maxHeight: maxHeight }"
                    class="w-full p-4 border-0 focus:outline-none resize-none bg-transparent text-gray-900 dark:text-gray-100"
                    @input="onInput"
                    @keydown="onKeydown"
                ></textarea>

                <!-- Preview -->
                <div
                    v-show="isPreviewMode"
                    :class="[
                        'p-4 overflow-y-auto prose prose-sm max-w-none dark:prose-invert',
                        'prose-headings:font-semibold prose-a:text-blue-600 dark:prose-a:text-blue-400',
                        'prose-blockquote:border-l-4 prose-blockquote:border-gray-300 dark:prose-blockquote:border-gray-600',
                        'bg-gray-50 dark:bg-gray-700 [&_p]:mb-4 [&_h2]:text-lg [&_h2]:font-semibold'
                    ]"
                    :style="{ minHeight: minHeight, maxHeight: maxHeight }"
                    v-html="textareaValue"
                ></div>
            </div>
        </div>

        <!-- Footer with character count and error -->
        <div class="mt-2 flex justify-between items-start">
            <div class="flex-1">
                <p v-if="errors" class="text-sm text-red-600 dark:text-red-400">
                    {{ errors }}
                </p>
                <p v-else class="text-xs text-gray-500 dark:text-gray-400">
                    Use **bold**, *italic*, - lists, > quotes, and [links](url) for formatting.
                </p>
            </div>
            <span class="text-xs text-gray-400 ml-4 flex-shrink-0">
                {{ characterCount }} characters
            </span>
        </div>
    </div>
</template>