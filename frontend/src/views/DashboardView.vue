<template>
  <AppLayout>
    <div v-if="portfolio.loading && !portfolio.hasData">
      <LoadingSpinner />
    </div>
    <div v-else-if="portfolio.error">
      <ErrorAlert :message="portfolio.error" />
    </div>
    <div v-else class="space-y-6">
      <SummaryCards v-if="portfolio.summary" :summary="portfolio.summary" />

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <IncomeTimeline
          v-if="portfolio.calendar"
          :monthly-income="portfolio.calendar.monthly_income"
        />
        <SectorPieChart v-if="portfolio.sectors" :sectors="portfolio.sectors" />
      </div>

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <TopEarners :holdings="portfolio.holdings" />
        <BestBuyWindows :windows="portfolio.calendar?.investment_windows || []" />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { onMounted } from 'vue'
import AppLayout from '../components/layout/AppLayout.vue'
import LoadingSpinner from '../components/shared/LoadingSpinner.vue'
import ErrorAlert from '../components/shared/ErrorAlert.vue'
import SummaryCards from '../components/dashboard/SummaryCards.vue'
import IncomeTimeline from '../components/dashboard/IncomeTimeline.vue'
import SectorPieChart from '../components/dashboard/SectorPieChart.vue'
import TopEarners from '../components/dashboard/TopEarners.vue'
import BestBuyWindows from '../components/dashboard/BestBuyWindows.vue'
import { usePortfolioStore } from '../stores/portfolio'

const portfolio = usePortfolioStore()

onMounted(() => {
  if (!portfolio.hasData) {
    portfolio.fetchAll()
  }
})
</script>
