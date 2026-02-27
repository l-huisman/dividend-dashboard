<template>
  <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
    <div
      v-for="card in cards"
      :key="card.label"
      class="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-800"
    >
      <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
        {{ card.label }}
      </p>
      <p class="mt-1 text-xl font-semibold tabular-nums text-slate-800 dark:text-slate-100">
        {{ card.value }}
      </p>
      <p class="mt-1 text-sm tabular-nums" :class="card.subtitleClass">
        {{ card.subtitle }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { formatEur, formatPct } from '../../utils/format'

const props = defineProps({
  summary: {
    type: Object,
    required: true,
  },
})

const cards = computed(() => {
  const s = props.summary
  const gainPositive = (s.total_gain || 0) >= 0
  const gainPctPositive = (s.total_gain_pct || 0) >= 0

  return [
    {
      label: 'Portfolio Value',
      value: formatEur(s.total_value),
      subtitle: (gainPctPositive ? '+' : '') + formatPct(s.total_gain_pct || 0),
      subtitleClass: gainPctPositive
        ? 'text-emerald-500 dark:text-emerald-400'
        : 'text-red-500 dark:text-red-400',
    },
    {
      label: 'Total Invested',
      value: formatEur(s.total_invested),
      subtitle: (gainPositive ? '+' : '') + formatEur(s.total_gain),
      subtitleClass: gainPositive
        ? 'text-emerald-500 dark:text-emerald-400'
        : 'text-red-500 dark:text-red-400',
    },
    {
      label: 'Annual Dividends',
      value: formatEur(s.total_annual_dividend),
      subtitle: formatPct(s.weighted_yield || 0) + ' yield',
      subtitleClass: 'text-slate-500 dark:text-slate-400',
    },
    {
      label: 'Monthly Income',
      value: formatEur(s.monthly_dividend),
      subtitle: formatEur(s.daily_dividend) + '/day',
      subtitleClass: 'text-slate-500 dark:text-slate-400',
    },
  ]
})
</script>
