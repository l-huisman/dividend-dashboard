<template>
  <div class="rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Upcoming Dividends</h3>
    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
      Buy before the ex-dividend date to qualify for the next payment.
    </p>
    <div v-if="activeEvents.length === 0" class="mt-3 text-sm text-slate-500 dark:text-slate-400">
      No upcoming events.
    </div>
    <table v-else class="mt-3 w-full text-sm">
      <thead>
        <tr class="border-b border-slate-200 text-xs text-slate-400 dark:border-slate-700 dark:text-slate-500">
          <th class="pb-2 text-left font-medium">Stock</th>
          <th class="pb-2 text-right font-medium">Ex-div</th>
          <th class="pb-2 text-right font-medium">Payment</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
        <tr v-for="e in activeEvents" :key="e.ticker">
          <td class="py-1.5">
            <span class="font-medium text-slate-800 dark:text-slate-100">{{ e.ticker }}</span>
            <span class="ml-1.5 text-xs text-slate-400 dark:text-slate-500">{{ e.name }}</span>
          </td>
          <td class="py-1.5 text-right">
            <span
              class="inline-block rounded-full px-2 py-0.5 text-xs font-medium"
              :class="daysBadgeClass(e.days_until_ex)"
            >
              {{ e.days_until_ex + 'd' }}
            </span>
          </td>
          <td class="py-1.5 text-right tabular-nums font-medium text-emerald-500 dark:text-emerald-400">
            {{ formatEur(e.per_payment) }}
          </td>
        </tr>
      </tbody>
    </table>
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
