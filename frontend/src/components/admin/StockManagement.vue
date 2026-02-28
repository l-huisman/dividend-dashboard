<template>
  <div class="space-y-6">
    <h1 class="text-xl font-semibold text-slate-800 dark:text-slate-100">Stock Management</h1>

    <!-- Search -->
    <div class="flex flex-wrap items-center justify-between gap-3">
      <input
        v-model="searchInput"
        @input="onSearch"
        type="text"
        placeholder="Search by ticker or name..."
        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 dark:placeholder-slate-500 sm:w-72"
      />
      <button
        @click="refreshAll"
        :disabled="refreshingAll"
        class="rounded-lg bg-amber-500 px-3 py-2 text-sm font-medium text-white hover:bg-amber-600 disabled:opacity-50"
      >
        {{ refreshingAll ? `Refreshing (${refreshProgress}/${stockStore.total})...` : 'Refresh All' }}
      </button>
    </div>

    <div v-if="stockStore.loading">
      <LoadingSpinner />
    </div>
    <div v-else-if="stockStore.error">
      <ErrorAlert :message="stockStore.error" />
    </div>
    <div v-else>
      <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800/50">
              <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-slate-400">Ticker</th>
              <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-slate-400">Name</th>
              <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-slate-400">Sector</th>
              <th class="px-4 py-3 text-right font-medium text-slate-500 dark:text-slate-400">Price</th>
              <th class="px-4 py-3 text-right font-medium text-slate-500 dark:text-slate-400">Yield</th>
              <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-slate-400">Holders</th>
              <th class="px-4 py-3 text-right font-medium text-slate-500 dark:text-slate-400">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="stock in stockStore.stocks"
              :key="stock.id"
              class="border-b border-slate-100 last:border-0 dark:border-slate-700/50"
            >
              <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-100">{{ stock.ticker }}</td>
              <td class="max-w-[200px] truncate px-4 py-3 text-slate-600 dark:text-slate-300">{{ stock.name }}</td>
              <td class="px-4 py-3 text-slate-500 dark:text-slate-400">{{ stock.sector || '—' }}</td>
              <td class="px-4 py-3 text-right text-slate-800 dark:text-slate-100">{{ formatEur(Number(stock.price) * USD_TO_EUR) }}</td>
              <td class="px-4 py-3 text-right text-slate-800 dark:text-slate-100">{{ (Number(stock.dividend_yield) * 100).toFixed(2) }}%</td>
              <td class="px-4 py-3 text-center">
                <span v-if="stock.holders > 0" class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                  {{ stock.holders }} {{ stock.holders === 1 ? 'user' : 'users' }}
                </span>
                <span v-else class="text-xs text-slate-400 dark:text-slate-500">—</span>
              </td>
              <td class="px-4 py-3 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button
                    @click="openEditModal(stock)"
                    class="rounded px-2 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/20"
                  >
                    Edit
                  </button>
                  <button
                    @click="refreshStock(stock)"
                    :disabled="refreshing === stock.id"
                    class="rounded px-2 py-1 text-xs font-medium text-amber-600 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-900/20 disabled:opacity-50"
                  >
                    {{ refreshing === stock.id ? 'Refreshing...' : 'Refresh' }}
                  </button>
                  <button
                    v-if="confirmDelete !== stock.id"
                    @click="confirmDelete = stock.id"
                    class="rounded px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                  >
                    Delete
                  </button>
                  <div v-else class="flex items-center gap-1">
                    <button
                      @click="deleteStock(stock.id)"
                      class="rounded bg-red-600 px-2 py-1 text-xs font-medium text-white hover:bg-red-700"
                    >
                      Confirm
                    </button>
                    <button
                      @click="confirmDelete = null"
                      class="rounded px-2 py-1 text-xs font-medium text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700"
                    >
                      Cancel
                    </button>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="mt-4 flex items-center justify-between">
        <p class="text-xs text-slate-500 dark:text-slate-400">{{ stockStore.total }} total stocks</p>
        <div v-if="totalPages > 1" class="flex items-center gap-1">
          <button
            v-for="p in totalPages"
            :key="p"
            @click="goToPage(p)"
            class="rounded px-2.5 py-1 text-xs font-medium"
            :class="p === stockStore.page
              ? 'bg-blue-600 text-white'
              : 'text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700'"
          >
            {{ p }}
          </button>
        </div>
      </div>
    </div>

    <!-- Edit Modal -->
    <div
      v-if="editStock"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
      @click.self="editStock = null"
    >
      <div class="mx-4 w-full max-w-lg rounded-lg border border-slate-200 bg-white p-6 shadow-xl dark:border-slate-700 dark:bg-slate-800">
        <h2 class="mb-4 text-lg font-semibold text-slate-800 dark:text-slate-100">Edit Stock</h2>

        <div v-if="editError">
          <ErrorAlert :message="editError" />
        </div>

        <form @submit.prevent="saveStock" class="mt-3 space-y-3">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-400">Ticker</label>
              <input v-model="editForm.ticker" type="text" class="w-full rounded border border-slate-300 bg-white px-2.5 py-1.5 text-sm text-slate-800 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100" />
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-400">Name</label>
              <input v-model="editForm.name" type="text" class="w-full rounded border border-slate-300 bg-white px-2.5 py-1.5 text-sm text-slate-800 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100" />
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-400">Sector</label>
              <input v-model="editForm.sector" type="text" class="w-full rounded border border-slate-300 bg-white px-2.5 py-1.5 text-sm text-slate-800 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100" />
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-400">Price</label>
              <input v-model.number="editForm.price" type="number" step="any" class="w-full rounded border border-slate-300 bg-white px-2.5 py-1.5 text-sm text-slate-800 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100" />
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-400">Dividend/Share</label>
              <input v-model.number="editForm.dividend_per_share" type="number" step="any" class="w-full rounded border border-slate-300 bg-white px-2.5 py-1.5 text-sm text-slate-800 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100" />
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-400">Dividend Yield (decimal)</label>
              <input v-model.number="editForm.dividend_yield" type="number" step="any" class="w-full rounded border border-slate-300 bg-white px-2.5 py-1.5 text-sm text-slate-800 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100" />
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-400">Ex-Dividend Date</label>
              <input v-model="editForm.ex_dividend_date" type="date" class="w-full rounded border border-slate-300 bg-white px-2.5 py-1.5 text-sm text-slate-800 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100" />
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-400">Pay Date</label>
              <input v-model="editForm.pay_date" type="date" class="w-full rounded border border-slate-300 bg-white px-2.5 py-1.5 text-sm text-slate-800 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100" />
            </div>
          </div>
          <div>
            <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-400">Frequency</label>
            <select v-model="editForm.frequency" class="w-full rounded border border-slate-300 bg-white px-2.5 py-1.5 text-sm text-slate-800 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100">
              <option value="monthly">Monthly</option>
              <option value="quarterly">Quarterly</option>
              <option value="semi-annual">Semi-Annual</option>
              <option value="annual">Annual</option>
            </select>
          </div>
          <div class="flex justify-end gap-2 pt-2">
            <button
              type="button"
              @click="editStock = null"
              class="rounded-lg px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
            >
              {{ saving ? 'Saving...' : 'Save' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../api/axios'
import { useStockStore } from '../../stores/stocks'
import { formatEur } from '../../utils/format'
import LoadingSpinner from '../shared/LoadingSpinner.vue'
import ErrorAlert from '../shared/ErrorAlert.vue'

const USD_TO_EUR = 1 / 1.18
const stockStore = useStockStore()

const searchInput = ref(stockStore.search)
const confirmDelete = ref(null)
const refreshing = ref(null)
const refreshingAll = ref(false)
const refreshProgress = ref(0)
const editStock = ref(null)
const editForm = ref({})
const editError = ref(null)
const saving = ref(false)

let searchTimeout = null

const totalPages = computed(() => Math.ceil(stockStore.total / stockStore.limit))

function onSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    stockStore.setSearch(searchInput.value)
    stockStore.fetchStocks()
  }, 300)
}

function goToPage(p) {
  stockStore.setPage(p)
  stockStore.fetchStocks()
}

function openEditModal(stock) {
  editStock.value = stock
  editError.value = null
  editForm.value = {
    ticker: stock.ticker,
    name: stock.name,
    sector: stock.sector,
    price: stock.price,
    dividend_per_share: stock.dividend_per_share,
    dividend_yield: stock.dividend_yield,
    ex_dividend_date: stock.ex_dividend_date || '',
    pay_date: stock.pay_date || '',
    frequency: stock.frequency,
  }
}

async function saveStock() {
  saving.value = true
  editError.value = null
  try {
    await api.put(`/stocks/${editStock.value.id}`, editForm.value)
    editStock.value = null
    stockStore.fetchStocks()
  } catch (err) {
    editError.value = err.response?.data?.error || 'Failed to update stock.'
  } finally {
    saving.value = false
  }
}

async function refreshStock(stock) {
  refreshing.value = stock.id
  try {
    await api.post(`/stocks/refresh/${stock.ticker}`)
    stockStore.fetchStocks()
  } catch (err) {
    stockStore.error = err.response?.data?.error || 'Failed to refresh stock.'
  } finally {
    refreshing.value = null
  }
}

async function deleteStock(id) {
  try {
    await api.delete(`/stocks/${id}`)
    confirmDelete.value = null
    stockStore.fetchStocks()
  } catch (err) {
    stockStore.error = err.response?.data?.error || 'Failed to delete stock.'
    confirmDelete.value = null
  }
}

async function refreshAll() {
  refreshingAll.value = true
  refreshProgress.value = 0

  // Fetch all stock tickers (get all pages)
  const tickers = []
  let p = 1
  while (true) {
    const { data } = await api.get('/stocks', { params: { page: p, limit: 100, sort: 'ticker', direction: 'asc' } })
    tickers.push(...data.data.map(s => s.ticker))
    if (tickers.length >= data.total) break
    p++
  }

  for (const ticker of tickers) {
    try {
      await api.post(`/stocks/refresh/${ticker}`)
    } catch {
      // skip failed refreshes
    }
    refreshProgress.value++
  }

  refreshingAll.value = false
  stockStore.fetchStocks()
}

onMounted(() => stockStore.fetchStocks())
</script>
