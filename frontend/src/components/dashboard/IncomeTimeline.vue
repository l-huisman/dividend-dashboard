<template>
  <div class="rounded-lg border border-slate-200 bg-white p-3 dark:border-slate-700 dark:bg-slate-800 sm:p-5">
    <div class="mb-4">
      <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Monthly Income Timeline</h3>
      <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
        Scroll through your dividend income from today to Year {{ projYears }}
        ({{ (divGrowth * 100).toFixed(0) }}% div growth, {{ (priceGrowth * 100).toFixed(0) }}% price growth)
      </p>
    </div>

    <div v-if="timeline.length > 0" class="relative">
      <!-- Floating Y-axis overlay -->
      <div
        class="pointer-events-none absolute bottom-[40px] left-0 top-0 z-10 flex w-12 flex-col justify-between py-2 ml-[-1px] mb-[-12px] pb-[20px]"
        :style="{
          background: isDark
            ? 'linear-gradient(to right, rgb(30 41 59) 80%, transparent)'
            : 'linear-gradient(to right, rgb(255 255 255) 80%, transparent)',
        }"
      >
        <span
          v-for="(tick, i) in yTicks"
          :key="i"
          class="pr-4 text-right text-[11px] tabular-nums text-slate-400"
        >
          {{ formatYTick(tick) }}
        </span>
      </div>

      <!-- Scrollable chart container -->
      <div
        ref="scrollEl"
        class="scrollbar-dark overflow-x-auto overflow-y-hidden pl-12 sm:pl-16"
        style="scrollbar-width: thin; -webkit-overflow-scrolling: touch"
        @scroll="handleScroll"
      >
        <svg
          :width="timeline.length * 48 + 20"
          :height="340"
          class="block"
        >
          <!-- Grid lines -->
          <line
            v-for="frac in [0.25, 0.5, 0.75, 1]"
            :key="'grid-' + frac"
            :x1="0"
            :x2="timeline.length * 48 + 20"
            :y1="290 - frac * 270"
            :y2="290 - frac * 270"
            :stroke="isDark ? '#334155' : '#F1F5F9'"
            stroke-dasharray="3 3"
          />
          <line
            :x1="0"
            :x2="timeline.length * 48 + 20"
            :y1="290"
            :y2="290"
            :stroke="isDark ? '#334155' : '#F1F5F9'"
          />

          <!-- Bars -->
          <g
            v-for="(entry, idx) in timeline"
            :key="idx"
            class="cursor-pointer"
            @click="handleBarClick(idx)"
          >
            <!-- Highlight glow for selected/current -->
            <rect
              v-if="idx === selectedIdx || entry.isCurrent"
              :x="idx * 48 + 10 - 3"
              :y="290 - barHeight(entry.income) - 3"
              :width="34"
              :height="Math.max(barHeight(entry.income) + 3, 3)"
              rx="6"
              ry="6"
              :fill="idx === selectedIdx ? 'rgba(37,99,235,0.15)' : 'rgba(245,158,11,0.15)'"
            />

            <!-- Bar -->
            <rect
              :x="idx * 48 + 10"
              :y="290 - barHeight(entry.income)"
              :width="28"
              :height="Math.max(barHeight(entry.income), 0)"
              rx="4"
              ry="4"
              :fill="barColor(entry, idx)"
              :stroke="idx === selectedIdx ? '#2563EB' : entry.isCurrent ? '#F59E0B' : 'none'"
              :stroke-width="idx === selectedIdx || entry.isCurrent ? 2 : 0"
            />

            <!-- Value label -->
            <text
              v-if="entry.income > 0"
              :x="idx * 48 + 24"
              :y="290 - barHeight(entry.income) - 6"
              text-anchor="middle"
              :font-size="idx === selectedIdx || entry.isCurrent ? 10 : 9"
              :font-weight="idx === selectedIdx || entry.isCurrent ? 700 : 400"
              :fill="idx === selectedIdx ? '#2563EB' : entry.isCurrent ? '#F59E0B' : (isDark ? '#94A3B8' : '#64748B')"
              class="pointer-events-none"
            >
              {{ formatBarLabel(entry.income) }}
            </text>

            <!-- X-axis label -->
            <text
              :x="idx * 48 + 24"
              :y="310"
              text-anchor="middle"
              :font-size="entry.isCurrent || idx === selectedIdx ? 11 : entry.monthIndex === 0 ? 11 : 9"
              :font-weight="entry.isCurrent || idx === selectedIdx || entry.monthIndex === 0 ? 700 : 400"
              :fill="entry.isCurrent ? '#F59E0B' : idx === selectedIdx ? '#2563EB' : entry.monthIndex === 0 ? (isDark ? '#E2E8F0' : '#1E293B') : (isDark ? '#64748B' : '#94A3B8')"
              class="pointer-events-none"
            >
              {{ entry.monthIndex === 0 ? entry.calYear : MONTHS[entry.monthIndex] }}
            </text>

            <!-- NOW indicator -->
            <text
              v-if="entry.isCurrent"
              :x="idx * 48 + 24"
              :y="326"
              text-anchor="middle"
              font-size="8"
              font-weight="700"
              fill="#F59E0B"
              letter-spacing="1"
              class="pointer-events-none uppercase"
            >
              NOW
            </text>

            <!-- Year separator -->
            <line
              v-if="entry.monthIndex === 0 && idx > 0"
              :x1="idx * 48 + 5"
              :x2="idx * 48 + 5"
              :y1="10"
              :y2="295"
              :stroke="isDark ? '#475569' : '#E2E8F0'"
              stroke-dasharray="4 4"
              class="pointer-events-none"
            />

            <!-- Tooltip hit area -->
            <rect
              :x="idx * 48 + 5"
              :y="0"
              :width="48"
              :height="330"
              fill="transparent"
              opacity="0"
            >
              <title>{{ entry.label }}: {{ formatEur(entry.income) }} (Year {{ entry.year }})</title>
            </rect>
          </g>
        </svg>
      </div>
    </div>

    <!-- Footer -->
    <div v-if="timeline.length > 0" class="mt-2 flex flex-wrap items-center justify-between gap-1 text-xs">
      <span class="text-slate-400 dark:text-slate-500">Scroll to explore</span>
      <span class="text-slate-500 dark:text-slate-400">
        {{ formatEur(todayAnnual) }}/yr
        &#8594;
        <span class="font-semibold text-emerald-500 dark:text-emerald-400">{{ formatEur(futureAnnual) }}/yr</span>
        <span
          v-if="multiplier > 0"
          class="ml-1 rounded bg-emerald-50 px-1.5 py-0.5 text-[11px] font-bold text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400"
        >
          {{ multiplier.toFixed(1) }}x
        </span>
      </span>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useTheme } from '../../composables/useTheme'
import { formatEur } from '../../utils/format'

const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']

const { isDark } = useTheme()

const props = defineProps({
  monthlyIncome: { type: Array, required: true },
  projectionData: { type: Array, default: () => [] },
  projYears: { type: Number, default: 20 },
  divGrowth: { type: Number, default: 0.05 },
  priceGrowth: { type: Number, default: 0.07 },
})

const emit = defineEmits(['scroll'])

const scrollEl = ref(null)
const selectedIdx = ref(-1)
const yAxisMax = ref(0)
const yAxisTarget = ref(0)
let animFrame = null

// Build the full timeline from base monthly income + projection growth
const timeline = computed(() => {
  if (props.monthlyIncome.length === 0) return []

  const startYear = new Date().getFullYear()
  const currentMonth = new Date().getMonth()
  const basePortfolio = props.projectionData[0]?.portfolio_value || 1
  const data = []
  const totalMonths = props.projYears * 12

  for (let m = currentMonth; m <= totalMonths; m++) {
    const yearFrac = m / 12
    const mi = m % 12
    const divMultiplier = Math.pow(1 + props.divGrowth, yearFrac)

    const lowerYear = Math.floor(yearFrac)
    const upperYear = Math.min(Math.ceil(yearFrac), props.projYears)
    const frac = yearFrac - lowerYear
    const lowerVal = props.projectionData[lowerYear]?.portfolio_value || basePortfolio
    const upperVal = props.projectionData[upperYear]?.portfolio_value || lowerVal
    const interpolatedValue = lowerVal + (upperVal - lowerVal) * frac
    const valueMultiplier = interpolatedValue / basePortfolio

    const baseIncome = props.monthlyIncome[mi]?.income || 0
    const scaled = baseIncome * divMultiplier * valueMultiplier
    const calYear = startYear + Math.floor(yearFrac)

    data.push({
      label: `${MONTHS[mi]} '${String(calYear).slice(2)}`,
      income: Math.round(scaled * 100) / 100,
      year: Math.floor(yearFrac),
      monthIndex: mi,
      calYear,
      isCurrent: m === currentMonth,
    })
  }
  return data
})

const yTicks = computed(() => {
  const max = yAxisMax.value || 1
  return [max, max * 0.75, max * 0.5, max * 0.25, 0]
})

const todayAnnual = computed(() => {
  return props.monthlyIncome.reduce((s, m) => s + (m.income || 0), 0)
})

const futureAnnual = computed(() => {
  if (timeline.value.length < 12) return 0
  const last12 = timeline.value.slice(-12)
  return last12.reduce((s, m) => s + m.income, 0)
})

const multiplier = computed(() => {
  return todayAnnual.value > 0 ? futureAnnual.value / todayAnnual.value : 0
})

function barHeight(income) {
  const max = yAxisMax.value || 1
  return Math.max(0, (income / max) * 270)
}

function barColor(entry, idx) {
  if (idx === selectedIdx.value) return '#2563EB'
  if (entry.isCurrent) return '#F59E0B'
  const ratio = entry.year / Math.max(props.projYears, 1)
  const r = Math.round(16 + ratio * (37 - 16))
  const g = Math.round(185 + ratio * (99 - 185))
  const b = Math.round(129 + ratio * (235 - 129))
  return `rgb(${r},${g},${b})`
}

function formatBarLabel(income) {
  if (income < 10) return '\u20AC' + income.toFixed(2)
  return '\u20AC' + income.toFixed(1)
}

function formatYTick(v) {
  if (v < 10) return '\u20AC' + v.toFixed(2)
  if (v < 100) return '\u20AC' + v.toFixed(1)
  return '\u20AC' + Math.round(v)
}

function handleBarClick(idx) {
  selectedIdx.value = idx
  if (scrollEl.value) {
    scrollEl.value.scrollTo({ left: idx * 48, behavior: 'smooth' })
  }
}

function animateYAxis() {
  const target = yAxisTarget.value
  const diff = target - yAxisMax.value
  if (Math.abs(diff) < 0.01) {
    yAxisMax.value = target
    animFrame = null
    return
  }
  yAxisMax.value += diff * 0.12
  animFrame = requestAnimationFrame(animateYAxis)
}

function handleScroll() {
  const el = scrollEl.value
  if (!el || timeline.value.length === 0) return

  const scrollLeft = el.scrollLeft
  const visibleWidth = el.clientWidth
  const firstVisible = Math.floor(scrollLeft / 48)
  const lastVisible = Math.min(timeline.value.length - 1, Math.ceil((scrollLeft + visibleWidth) / 48))

  emit('scroll', Math.min(firstVisible, timeline.value.length - 1))

  let max = 0
  for (let i = firstVisible; i <= lastVisible; i++) {
    if (timeline.value[i] && timeline.value[i].income > max) {
      max = timeline.value[i].income
    }
  }

  const newTarget = max * 1.25
  if (Math.abs(newTarget - yAxisTarget.value) > 0.01) {
    yAxisTarget.value = newTarget
    if (!animFrame) {
      animFrame = requestAnimationFrame(animateYAxis)
    }
  }
}

function initYAxis() {
  if (timeline.value.length > 0 && yAxisMax.value === 0) {
    const first12 = timeline.value.slice(0, 12)
    const max = Math.max(...first12.map((d) => d.income))
    const initial = max * 1.25
    yAxisTarget.value = initial
    yAxisMax.value = initial
  }
}

watch(() => timeline.value.length, () => {
  initYAxis()
})

onMounted(() => {
  initYAxis()
})

onUnmounted(() => {
  if (animFrame) cancelAnimationFrame(animFrame)
})
</script>
