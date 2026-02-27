<template>
  <AppLayout>
    <div class="space-y-6">
      <h1 class="text-xl font-semibold text-slate-800 dark:text-slate-100">User Management</h1>
      <UserManagement />
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '../components/layout/AppLayout.vue'
import UserManagement from '../components/admin/UserManagement.vue'
</script>
