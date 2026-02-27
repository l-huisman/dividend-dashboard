<template>
  <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
    <!-- Ex-Dividend Dates -->
    <div class="rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
      <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Ex-Dividend Dates</h3>
      <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
        Buy before the ex-dividend date to qualify for the next payment. Showing next 31 days.
      </p>
      <div v-if="exDivEvents.length === 0" class="mt-3 text-sm text-slate-500 dark:text-slate-400">
        No upcoming ex-dividend dates.
      </div>
      <table v-else class="mt-3 w-full text-sm">
        <thead>
          <tr class="border-b border-slate-200 text-xs text-slate-400 dark:border-slate-700 dark:text-slate-500">
            <th class="pb-2 text-left font-medium">Stock</th>
            <th class="pb-2 text-right font-medium">Ex-div date</th>
            <th class="pb-2 text-right font-medium">Days</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
          <tr v-for="e in exDivEvents" :key="e.ticker">
            <td class="py-1.5">
              <span class="font-medium text-slate-800 dark:text-slate-100">{{ e.ticker }}</span>
              <span class="ml-1.5 text-xs text-slate-400 dark:text-slate-500">{{ e.name }}</span>
            </td>
            <td class="py-1.5 text-right text-xs text-slate-500 dark:text-slate-400">
              {{ e.ex_dividend_date }}
            </td>
            <td class="py-1.5 text-right">
              <span
                class="inline-block rounded-full px-2 py-0.5 text-xs font-medium"
                :class="daysBadgeClass(e.days_until_ex)"
              >Monthly Income Timeline
                {{ e.days_until_ex + 'd' }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Upcoming Payments -->
    <div class="rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
      <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Upcoming Payments</h3>
      <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
        Expected dividend payments based on your holdings. Showing next 31 days.
      </p>
      <div v-if="paymentEvents.length === 0" class="mt-3 text-sm text-slate-500 dark:text-slate-400">
        No upcoming payments.
      </div>
      <table v-else class="mt-3 w-full text-sm">
        <thead>
          <tr class="border-b border-slate-200 text-xs text-slate-400 dark:border-slate-700 dark:text-slate-500">
            <th class="pb-2 text-left font-medium">Stock</th>
            <th class="pb-2 text-right font-medium">Payment</th>
            <th class="pb-2 text-right font-medium">Days</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
          <tr v-for="e in paymentEvents" :key="e.ticker">
            <td class="py-1.5">
              <span class="font-medium text-slate-800 dark:text-slate-100">{{ e.ticker }}</span>
              <span class="ml-1.5 text-xs text-slate-400 dark:text-slate-500">{{ e.name }}</span>
            </td>
            <td class="py-1.5 text-right tabular-nums font-medium text-emerald-500 dark:text-emerald-400">
              {{ formatEur(e.per_payment) }}
            </td>
            <td class="py-1.5 text-right">
              <span
                class="inline-block rounded-full px-2 py-0.5 text-xs font-medium"
                :class="daysBadgeClass(e.days_until_pay)"
              >
                {{ e.days_until_pay + 'd' }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
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

const exDivEvents = computed(() => {
  return props.events
    .filter((e) => e.days_until_ex >= 0 && e.days_until_ex <= 31)
    .sort((a, b) => a.days_until_ex - b.days_until_ex)
})

const paymentEvents = computed(() => {
  return props.events
    .filter((e) => e.days_until_pay >= 0 && e.days_until_pay <= 31)
    .sort((a, b) => a.days_until_pay - b.days_until_pay)
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
