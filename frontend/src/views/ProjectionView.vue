<template>
  <AppLayout>
    <div v-if="projection.loading && projection.data.length === 0">
      <LoadingSpinner />
    </div>
    <div v-else-if="projection.error">
      <ErrorAlert :message="projection.error" />
    </div>
    <div v-else class="space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold text-slate-800 dark:text-slate-100">Projection</h1>
      </div>

      <div class="rounded-lg border border-sky-200 bg-sky-50 p-5 dark:border-sky-800 dark:bg-sky-900/20">
        <h4 class="text-sm font-bold text-sky-700 dark:text-sky-300">How this projection works</h4>
        <p class="mt-2 text-sm leading-relaxed text-sky-800 dark:text-sky-200">
          <span class="font-semibold">Starting point:</span> Your current portfolio with a weighted
          dividend yield. Each month the model adds your contribution, reinvests all dividends (DRIP),
          and applies stock price growth. The dividend growth rate increases the yield annually to
          simulate companies raising their dividends over time. All values are in EUR.
        </p>
      </div>

      <ProjectionControls @update="projection.fetchProjection()" />

      <div v-if="projection.finalYear" class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-800">
          <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Final Value</p>
          <p class="mt-1 text-xl font-semibold tabular-nums text-slate-800 dark:text-slate-100">
            {{ formatEur(projection.finalYear.portfolio_value) }}
          </p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-800">
          <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Annual Div.</p>
          <p class="mt-1 text-xl font-semibold tabular-nums text-emerald-500 dark:text-emerald-400">
            {{ formatEur(projection.finalYear.annual_dividends) }}
          </p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-800">
          <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Monthly Div.</p>
          <p class="mt-1 text-xl font-semibold tabular-nums text-emerald-500 dark:text-emerald-400">
            {{ formatEur(projection.finalYear.monthly_dividends) }}
          </p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-800">
          <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Yield on Cost</p>
          <p class="mt-1 text-xl font-semibold tabular-nums text-blue-600 dark:text-blue-400">
            {{ formatPct(projection.finalYear.yield_on_cost) }}
          </p>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <GrowthChart :data="projection.data" :monthly="projection.monthly" :price-growth="projection.priceGrowth" />
        <DividendGrowthChart :data="projection.data" :div-growth="projection.divGrowth" />
      </div>

      <MilestonesTable :milestones="projection.milestones" :final-year="projection.finalYear" />
    </div>
  </AppLayout>
</template>

<script setup>
import { onMounted } from 'vue'
import AppLayout from '../components/layout/AppLayout.vue'
import LoadingSpinner from '../components/shared/LoadingSpinner.vue'
import ErrorAlert from '../components/shared/ErrorAlert.vue'
import ProjectionControls from '../components/projection/ProjectionControls.vue'
import GrowthChart from '../components/projection/GrowthChart.vue'
import DividendGrowthChart from '../components/projection/DividendGrowthChart.vue'
import MilestonesTable from '../components/projection/MilestonesTable.vue'
import { useProjectionStore } from '../stores/projection'
import { formatEur, formatPct } from '../utils/format'

const projection = useProjectionStore()

onMounted(() => {
  if (projection.data.length === 0) {
    projection.fetchProjection()
  }
})
</script>
