<template>
  <AppLayout>
    <div v-if="portfolio.loading && !portfolio.hasData">
      <LoadingSpinner />
    </div>
    <div v-else-if="portfolio.error">
      <ErrorAlert :message="portfolio.error" />
    </div>
    <div v-else class="space-y-6">
      <h1 class="text-xl font-semibold text-slate-800 dark:text-slate-100">Dividend Calendar</h1>

      <IncomeTimeline
        v-if="portfolio.calendar"
        :monthly-income="portfolio.calendar.monthly_income"
        :projection-data="projection.data"
        :proj-years="projection.years"
        :div-growth="projection.divGrowth / 100"
        :price-growth="projection.priceGrowth / 100"
      />

      <UpcomingEvents :events="portfolio.calendar?.upcoming_dividends || []" />
    </div>
  </AppLayout>
</template>

<script setup>
import { onMounted } from 'vue'
import AppLayout from '../components/layout/AppLayout.vue'
import LoadingSpinner from '../components/shared/LoadingSpinner.vue'
import ErrorAlert from '../components/shared/ErrorAlert.vue'
import IncomeTimeline from '../components/dashboard/IncomeTimeline.vue'
import UpcomingEvents from '../components/calendar/UpcomingEvents.vue'
import { usePortfolioStore } from '../stores/portfolio'
import { useProjectionStore } from '../stores/projection'

const portfolio = usePortfolioStore()
const projection = useProjectionStore()

onMounted(() => {
  if (!portfolio.hasData) {
    portfolio.fetchAll()
  }
  if (projection.data.length === 0) {
    projection.fetchProjection()
  }
})
</script>
