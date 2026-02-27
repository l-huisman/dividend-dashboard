<template>
  <div class="rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Add Holding Manually</h3>

    <form @submit.prevent="handleSubmit" class="mt-4 space-y-4">
      <div>
        <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Stock</label>
        <select
          v-model="form.stock_id"
          class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100"
        >
          <option value="">Select a stock...</option>
          <option v-for="s in stocks.stocks" :key="s.id" :value="s.id">
            {{ s.ticker }} - {{ s.name }}
          </option>
        </select>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Shares</label>
          <input
            type="number"
            v-model.number="form.shares"
            step="0.0001"
            min="0"
            required
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm tabular-nums text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100"
          />
        </div>
        <div>
          <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Invested (USD)</label>
          <input
            type="number"
            v-model.number="form.invested"
            step="0.01"
            min="0"
            required
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm tabular-nums text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100"
          />
        </div>
      </div>

      <div>
        <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Date Bought</label>
        <input
          type="date"
          v-model="form.bought_on"
          class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100"
        />
      </div>

      <button
        type="submit"
        :disabled="submitting || !form.stock_id"
        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50 dark:bg-blue-500 dark:hover:bg-blue-600"
      >
        {{ submitting ? 'Adding...' : 'Add Holding' }}
      </button>

      <div
        v-if="success"
        class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-400"
      >
        {{ success }}
      </div>
      <div
        v-if="error"
        class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400"
      >
        {{ error }}
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { usePortfolioStore } from '../../stores/portfolio'
import { useStockStore } from '../../stores/stocks'

const portfolio = usePortfolioStore()
const stocks = useStockStore()

const submitting = ref(false)
const success = ref('')
const error = ref('')

const initialForm = {
  stock_id: '',
  shares: null,
  invested: null,
  bought_on: '',
}

const form = reactive({ ...initialForm })

function resetForm() {
  Object.assign(form, { ...initialForm })
}

async function handleSubmit() {
  if (!form.stock_id) return

  submitting.value = true
  success.value = ''
  error.value = ''

  try {
    await portfolio.createHolding({
      stock_id: form.stock_id,
      shares: form.shares,
      invested: form.invested,
      bought_on: form.bought_on || undefined,
    })
    success.value = 'Holding added successfully.'
    resetForm()
  } catch (e) {
    error.value = e.response?.data?.error || 'Failed to add holding.'
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  if (stocks.stocks.length === 0) {
    stocks.fetchStocks()
  }
})
</script>
