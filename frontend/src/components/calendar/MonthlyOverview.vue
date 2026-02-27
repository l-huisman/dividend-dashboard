<template>
  <div class="rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Monthly Breakdown</h3>
    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Which stocks pay dividends in each month and your estimated income</p>
    <div class="mt-3 space-y-2">
      <div v-for="m in monthlyIncome" :key="m.month" class="flex items-center gap-3">
        <span class="w-8 text-xs font-medium text-slate-500 dark:text-slate-400">{{ m.month }}</span>
        <div class="flex-1">
          <div class="h-5 rounded bg-slate-100 dark:bg-slate-700">
            <div
              class="h-5 rounded bg-blue-500 dark:bg-blue-400 transition-all duration-300"
              :style="{ width: barWidth(m.income) }"
            ></div>
          </div>
        </div>
        <span class="w-20 text-right text-sm font-medium tabular-nums text-slate-800 dark:text-slate-100">
          {{ formatEur(m.income) }}
        </span>
      </div>
    </div>
    <div class="mt-3 flex justify-between border-t border-slate-200 pt-3 dark:border-slate-700">
      <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Annual</span>
      <span class="text-sm font-semibold tabular-nums text-slate-800 dark:text-slate-100">
        {{ formatEur(totalAnnual) }}
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { formatEur } from '../../utils/format'

const props = defineProps({
  monthlyIncome: {
    type: Array,
    required: true,
  },
})

const maxIncome = computed(() => {
  if (props.monthlyIncome.length === 0) return 0
  return Math.max(...props.monthlyIncome.map((m) => m.income))
})

const totalAnnual = computed(() => {
  return props.monthlyIncome.reduce((sum, m) => sum + m.income, 0)
})

function barWidth(income) {
  if (maxIncome.value === 0) return '0%'
  return ((income / maxIncome.value) * 100).toFixed(1) + '%'
}
</script>
