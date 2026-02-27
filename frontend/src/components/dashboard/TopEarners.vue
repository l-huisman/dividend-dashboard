<template>
  <div class="rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Top Earners</h3>
    <div v-if="topFive.length === 0" class="mt-3 text-sm text-slate-500 dark:text-slate-400">
      No holdings found.
    </div>
    <div v-else class="mt-3 space-y-3">
      <div
        v-for="item in topFive"
        :key="item.stock?.ticker"
        class="flex items-center justify-between"
      >
        <div class="min-w-0 flex-1">
          <p class="text-sm font-medium text-slate-800 dark:text-slate-100">
            {{ item.stock?.ticker }}
          </p>
          <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ item.stock?.name }}</p>
        </div>
        <p class="ml-4 shrink-0 text-sm font-medium tabular-nums text-emerald-500 dark:text-emerald-400">
          {{ formatEur(item.annualDividend) }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { formatEur } from '../../utils/format'

const props = defineProps({
  holdings: {
    type: Array,
    required: true,
  },
})

const topFive = computed(() => {
  return [...props.holdings]
    .map((h) => ({
      ...h,
      annualDividend: (h.shares * (h.stock?.dividend_per_share || 0)) / 1.18,
    }))
    .sort((a, b) => b.annualDividend - a.annualDividend)
    .slice(0, 5)
})
</script>
