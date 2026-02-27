<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-900">
    <SideBar />
    <main
      class="min-h-screen px-4 py-6 pt-16 transition-all duration-300 sm:px-6 lg:ml-16 lg:px-8 lg:pt-6"
    >
      <div class="mx-auto max-w-7xl">
        <slot />
      </div>
    </main>
  </div>
</template>

<script setup>
import SideBar from './SideBar.vue'
</script>
