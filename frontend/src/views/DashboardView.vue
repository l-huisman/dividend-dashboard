<template>
  <AppLayout>
    <div v-if="portfolio.loading && !portfolio.hasData">
      <LoadingSpinner />
    </div>
    <div v-else-if="portfolio.error">
      <ErrorAlert :message="portfolio.error" />
    </div>
    <div v-else class="space-y-6">
      <SummaryCards
        v-if="portfolio.summary"
        :summary="portfolio.summary"
        :timeline-income="timelineIncome"
        :timeline-view-start="timelineViewStart"
        :projection-data="projection.data"
        :proj-years="projection.years"
      />

      <IncomeTimeline
        v-if="portfolio.calendar"
        :monthly-income="portfolio.calendar.monthly_income"
        :projection-data="projection.data"
        :proj-years="projection.years"
        :div-growth="projection.divGrowth / 100"
        :price-growth="projection.priceGrowth / 100"
        @scroll="timelineViewStart = $event"
      />

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <SectorPieChart v-if="portfolio.sectors" :sectors="portfolio.sectors" />
        <TopEarners :holdings="portfolio.holdings" />
      </div>

      <BestBuyWindows :windows="portfolio.calendar?.investment_windows || []" />
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import AppLayout from '../components/layout/AppLayout.vue'
import LoadingSpinner from '../components/shared/LoadingSpinner.vue'
import ErrorAlert from '../components/shared/ErrorAlert.vue'
import SummaryCards from '../components/dashboard/SummaryCards.vue'
import IncomeTimeline from '../components/dashboard/IncomeTimeline.vue'
import SectorPieChart from '../components/dashboard/SectorPieChart.vue'
import TopEarners from '../components/dashboard/TopEarners.vue'
import BestBuyWindows from '../components/dashboard/BestBuyWindows.vue'
import { usePortfolioStore } from '../stores/portfolio'
import { useProjectionStore } from '../stores/projection'

const portfolio = usePortfolioStore()
const projection = useProjectionStore()

const timelineViewStart = ref(0)

// Compute extended timeline income from IncomeTimeline's internal logic
// This is passed through to SummaryCards for dynamic updates
const timelineIncome = computed(() => {
  const mi = portfolio.calendar?.monthly_income
  if (!mi || mi.length === 0) return []

  const startYear = new Date().getFullYear()
  const currentMonth = new Date().getMonth()
  const basePortfolio = projection.data[0]?.portfolio_value || 1
  const divGrowth = projection.divGrowth / 100
  const projYears = projection.years
  const data = []
  const totalMonths = projYears * 12

  for (let m = currentMonth; m <= totalMonths; m++) {
    const yearFrac = m / 12
    const mIdx = m % 12
    const divMultiplier = Math.pow(1 + divGrowth, yearFrac)

    const lowerYear = Math.floor(yearFrac)
    const upperYear = Math.min(Math.ceil(yearFrac), projYears)
    const frac = yearFrac - lowerYear
    const lowerVal = projection.data[lowerYear]?.portfolio_value || basePortfolio
    const upperVal = projection.data[upperYear]?.portfolio_value || lowerVal
    const interpolatedValue = lowerVal + (upperVal - lowerVal) * frac
    const valueMultiplier = interpolatedValue / basePortfolio

    const baseIncome = mi[mIdx]?.income || 0
    const scaled = baseIncome * divMultiplier * valueMultiplier

    data.push({
      income: Math.round(scaled * 100) / 100,
      year: Math.floor(yearFrac),
      monthIndex: mIdx,
    })
  }
  return data
})

onMounted(async () => {
  if (!portfolio.hasData) {
    await portfolio.fetchAll()
  }
  if (projection.data.length === 0) {
    await projection.fetchProjection()
  }
})
</script>
