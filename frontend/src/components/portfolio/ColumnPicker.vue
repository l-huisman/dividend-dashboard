<template>
  <div ref="pickerRef" class="relative">
    <button
      @click="open = !open"
      class="flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
    >
      <AdjustmentsHorizontalIcon class="h-4 w-4" />
      Columns
    </button>

    <div
      v-if="open"
      class="absolute right-0 z-10 mt-1 w-48 rounded-lg border border-slate-200 bg-white p-2 shadow-lg dark:border-slate-700 dark:bg-slate-800"
    >
      <label
        v-for="col in columns"
        :key="col.key"
        class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm text-slate-700 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-700"
      >
        <input
          type="checkbox"
          :checked="modelValue.includes(col.key)"
          @change="toggleColumn(col.key)"
          class="h-3.5 w-3.5 rounded border-slate-300 bg-white text-blue-600 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-600"
        />
        {{ col.label }}
      </label>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { AdjustmentsHorizontalIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  columns: {
    type: Array,
    required: true,
  },
  modelValue: {
    type: Array,
    required: true,
  },
})

const emit = defineEmits(['update:modelValue'])

const open = ref(false)
const pickerRef = ref(null)

function toggleColumn(key) {
  const current = [...props.modelValue]
  const index = current.indexOf(key)
  if (index >= 0) {
    current.splice(index, 1)
  } else {
    current.push(key)
  }
  emit('update:modelValue', current)
}

function handleClickOutside(event) {
  if (pickerRef.value && !pickerRef.value.contains(event.target)) {
    open.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
