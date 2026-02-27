<template>
  <div v-if="message" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
    {{ message }}
  </div>
</template>

<script setup>
defineProps({
  message: { type: String, default: '' }
})
</script>
