<template>
  <div class="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-800">
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
      <div>
        <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Monthly (EUR)</label>
        <input
          type="number"
          v-model.number="monthly"
          @change="update"
          min="0"
          max="10000"
          step="50"
          class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm tabular-nums text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100"
        />
      </div>
      <div>
        <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Years</label>
        <input
          type="number"
          v-model.number="years"
          @change="update"
          min="1"
          max="50"
          step="1"
          class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm tabular-nums text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100"
        />
      </div>
      <div>
        <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Div. Growth (%)</label>
        <input
          type="number"
          v-model.number="divGrowth"
          @change="update"
          min="0"
          max="20"
          step="0.5"
          class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm tabular-nums text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100"
        />
      </div>
      <div>
        <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Price Growth (%)</label>
        <input
          type="number"
          v-model.number="priceGrowth"
          @change="update"
          min="0"
          max="20"
          step="0.5"
          class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm tabular-nums text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useProjectionStore } from '../../stores/projection'

const emit = defineEmits(['update'])
const projection = useProjectionStore()

const monthly = ref(projection.monthly)
const years = ref(projection.years)
const divGrowth = ref(projection.divGrowth)
const priceGrowth = ref(projection.priceGrowth)

onMounted(() => {
  monthly.value = projection.monthly
  years.value = projection.years
  divGrowth.value = projection.divGrowth
  priceGrowth.value = projection.priceGrowth
})

function update() {
  projection.setParams({
    monthly: monthly.value,
    years: years.value,
    divGrowth: divGrowth.value,
    priceGrowth: priceGrowth.value,
  })
  emit('update')
}
</script>
