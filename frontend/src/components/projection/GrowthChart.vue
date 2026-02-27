<template>
  <div class="rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
    <div class="flex items-start justify-between">
      <div>
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Portfolio Growth</h3>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
          {{ '\u20AC' }}{{ monthly }}/mo + DRIP + {{ priceGrowth.toFixed(1) }}% price growth
        </p>
      </div>
      <label class="flex shrink-0 cursor-pointer items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
        <input
          v-model="inflationAdjusted"
          type="checkbox"
          class="rounded border-slate-300 text-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700"
        />
        Inflation adj. (2.5%)
      </label>
    </div>
    <div class="mt-3" style="height: 300px">
      <Line :data="chartData" :options="chartOptions" />
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Line } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Filler,
  Tooltip,
} from 'chart.js'
import { useTheme } from '../../composables/useTheme'
import { formatEur } from '../../utils/format'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Filler, Tooltip)

const { isDark } = useTheme()

const props = defineProps({
  data: {
    type: Array,
    required: true,
  },
  monthly: {
    type: Number,
    default: 0,
  },
  priceGrowth: {
    type: Number,
    default: 0,
  },
})

const inflationAdjusted = ref(false)

function deflate(value, yearIndex) {
  if (!inflationAdjusted.value) return value
  return value / Math.pow(1.025, yearIndex)
}

const chartData = computed(() => ({
  labels: props.data.map((d) => d.label),
  datasets: [
    {
      label: inflationAdjusted.value ? 'Portfolio Value (real)' : 'Portfolio Value',
      data: props.data.map((d, i) => deflate(d.portfolio_value, i)),
      borderColor: '#3B82F6',
      backgroundColor: 'rgba(59, 130, 246, 0.2)',
      fill: true,
      tension: 0.3,
      pointRadius: 0,
      pointHoverRadius: 6,
      pointHoverBorderWidth: 2,
      borderWidth: 2,
    },
    {
      label: inflationAdjusted.value ? 'Total Contributed (real)' : 'Total Contributed',
      data: props.data.map((d, i) => deflate(d.total_contributed, i)),
      borderColor: '#94A3B8',
      backgroundColor: 'transparent',
      fill: false,
      tension: 0.3,
      pointRadius: 0,
      pointHoverRadius: 6,
      pointHoverBorderWidth: 2,
      borderWidth: 2,
      borderDash: [6, 4],
    },
  ],
}))

const chartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  interaction: {
    mode: 'index',
    intersect: false,
  },
  plugins: {
    legend: {
      display: true,
      position: 'bottom',
      labels: {
        color: isDark.value ? '#94A3B8' : '#64748B',
        usePointStyle: true,
        pointStyle: 'line',
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
