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
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="row in sortedRows"
            :key="row.id"
            class="border-b border-slate-100 last:border-0 dark:border-slate-700/50"
          >
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
              {{ row[col.key] }}
            </td>
          </tr>
          <tr v-if="sortedRows.length === 0">
            <td
              :colspan="columns.length"
              class="px-4 py-8 text-center text-slate-500 dark:text-slate-400"
            >
              No holdings found.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
      {{ sortedRows.length }} of {{ holdings.length }} holdings
    </p>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { ChevronUpIcon, ChevronDownIcon } from '@heroicons/vue/20/solid'
import { formatEur, formatPct, formatNumber } from '../../utils/format'

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

const search = ref('')
const sortCol = ref('ticker')
const sortDir = ref('asc')

function toggleSort(key) {
  if (sortCol.value === key) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortCol.value = key
    sortDir.value = 'asc'
  }
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

const rows = computed(() => {
  return props.holdings.map((h) => {
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
      rawValue,
      rawInvested,
      rawGain,
      rawYield,
      rawAnnualDiv,
      rawShares: h.shares,
      rawExDiv: h.stock.ex_dividend_date || '',
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
</script>
