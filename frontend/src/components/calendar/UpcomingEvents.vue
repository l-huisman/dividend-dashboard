<template>
  <div class="rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Upcoming Dividends</h3>
    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
      Next ex-dividend and payment dates for your holdings. Buy before the ex-dividend date to qualify for the next payment.
    </p>
    <div v-if="activeEvents.length === 0" class="mt-3 text-sm text-slate-500 dark:text-slate-400">
      No upcoming events.
    </div>
    <div v-else class="mt-3 divide-y divide-slate-100 dark:divide-slate-700/50">
      <div
        v-for="e in activeEvents"
        :key="e.ticker"
        class="flex items-center justify-between py-3 first:pt-0 last:pb-0"
      >
        <div class="min-w-0 flex-1">
          <p class="text-sm font-medium text-slate-800 dark:text-slate-100">{{ e.ticker }}</p>
          <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ e.name }}</p>
        </div>
        <div class="ml-4 text-right">
          <div class="flex items-center justify-end gap-2">
            <span class="text-xs text-slate-500 dark:text-slate-400">Ex-div</span>
            <span
              class="rounded-full px-2 py-0.5 text-xs font-medium"
              :class="daysBadgeClass(e.days_until_ex)"
            >
              {{ e.days_until_ex >= 0 ? e.days_until_ex + 'd' : 'Passed' }}
            </span>
          </div>
          <p class="mt-0.5 text-sm font-medium tabular-nums text-emerald-500 dark:text-emerald-400">
            {{ formatEur(e.per_payment) }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { formatEur } from '../../utils/format'

const props = defineProps({
  events: {
    type: Array,
    required: true,
  },
})

const activeEvents = computed(() => {
  return props.events.filter((e) => e.days_until_ex >= 0)
})

function daysBadgeClass(days) {
  if (days > 7) {
    return 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400'
  }
  if (days >= 3) {
    return 'bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400'
  }
  return 'bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-400'
}
</script>
