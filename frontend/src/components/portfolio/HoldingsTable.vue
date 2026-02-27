<template>
  <div>
    <div class="mb-4">
      <input
        v-model="search"
        type="text"
        placeholder="Search holdings..."
        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 dark:placeholder-slate-500 sm:max-w-xs"
      />
    </div>

    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800/50">
            <!-- Expand + actions column -->
            <th class="w-10 px-2 py-3"></th>
            <th
              v-for="col in columns"
              :key="col.key"
              @click="toggleSort(col.key)"
              class="cursor-pointer px-4 py-3 font-medium text-slate-500 dark:text-slate-400"
              :class="col.align === 'left' ? 'text-left' : 'text-right'"
            >
              <span class="inline-flex items-center gap-1">
                {{ col.label }}
                <ChevronUpIcon v-if="sortCol === col.key && sortDir === 'asc'" class="h-3.5 w-3.5" />
                <ChevronDownIcon v-else-if="sortCol === col.key && sortDir === 'desc'" class="h-3.5 w-3.5" />
              </span>
            </th>
            <!-- Sell column -->
            <th class="w-20 px-4 py-3"></th>
          </tr>
        </thead>
        <tbody>
          <template v-for="row in paginatedRows" :key="row.stockId">
            <!-- Aggregated row -->
            <tr class="border-b border-slate-100 dark:border-slate-700/50">
              <td class="px-2 py-3 text-center">
                <button
                  v-if="row.lots.length > 1"
                  @click="toggleExpand(row.stockId)"
                  class="inline-flex h-5 w-5 items-center justify-center rounded text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700 dark:hover:text-slate-300"
                >
                  <ChevronDownIcon
                    class="h-4 w-4 transition-transform"
                    :class="expanded[row.stockId] ? 'rotate-180' : ''"
                  />
                </button>
                <span
                  v-else
                  class="inline-block h-5 w-5"
                ></span>
              </td>
              <td
                v-for="col in columns"
                :key="col.key"
                class="whitespace-nowrap px-4 py-3 tabular-nums"
                :class="[
                  col.align === 'left' ? 'text-left' : 'text-right',
                  col.key === 'gain'
                    ? row.rawGain >= 0
                      ? 'text-emerald-500 dark:text-emerald-400'
                      : 'text-red-500 dark:text-red-400'
                    : 'text-slate-800 dark:text-slate-100',
                ]"
              >
                <span v-if="col.key === 'ticker'" class="font-medium">
                  {{ row[col.key] }}
                  <span
                    v-if="row.lots.length > 1"
                    class="ml-1 rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-normal text-slate-500 dark:bg-slate-700 dark:text-slate-400"
                  >
                    {{ row.lots.length }} lots
                  </span>
                </span>
                <span v-else>{{ row[col.key] }}</span>
              </td>
              <td class="px-4 py-3 text-right">
                <button
                  @click="openSellModal(row)"
                  class="rounded px-2.5 py-1 text-xs font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                >
                  Sell
                </button>
              </td>
            </tr>

            <!-- Expanded lot rows -->
            <template v-if="expanded[row.stockId] && row.lots.length > 1">
              <tr
                v-for="lot in row.lots"
                :key="lot.id"
                class="border-b border-slate-50 bg-slate-50/50 dark:border-slate-700/30 dark:bg-slate-800/30"
              >
                <td class="px-2 py-2"></td>
                <td
                  v-for="col in columns"
                  :key="col.key"
                  class="whitespace-nowrap px-4 py-2 tabular-nums text-xs"
                  :class="[
                    col.align === 'left' ? 'text-left' : 'text-right',
                    col.key === 'gain'
                      ? lot.rawGain >= 0
                        ? 'text-emerald-500 dark:text-emerald-400'
                        : 'text-red-500 dark:text-red-400'
                      : 'text-slate-500 dark:text-slate-400',
                  ]"
                >
                  <span v-if="col.key === 'ticker'" class="pl-4">
                    {{ lot.boughtOn }}
                  </span>
                  <span v-else>{{ lot[col.key] }}</span>
                </td>
                <td class="px-4 py-2 text-right">
                  <button
                    @click="openSellModal(row, lot)"
                    class="rounded px-2 py-0.5 text-[11px] font-medium text-red-500 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                  >
                    Sell
                  </button>
                </td>
              </tr>
            </template>
          </template>

          <tr v-if="sortedRows.length === 0">
            <td
              :colspan="columns.length + 2"
              class="px-4 py-8 text-center text-slate-500 dark:text-slate-400"
            >
              No holdings found.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
      <div class="flex items-center gap-2">
        <label class="text-xs text-slate-500 dark:text-slate-400">Per page</label>
        <select
          v-model.number="perPage"
          class="rounded border border-slate-300 bg-white px-2 py-1 text-xs dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100"
        >
          <option :value="10">10</option>
          <option :value="25">25</option>
          <option :value="50">50</option>
        </select>
      </div>
      <div class="flex items-center gap-1">
        <button
          :disabled="currentPage <= 1"
          @click="currentPage--"
          class="rounded px-2 py-1 text-xs font-medium text-slate-600 hover:bg-slate-100 disabled:opacity-40 dark:text-slate-300 dark:hover:bg-slate-700"
        >
          Prev
        </button>
        <span class="px-2 text-xs tabular-nums text-slate-500 dark:text-slate-400">
          {{ (currentPage - 1) * perPage + 1 }}-{{ Math.min(currentPage * perPage, sortedRows.length) }}
          of {{ sortedRows.length }}
        </span>
        <button
          :disabled="currentPage >= totalPages"
          @click="currentPage++"
          class="rounded px-2 py-1 text-xs font-medium text-slate-600 hover:bg-slate-100 disabled:opacity-40 dark:text-slate-300 dark:hover:bg-slate-700"
        >
          Next
        </button>
      </div>
    </div>

    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
      {{ sortedRows.length }} unique stocks · {{ holdings.length }} total lots
    </p>

    <!-- Sell Modal -->
    <div
      v-if="sellModal.open"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
      @click.self="closeSellModal"
    >
      <div class="w-full max-w-sm rounded-lg border border-slate-200 bg-white p-5 shadow-xl dark:border-slate-700 dark:bg-slate-800">
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">
          Sell {{ sellModal.ticker }}
        </h3>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
          You own {{ formatNumber(sellModal.maxShares, 4) }} shares
          <span v-if="sellModal.lotDate"> (lot: {{ sellModal.lotDate }})</span>
        </p>

        <div class="mt-4 space-y-3">
          <div>
            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">
              Shares to sell
            </label>
            <input
              v-model.number="sellModal.shares"
              type="number"
              step="any"
              min="0"
              :max="sellModal.maxShares"
              class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm tabular-nums text-slate-800 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100"
            />
          </div>

          <div class="flex gap-2">
            <button
              @click="sellModal.shares = sellModal.maxShares"
              class="rounded bg-slate-100 px-2 py-1 text-xs text-slate-600 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600"
            >
              Sell all
            </button>
            <button
              @click="sellModal.shares = +(sellModal.maxShares / 2).toFixed(4)"
              class="rounded bg-slate-100 px-2 py-1 text-xs text-slate-600 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600"
            >
              Half
            </button>
            <button
              @click="sellModal.shares = +(sellModal.maxShares / 4).toFixed(4)"
              class="rounded bg-slate-100 px-2 py-1 text-xs text-slate-600 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600"
            >
              Quarter
            </button>
          </div>

          <div v-if="sellModal.shares > 0" class="rounded-lg bg-red-50 p-3 dark:bg-red-900/10">
            <p class="text-xs text-red-700 dark:text-red-400">
              Selling {{ formatNumber(sellModal.shares, 4) }} of {{ formatNumber(sellModal.maxShares, 4) }} shares
              <span v-if="sellModal.shares >= sellModal.maxShares"> (full position)</span>
            </p>
          </div>

          <div v-if="sellModal.error" class="rounded-lg bg-red-50 p-3 dark:bg-red-900/10">
            <p class="text-xs text-red-600 dark:text-red-400">{{ sellModal.error }}</p>
          </div>

          <div class="flex items-center justify-end gap-2 pt-2">
            <button
              @click="closeSellModal"
              class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700"
            >
              Cancel
            </button>
            <button
              @click="executeSell"
              :disabled="sellModal.selling || sellModal.shares <= 0 || sellModal.shares > sellModal.maxShares"
              class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50 dark:bg-red-500 dark:hover:bg-red-600"
            >
              {{ sellModal.selling ? 'Selling...' : 'Confirm Sell' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { ChevronUpIcon, ChevronDownIcon } from '@heroicons/vue/20/solid'
import { formatEur, formatPct, formatNumber } from '../../utils/format'
import { usePortfolioStore } from '../../stores/portfolio'

const USD_TO_EUR = 1 / 1.18

const props = defineProps({
  holdings: {
    type: Array,
    required: true,
  },
  columns: {
    type: Array,
    required: true,
  },
})

const portfolio = usePortfolioStore()

const search = ref('')
const sortCol = ref('ticker')
const sortDir = ref('asc')
const expanded = reactive({})

const perPage = ref(10)
const currentPage = ref(1)

const totalPages = computed(() => Math.ceil(sortedRows.value.length / perPage.value))

const paginatedRows = computed(() => {
  const start = (currentPage.value - 1) * perPage.value
  return sortedRows.value.slice(start, start + perPage.value)
})

watch([search, sortCol, sortDir], () => {
  currentPage.value = 1
})

watch(perPage, () => {
  currentPage.value = 1
})

function toggleSort(key) {
  if (sortCol.value === key) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortCol.value = key
    sortDir.value = 'asc'
  }
}

function toggleExpand(stockId) {
  expanded[stockId] = !expanded[stockId]
}

function formatDate(dateStr) {
  if (!dateStr) return '--'
  const date = new Date(dateStr)
  if (isNaN(date.getTime())) return '--'
  return date.toLocaleDateString('nl-NL', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

function buildLot(h) {
  const rawValue = h.shares * h.stock.price * USD_TO_EUR
  const rawInvested = h.invested * USD_TO_EUR
  const rawGain = rawValue - rawInvested
  const rawYield = h.stock.dividend_yield || 0
  const rawAnnualDiv = h.shares * h.stock.dividend_per_share * USD_TO_EUR

  return {
    id: h.id,
    ticker: h.stock.ticker,
    name: h.stock.name,
    shares: formatNumber(h.shares, 4),
    value: formatEur(rawValue),
    invested: formatEur(rawInvested),
    gain: formatEur(rawGain),
    yield: formatPct(rawYield),
    annualDiv: formatEur(rawAnnualDiv),
    exDiv: formatDate(h.stock.ex_dividend_date),
    sector: h.stock.sector || '--',
    boughtOn: formatDate(h.bought_on),
    rawValue,
    rawInvested,
    rawGain,
    rawYield,
    rawAnnualDiv,
    rawShares: h.shares,
    rawExDiv: h.stock.ex_dividend_date || '',
  }
}

// Group holdings by stock_id and aggregate
const rows = computed(() => {
  const grouped = {}

  for (const h of props.holdings) {
    const sid = h.stock_id
    if (!grouped[sid]) {
      grouped[sid] = {
        stockId: sid,
        stock: h.stock,
        holdings: [],
      }
    }
    grouped[sid].holdings.push(h)
  }

  return Object.values(grouped).map((group) => {
    const lots = group.holdings.map(buildLot)
    const totalShares = group.holdings.reduce((s, h) => s + h.shares, 0)
    const totalInvestedUsd = group.holdings.reduce((s, h) => s + h.invested, 0)
    const rawValue = totalShares * group.stock.price * USD_TO_EUR
    const rawInvested = totalInvestedUsd * USD_TO_EUR
    const rawGain = rawValue - rawInvested
    const rawYield = group.stock.dividend_yield || 0
    const rawAnnualDiv = totalShares * group.stock.dividend_per_share * USD_TO_EUR

    return {
      stockId: group.stockId,
      ticker: group.stock.ticker,
      name: group.stock.name,
      shares: formatNumber(totalShares, 4),
      value: formatEur(rawValue),
      invested: formatEur(rawInvested),
      gain: formatEur(rawGain),
      yield: formatPct(rawYield),
      annualDiv: formatEur(rawAnnualDiv),
      exDiv: formatDate(group.stock.ex_dividend_date),
      sector: group.stock.sector || '--',
      rawValue,
      rawInvested,
      rawGain,
      rawYield,
      rawAnnualDiv,
      rawShares: totalShares,
      rawExDiv: group.stock.ex_dividend_date || '',
      lots,
      holdingIds: group.holdings.map((h) => h.id),
    }
  })
})

const filteredRows = computed(() => {
  const term = search.value.toLowerCase().trim()
  if (!term) return rows.value
  return rows.value.filter(
    (row) =>
      row.ticker.toLowerCase().includes(term) ||
      row.name.toLowerCase().includes(term),
  )
})

const RAW_KEY_MAP = {
  ticker: 'ticker',
  name: 'name',
  shares: 'rawShares',
  value: 'rawValue',
  invested: 'rawInvested',
  gain: 'rawGain',
  yield: 'rawYield',
  annualDiv: 'rawAnnualDiv',
  exDiv: 'rawExDiv',
  sector: 'sector',
}

const sortedRows = computed(() => {
  const data = [...filteredRows.value]
  const rawKey = RAW_KEY_MAP[sortCol.value] || sortCol.value
  const dir = sortDir.value === 'asc' ? 1 : -1

  data.sort((a, b) => {
    const aVal = a[rawKey]
    const bVal = b[rawKey]

    if (typeof aVal === 'number' && typeof bVal === 'number') {
      return (aVal - bVal) * dir
    }

    const aStr = String(aVal).toLowerCase()
    const bStr = String(bVal).toLowerCase()
    if (aStr < bStr) return -1 * dir
    if (aStr > bStr) return 1 * dir
    return 0
  })

  return data
})

// ── Sell Modal ──
const sellModal = reactive({
  open: false,
  ticker: '',
  maxShares: 0,
  shares: 0,
  selling: false,
  error: '',
  // targets: which holdings to sell from (FIFO order)
  targets: [],
  lotDate: '',
})

function openSellModal(row, lot = null) {
  sellModal.error = ''
  sellModal.selling = false
  sellModal.ticker = row.ticker

  if (lot) {
    // Sell from a specific lot
    const h = props.holdings.find((h) => h.id === lot.id)
    sellModal.maxShares = h.shares
    sellModal.targets = [h]
    sellModal.lotDate = lot.boughtOn
  } else {
    // Sell from the aggregated position (FIFO: oldest first)
    const stockHoldings = props.holdings
      .filter((h) => h.stock_id === row.stockId)
      .sort((a, b) => (a.bought_on || '').localeCompare(b.bought_on || ''))
    sellModal.maxShares = stockHoldings.reduce((s, h) => s + h.shares, 0)
    sellModal.targets = stockHoldings
    sellModal.lotDate = ''
  }

  sellModal.shares = 0
  sellModal.open = true
}

function closeSellModal() {
  sellModal.open = false
}

async function executeSell() {
  if (sellModal.shares <= 0 || sellModal.shares > sellModal.maxShares) return

  sellModal.selling = true
  sellModal.error = ''

  let remaining = sellModal.shares

  try {
    for (const h of sellModal.targets) {
      if (remaining <= 0) break

      const toSell = Math.min(remaining, h.shares)
      await portfolio.sellHolding(h.id, toSell)
      remaining -= toSell
    }

    closeSellModal()
  } catch (e) {
    sellModal.error = e.response?.data?.error || 'Failed to sell shares'
  } finally {
    sellModal.selling = false
  }
}
</script>
