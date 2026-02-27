<template>
  <AppLayout>
    <div class="space-y-6">
      <h1 class="text-xl font-semibold text-slate-800 dark:text-slate-100">Import Holdings</h1>
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <CsvImport />
        <ManualAddForm />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '../components/layout/AppLayout.vue'
import CsvImport from '../components/import/CsvImport.vue'
import ManualAddForm from '../components/import/ManualAddForm.vue'
</script>
