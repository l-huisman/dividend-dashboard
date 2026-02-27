<template>
  <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
    <div
      v-for="card in cards"
      :key="card.label"
      class="rounded-lg border border-slate-200 bg-white p-4 text-center transition-all duration-300 dark:border-slate-700 dark:bg-slate-800"
    >
      <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">
        {{ card.label }}
      </p>
      <p class="mt-1 text-xl font-bold tabular-nums" :style="{ color: card.color }">
        {{ card.value }}
      </p>
      <p class="mt-0.5 text-[11px] text-slate-400 dark:text-slate-500">
        {{ card.sub }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { formatEur, formatPct } from '../../utils/format'

const props = defineProps({
  summary: { type: Object, required: true },
  timelineIncome: { type: Array, default: () => [] },
  timelineViewStart: { type: Number, default: 0 },
  projectionData: { type: Array, default: () => [] },
  projYears: { type: Number, default: 20 },
})

const cards = computed(() => {
  const s = props.summary
  const tl = props.timelineIncome
  const viewStart = props.timelineViewStart

  // If we have timeline data, compute dynamic values from visible 12 bars
  if (tl.length > 0 && viewStart >= 0) {
    const visible12 = tl.slice(viewStart, viewStart + 12)
    const viewAnnualDiv = visible12.reduce((sum, m) => sum + m.income, 0)
    const viewMonthlyDiv = viewAnnualDiv / 12
    const viewDailyDiv = viewAnnualDiv / 365
    const viewYear = visible12[0]?.year ?? 0
    const isProjected = viewYear > 0
    const yearLabel = isProjected ? ` (Yr ${viewYear})` : ''

    const viewProj = props.projectionData.find((d) => d.year === viewYear) || props.projectionData[0] || {}
    const viewPortfolio = viewProj.portfolio_value || (s.total_value || 0)
    const viewContributed = viewProj.total_contributed || (s.total_invested || 0)

    const finalProj = props.projectionData.length > 0
      ? props.projectionData[props.projectionData.length - 1]
      : {}

    return [
      {
        label: `Annual Dividends${yearLabel}`,
        value: formatEur(viewAnnualDiv),
        color: '#10B981',
        sub: 'per year',
      },
      {
        label: `Monthly Income${yearLabel}`,
        value: formatEur(viewMonthlyDiv),
        color: '#3B82F6',
        sub: 'per month',
      },
      {
        label: `Daily Income${yearLabel}`,
        value: formatEur(viewDailyDiv),
        color: '#8B5CF6',
        sub: 'per day',
      },
      {
        label: isProjected ? `Portfolio (Yr ${viewYear})` : 'Total Invested',
        value: isProjected ? formatEur(viewPortfolio) : formatEur(s.total_invested),
        color: '#64748B',
        sub: isProjected
          ? `Contributed: ${formatEur(viewContributed)}`
          : `Return: ${(s.total_gain_pct || 0) >= 0 ? '+' : ''}${((s.total_gain_pct || 0) * 100).toFixed(1)}%`,
      },
      {
        label: `Projected (${props.projYears}yr)`,
        value: formatEur(finalProj.annual_dividends || 0),
        color: '#EC4899',
        sub: `${formatEur(finalProj.monthly_dividends || 0)}/mo`,
      },
    ]
  }

  // Fallback: static cards from summary (no timeline)
  const gainPositive = (s.total_gain || 0) >= 0
  return [
    {
      label: 'Portfolio Value',
      value: formatEur(s.total_value),
      color: '#1E293B',
      sub: (gainPositive ? '+' : '') + formatPct(s.total_gain_pct || 0),
    },
    {
      label: 'Total Invested',
      value: formatEur(s.total_invested),
      color: '#64748B',
      sub: (gainPositive ? '+' : '') + formatEur(s.total_gain),
    },
    {
      label: 'Annual Dividends',
      value: formatEur(s.total_annual_dividend),
      color: '#10B981',
      sub: formatPct(s.weighted_yield || 0) + ' yield',
    },
    {
      label: 'Monthly Income',
      value: formatEur(s.monthly_dividend),
      color: '#3B82F6',
      sub: formatEur(s.daily_dividend) + '/day',
    },
    {
      label: `Projected (${props.projYears}yr)`,
      value: '--',
      color: '#EC4899',
      sub: 'Load projection',
    },
  ]
})
</script>
