<template>
  <div class="rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Best Buy Windows</h3>
    <div v-if="windows.length === 0" class="mt-3 text-sm text-slate-500 dark:text-slate-400">
      No upcoming windows.
    </div>
    <div v-else class="mt-3 space-y-4">
      <div
        v-for="(w, i) in windows"
        :key="i"
        class="rounded-lg border border-slate-100 p-3 dark:border-slate-700"
      >
        <div class="flex items-center justify-between">
          <p class="text-sm font-medium text-slate-800 dark:text-slate-100">
            Buy by {{ w.buy_by }}
          </p>
          <span
            class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-600 dark:bg-blue-900/30 dark:text-blue-400"
          >
            {{ w.days }}d
          </span>
        </div>
        <p class="mt-1 text-xs tabular-nums text-emerald-500 dark:text-emerald-400">
          {{ formatEur(w.total_dividend) }} total dividend
        </p>
        <div class="mt-2 flex flex-wrap gap-1">
          <span
            v-for="s in w.stocks"
            :key="s.ticker"
            class="rounded bg-slate-100 px-1.5 py-0.5 text-xs text-slate-600 dark:bg-slate-700 dark:text-slate-300"
          >
            {{ s.ticker }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { formatEur } from '../../utils/format'

defineProps({
  windows: {
    type: Array,
    required: true,
  },
})
</script>
