<template>
  <div class="rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Sector Allocation</h3>
    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Portfolio value distribution (EUR)</p>
    <div class="mt-4 grid grid-cols-1 gap-6 sm:grid-cols-2">
      <div>
        <p class="mb-2 text-xs font-medium text-slate-500 dark:text-slate-400">By Value</p>
        <Doughnut :data="valueChartData" :options="chartOptions" />
      </div>
      <div>
        <p class="mb-2 text-xs font-medium text-slate-500 dark:text-slate-400">By Dividend</p>
        <Doughnut :data="dividendChartData" :options="chartOptions" />
      </div>
    </div>
    <div class="mt-4 flex flex-wrap gap-x-4 gap-y-1">
      <div v-for="item in sectors.by_value" :key="item.name" class="flex items-center gap-1.5">
        <span
          class="h-2.5 w-2.5 rounded-full"
          :style="{ backgroundColor: getSectorColor(item.name) }"
        ></span>
        <span class="text-xs text-slate-600 dark:text-slate-300">{{ item.name }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Doughnut } from 'vue-chartjs'
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js'
import { useTheme } from '../../composables/useTheme'
import { formatEur } from '../../utils/format'

ChartJS.register(ArcElement, Tooltip, Legend)

const { isDark } = useTheme()

const SECTOR_COLORS = {
  'Technology': '#3B82F6',
  'Real Estate': '#8B5CF6',
  'Healthcare': '#10B981',
  'Energy': '#F59E0B',
  'Consumer Defensive': '#6366F1',
  'Consumer Cyclical': '#EC4899',
  'Financial Services': '#14B8A6',
  'Industrials': '#F97316',
  'Basic Materials': '#64748B',
  'Utilities': '#EAB308',
}

const FALLBACK_COLOR = '#94A3B8'

const props = defineProps({
  sectors: {
    type: Object,
    required: true,
  },
})

function getSectorColor(name) {
  return SECTOR_COLORS[name] || FALLBACK_COLOR
}

function buildChartData(items) {
  return {
    labels: items.map((i) => i.name),
    datasets: [
      {
        data: items.map((i) => i.value),
        backgroundColor: items.map((i) => getSectorColor(i.name)),
        borderWidth: 0,
      },
    ],
  }
}

const valueChartData = computed(() => buildChartData(props.sectors.by_value || []))
const dividendChartData = computed(() => buildChartData(props.sectors.by_dividend || []))

const chartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: true,
  cutout: '65%',
  plugins: {
    legend: {
      display: false,
    },
    tooltip: {
      callbacks: {
        label: (context) => {
          const label = context.label || ''
          const value = formatEur(context.parsed)
          return `${label}: ${value}`
        },
      },
      bodyColor: isDark.value ? '#E2E8F0' : '#1E293B',
      backgroundColor: isDark.value ? '#1E293B' : '#FFFFFF',
      borderColor: isDark.value ? '#334155' : '#E2E8F0',
      borderWidth: 1,
      titleColor: isDark.value ? '#E2E8F0' : '#1E293B',
    },
  },
}))
</script>
