<template>
  <div class="rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Income Milestones</h3>
    <div class="mt-3 overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-slate-200 dark:border-slate-700">
            <th class="px-3 py-2 text-left font-medium text-slate-500 dark:text-slate-400">Target</th>
            <th class="px-3 py-2 text-right font-medium text-slate-500 dark:text-slate-400">Reached at</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="m in milestones"
            :key="m.target"
            class="border-b border-slate-100 last:border-0 dark:border-slate-700/50"
          >
            <td class="px-3 py-2 tabular-nums text-slate-800 dark:text-slate-100">
              {{ formatEur(m.target) }}/yr
            </td>
            <td
              class="px-3 py-2 text-right tabular-nums"
              :class="m.reached ? 'text-emerald-500 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500'"
            >
              {{ m.reached ? `Year ${m.year}` : 'Not reached' }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { formatEur } from '../../utils/format'

defineProps({
  milestones: {
    type: Array,
    required: true,
  },
  finalYear: {
    type: Object,
    default: null,
  },
})
</script>
