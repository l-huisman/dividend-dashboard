<template>
  <div class="rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Import CSV</h3>
    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
      Upload a Trading 212 CSV export to import holdings.
    </p>

    <div
      @dragover.prevent="dragging = true"
      @dragleave="dragging = false"
      @drop.prevent="handleDrop"
      @click="$refs.fileInput.click()"
      class="mt-4 flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed p-8 transition-colors"
      :class="
        dragging
          ? 'border-blue-400 bg-blue-50 dark:border-blue-500 dark:bg-blue-900/20'
          : 'border-slate-300 bg-slate-50 hover:border-slate-400 dark:border-slate-600 dark:bg-slate-700/50 dark:hover:border-slate-500'
      "
    >
      <ArrowUpTrayIcon class="h-8 w-8 text-slate-400 dark:text-slate-500" />
      <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
        {{ file ? file.name : 'Drop CSV file here or click to browse' }}
      </p>
      <input
        ref="fileInput"
        type="file"
        accept=".csv"
        class="hidden"
        @change="handleFileSelect"
      />
    </div>

    <div v-if="file" class="mt-4 flex items-center gap-3">
      <button
        @click="importFile"
        :disabled="importing"
        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50 dark:bg-blue-500 dark:hover:bg-blue-600"
      >
        {{ importing ? 'Importing...' : 'Import' }}
      </button>
      <button
        @click="clearFile"
        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700"
      >
        Clear
      </button>
    </div>

    <div
      v-if="success"
      class="mt-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-400"
    >
      {{ success }}
    </div>
    <div
      v-if="error"
      class="mt-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400"
    >
      {{ error }}
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { ArrowUpTrayIcon } from '@heroicons/vue/24/outline'
import { usePortfolioStore } from '../../stores/portfolio'

const portfolio = usePortfolioStore()

const file = ref(null)
const dragging = ref(false)
const importing = ref(false)
const success = ref('')
const error = ref('')

function clearMessages() {
  success.value = ''
  error.value = ''
}

function handleFileSelect(event) {
  clearMessages()
  const selected = event.target.files[0]
  if (selected && selected.name.endsWith('.csv')) {
    file.value = selected
  }
}

function handleDrop(event) {
  dragging.value = false
  clearMessages()
  const dropped = event.dataTransfer.files[0]
  if (dropped && dropped.name.endsWith('.csv')) {
    file.value = dropped
  }
}

function clearFile() {
  file.value = null
  clearMessages()
}

async function importFile() {
  if (!file.value) return

  importing.value = true
  clearMessages()

  try {
    const csvText = await readFileAsText(file.value)
    const result = await portfolio.importCsv(csvText)
    success.value = result?.message || 'CSV imported successfully.'
    file.value = null
  } catch (e) {
    error.value = e.response?.data?.error || 'Failed to import CSV. Please check the file format.'
  } finally {
    importing.value = false
  }
}

function readFileAsText(f) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader()
    reader.onload = () => resolve(reader.result)
    reader.onerror = () => reject(new Error('Failed to read file.'))
    reader.readAsText(f)
  })
}
</script>
