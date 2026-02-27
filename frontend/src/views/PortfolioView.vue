<template>
  <AppLayout>
    <div v-if="portfolio.loading && !portfolio.hasData">
      <LoadingSpinner />
    </div>
    <div v-else-if="portfolio.error">
      <ErrorAlert :message="portfolio.error" />
    </div>
    <div v-else>
      <div class="mb-6 flex items-center justify-between">
        <h1 class="text-xl font-semibold text-slate-800 dark:text-slate-100">Portfolio</h1>
        <ColumnPicker :columns="allColumns" v-model="visibleColumnKeys" />
      </div>

      <HoldingsTable :holdings="portfolio.holdings" :columns="visibleColumns" />
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import AppLayout from '../components/layout/AppLayout.vue'
import LoadingSpinner from '../components/shared/LoadingSpinner.vue'
import ErrorAlert from '../components/shared/ErrorAlert.vue'
import HoldingsTable from '../components/portfolio/HoldingsTable.vue'
import ColumnPicker from '../components/portfolio/ColumnPicker.vue'
import { usePortfolioStore } from '../stores/portfolio'

const ALL_COLUMNS = [
  { key: 'ticker', label: 'Ticker', align: 'left', default: true },
  { key: 'name', label: 'Company', align: 'left', default: false },
  { key: 'shares', label: 'Shares', align: 'right', default: false },
  { key: 'value', label: 'Value', align: 'right', default: true },
  { key: 'invested', label: 'Invested', align: 'right', default: false },
  { key: 'gain', label: 'Gain/Loss', align: 'right', default: true },
  { key: 'yield', label: 'Yield', align: 'right', default: true },
  { key: 'annualDiv', label: 'Annual Div.', align: 'right', default: true },
  { key: 'exDiv', label: 'Ex-Div Date', align: 'right', default: true },
  { key: 'sector', label: 'Sector', align: 'left', default: false },
]

const portfolio = usePortfolioStore()
const allColumns = ALL_COLUMNS

const visibleColumnKeys = ref(ALL_COLUMNS.filter((c) => c.default).map((c) => c.key))

const visibleColumns = computed(() =>
  ALL_COLUMNS.filter((c) => visibleColumnKeys.value.includes(c.key)),
)

onMounted(() => {
  if (!portfolio.hasData) {
    portfolio.fetchAll()
  }
})
</script>
