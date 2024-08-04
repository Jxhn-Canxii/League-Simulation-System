<template>
    <button :type="type" class="rounded-md bg-rose-500 px-4 py-2 text-center text-sm text-white hover:bg-orange-600">
        <slot />
    </button>
</template>

<script setup>
defineProps({
    type: {
        type: String,
        default: 'submit',
    },
});
</script>
