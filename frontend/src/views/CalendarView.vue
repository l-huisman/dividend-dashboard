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

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <MonthlyOverview :monthly-income="portfolio.calendar?.monthly_income || []" />
        <UpcomingEvents :events="portfolio.calendar?.upcoming_dividends || []" />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { onMounted } from 'vue'
import AppLayout from '../components/layout/AppLayout.vue'
import LoadingSpinner from '../components/shared/LoadingSpinner.vue'
import ErrorAlert from '../components/shared/ErrorAlert.vue'
import MonthlyOverview from '../components/calendar/MonthlyOverview.vue'
import UpcomingEvents from '../components/calendar/UpcomingEvents.vue'
import { usePortfolioStore } from '../stores/portfolio'

const portfolio = usePortfolioStore()

onMounted(() => {
  if (!portfolio.hasData) {
    portfolio.fetchAll()
  }
})
</script>
