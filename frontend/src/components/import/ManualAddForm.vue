<template>
  <div class="rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Add Holding Manually</h3>
    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
      Select an existing stock or type to create a new one
    </p>

    <form @submit.prevent="handleSubmit" class="mt-4 space-y-4">
      <!-- Stock search / select -->
      <div class="relative" ref="comboWrapper">
        <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Stock</label>
        <input
          v-model="searchQuery"
          @focus="dropdownOpen = true"
          @input="dropdownOpen = true"
          type="text"
          placeholder="Search or type a new ticker..."
          autocomplete="off"
          class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100"
        />

        <!-- Selected stock badge -->
        <div
          v-if="selectedStock && !dropdownOpen"
          class="mt-1 flex items-center gap-2"
        >
          <span class="rounded bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
            {{ selectedStock.ticker }}
          </span>
          <span class="text-xs text-slate-500 dark:text-slate-400">{{ selectedStock.name }}</span>
          <button
            type="button"
            @click="clearSelection"
            class="text-xs text-slate-400 hover:text-red-500"
          >
            &#x2715;
          </button>
        </div>

        <!-- Dropdown -->
        <div
          v-if="dropdownOpen && searchQuery.length > 0"
          class="absolute z-20 mt-1 max-h-48 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-lg dark:border-slate-600 dark:bg-slate-700"
        >
          <button
            v-for="s in filteredStocks"
            :key="s.id"
            type="button"
            @click="selectStock(s)"
            class="flex w-full items-center justify-between px-3 py-2 text-left text-sm hover:bg-slate-50 dark:hover:bg-slate-600"
          >
            <div>
              <span class="font-medium text-slate-800 dark:text-slate-100">{{ s.ticker }}</span>
              <span class="ml-2 text-slate-500 dark:text-slate-400">{{ s.name }}</span>
            </div>
            <span class="text-xs text-slate-400 dark:text-slate-500">{{ s.sector }}</span>
          </button>

          <!-- Create new option -->
          <button
            type="button"
            @click="startCreating"
            class="flex w-full items-center gap-2 border-t border-slate-100 px-3 py-2 text-left text-sm text-blue-600 hover:bg-blue-50 dark:border-slate-600 dark:text-blue-400 dark:hover:bg-slate-600"
          >
            <span class="text-lg leading-none">+</span>
            <span>Add new stock "{{ searchQuery.toUpperCase() }}"</span>
          </button>
        </div>
      </div>

      <!-- New stock fields (only when creating) -->
      <div v-if="creatingNew" class="space-y-3 rounded-lg border border-blue-200 bg-blue-50/50 p-4 dark:border-blue-800 dark:bg-blue-900/10">
        <div class="flex items-center justify-between">
          <p class="text-xs font-medium text-blue-700 dark:text-blue-400">New stock details</p>
          <span v-if="lookingUp" class="text-xs text-blue-500 dark:text-blue-400">
            Fetching from Yahoo Finance...
          </span>
          <span v-else-if="lookupDone && !lookupFailed" class="text-xs text-emerald-600 dark:text-emerald-400">
            ✓ Auto-filled from Yahoo Finance
          </span>
          <span v-else-if="lookupFailed" class="text-xs text-amber-600 dark:text-amber-400">
            Ticker not found — fill in manually
          </span>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Ticker</label>
            <input
              v-model="newStock.ticker"
              type="text"
              required
              class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm uppercase text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100"
            />
          </div>
          <div>
            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Name</label>
            <input
              v-model="newStock.name"
              type="text"
              required
              class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100"
            />
          </div>
          <div>
            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Price (USD)</label>
            <input
              v-model.number="newStock.price"
              type="number"
              step="0.01"
              min="0"
              required
              class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm tabular-nums text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100"
            />
          </div>
          <div>
            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Annual Dividend (USD)</label>
            <input
              v-model.number="newStock.dividend_per_share"
              type="number"
              step="0.01"
              min="0"
              required
              class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm tabular-nums text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100"
            />
          </div>
        </div>
        <div>
          <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Sector</label>
          <select
            v-model="newStock.sector"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100"
          >
            <option value="">Select sector...</option>
            <option v-for="sec in sectors" :key="sec" :value="sec">{{ sec }}</option>
          </select>
        </div>
      </div>

      <!-- Holding fields -->
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
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
          class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 dark:[color-scheme:dark]"
        />
      </div>

      <button
        type="submit"
        :disabled="submitting || (!selectedStock && !creatingNew)"
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
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import api from '../../api/axios'
import { usePortfolioStore } from '../../stores/portfolio'
import { useStockStore } from '../../stores/stocks'

const portfolio = usePortfolioStore()
const stocks = useStockStore()

const submitting = ref(false)
const success = ref('')
const error = ref('')

const searchQuery = ref('')
const dropdownOpen = ref(false)
const selectedStock = ref(null)
const creatingNew = ref(false)
const lookingUp = ref(false)
const lookupDone = ref(false)
const lookupFailed = ref(false)
const comboWrapper = ref(null)

const sectors = [
  'Technology', 'Real Estate', 'Healthcare', 'Energy',
  'Consumer Defensive', 'Consumer Cyclical', 'Financial Services',
  'Industrials', 'Basic Materials', 'Utilities',
]

const newStock = reactive({
  ticker: '',
  name: '',
  price: null,
  dividend_per_share: null,
  sector: '',
})

const form = reactive({
  shares: null,
  invested: null,
  bought_on: '',
})

const filteredStocks = computed(() => {
  const q = searchQuery.value.toLowerCase().trim()
  if (!q) return []
  return stocks.stocks.filter(
    (s) => s.ticker.toLowerCase().includes(q) || s.name.toLowerCase().includes(q),
  ).slice(0, 8)
})

function selectStock(s) {
  selectedStock.value = s
  searchQuery.value = s.ticker
  dropdownOpen.value = false
  creatingNew.value = false
}

async function startCreating() {
  creatingNew.value = true
  selectedStock.value = null
  lookupDone.value = false
  lookupFailed.value = false

  const ticker = searchQuery.value.toUpperCase()
  newStock.ticker = ticker
  newStock.name = ''
  newStock.price = null
  newStock.dividend_per_share = null
  newStock.sector = ''
  dropdownOpen.value = false

  // Auto-fetch from Yahoo Finance
  lookingUp.value = true
  try {
    const { data } = await api.get(`/stocks/lookup/${encodeURIComponent(ticker)}`)
    newStock.name = data.name || ''
    newStock.price = data.price || null
    newStock.dividend_per_share = data.dividend_per_share || null
    newStock.sector = data.sector || ''
    lookupDone.value = true
  } catch {
    lookupFailed.value = true
  } finally {
    lookingUp.value = false
  }
}

function clearSelection() {
  selectedStock.value = null
  searchQuery.value = ''
  creatingNew.value = false
  lookupDone.value = false
  lookupFailed.value = false
}

function resetForm() {
  selectedStock.value = null
  searchQuery.value = ''
  creatingNew.value = false
  lookupDone.value = false
  lookupFailed.value = false
  Object.assign(newStock, { ticker: '', name: '', price: null, dividend_per_share: null, sector: '' })
  Object.assign(form, { shares: null, invested: null, bought_on: '' })
}

async function handleSubmit() {
  submitting.value = true
  success.value = ''
  error.value = ''

  try {
    let stockId

    if (creatingNew.value) {
      const { data: created } = await api.post('/stocks', {
        ticker: newStock.ticker,
        name: newStock.name,
        price: newStock.price,
        dividend_per_share: newStock.dividend_per_share,
        sector: newStock.sector,
      })
      stockId = created.id
      await stocks.fetchStocks()
    } else if (selectedStock.value) {
      stockId = selectedStock.value.id
    } else {
      error.value = 'Please select or create a stock.'
      submitting.value = false
      return
    }

    await portfolio.createHolding({
      stock_id: stockId,
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

function handleClickOutside(e) {
  if (comboWrapper.value && !comboWrapper.value.contains(e.target)) {
    dropdownOpen.value = false
  }
}

onMounted(() => {
  if (stocks.stocks.length === 0) {
    stocks.fetchStocks()
  }
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
