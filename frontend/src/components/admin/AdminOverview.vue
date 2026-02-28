<template>
  <div class="space-y-6">
    <h1 class="text-xl font-semibold text-slate-800 dark:text-slate-100">Admin Overview</h1>

    <div v-if="loading">
      <LoadingSpinner />
    </div>
    <div v-else-if="error">
      <ErrorAlert :message="error" />
    </div>
    <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-3">
      <div
        v-for="card in cards"
        :key="card.label"
        class="rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800"
      >
        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ card.label }}</p>
        <p class="mt-1 text-2xl font-semibold text-slate-800 dark:text-slate-100">{{ card.value }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../api/axios'
import LoadingSpinner from '../shared/LoadingSpinner.vue'
import ErrorAlert from '../shared/ErrorAlert.vue'

const stats = ref(null)
const loading = ref(false)
const error = ref(null)

const cards = computed(() => {
  if (!stats.value) return []
  return [
    { label: 'Total Users', value: stats.value.total_users },
    { label: 'Admins', value: stats.value.total_admins },
    { label: 'Total Stocks', value: stats.value.total_stocks },
  ]
})

async function fetchStats() {
  loading.value = true
  error.value = null
  try {
    const { data } = await api.get('/admin/stats')
    stats.value = data
  } catch (err) {
    error.value = err.response?.data?.error || 'Failed to load stats.'
  } finally {
    loading.value = false
  }
}

onMounted(fetchStats)
</script>
