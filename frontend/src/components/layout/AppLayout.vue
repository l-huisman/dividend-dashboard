<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-900">
    <NavBar />
    <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
      <slot />
    </main>
  </div>
</template>

<script setup>
import NavBar from './NavBar.vue'
</script>
