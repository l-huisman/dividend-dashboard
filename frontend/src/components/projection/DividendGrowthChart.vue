<template>
  <div class="rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Dividend Growth</h3>
    <div class="mt-3" style="height: 300px">
      <Bar :data="chartData" :options="chartOptions" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Bar } from 'vue-chartjs'
import {
  Chart as ChartJS,
  BarElement,
  CategoryScale,
  LinearScale,
  Tooltip,
} from 'chart.js'
import { useTheme } from '../../composables/useTheme'
import { formatEur } from '../../utils/format'

ChartJS.register(BarElement, CategoryScale, LinearScale, Tooltip)

const { isDark } = useTheme()

const props = defineProps({
  data: {
    type: Array,
    required: true,
  },
})

const chartData = computed(() => ({
  labels: props.data.map((d) => d.label),
  datasets: [
    {
      label: 'Annual Dividends',
      data: props.data.map((d) => d.annual_dividends),
      backgroundColor: '#10B981',
      borderRadius: 4,
    },
    {
      label: 'Monthly Dividends',
      data: props.data.map((d) => d.monthly_dividends),
      backgroundColor: '#3B82F6',
      borderRadius: 4,
    },
  ],
}))

const chartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: true,
      position: 'bottom',
      labels: {
        color: isDark.value ? '#94A3B8' : '#64748B',
        usePointStyle: true,
        pointStyle: 'rect',
        font: {
          size: 11,
        },
        padding: 16,
      },
    },
    tooltip: {
      callbacks: {
        label: (context) => `${context.dataset.label}: ${formatEur(context.parsed.y)}`,
      },
      bodyColor: isDark.value ? '#E2E8F0' : '#1E293B',
      backgroundColor: isDark.value ? '#1E293B' : '#FFFFFF',
      borderColor: isDark.value ? '#334155' : '#E2E8F0',
      borderWidth: 1,
      titleColor: isDark.value ? '#E2E8F0' : '#1E293B',
    },
  },
  scales: {
    x: {
      grid: {
        display: false,
      },
      ticks: {
        color: isDark.value ? '#94A3B8' : '#64748B',
        font: {
          size: 11,
        },
      },
      border: {
        display: false,
      },
    },
    y: {
      grid: {
        color: isDark.value ? '#334155' : '#E2E8F0',
      },
      ticks: {
        color: isDark.value ? '#94A3B8' : '#64748B',
        callback: (value) => formatEur(value),
        font: {
          size: 11,
        },
      },
      border: {
        display: false,
      },
    },
  },
}))
</script>
