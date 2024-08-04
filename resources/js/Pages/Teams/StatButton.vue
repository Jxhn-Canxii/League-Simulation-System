<template>
    <div :class="buttonClass" class="relative w-16 h-16 rounded-full shadow-lg flex flex-col items-center justify-center">
        <p class="text-white font-semibold">{{ value }}</p>
        <p class="text-xs text-white">{{ label }}</p>
    </div>
</template>

<script setup>
import { defineProps, computed } from 'vue';

const props = defineProps({
    value: {
        type: [Number, String],
        required: true,
    },
    label: {
        type: String,
        required: true,
    },
    color: {
        type: String,
        default: 'bg-blue-500', // Default color class
    },
});

const buttonClass = computed(() => {
    return `${props.color} hover:${props.color} active:${props.color}`;
});
</script>

<style scoped>
/* Add any necessary scoped styles here */
</style>
