# UI Refinement Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Implement 7 UI refinements to bring the Vue frontend closer to the original JSX functionality.

**Architecture:** All changes are frontend-only Vue 3 components. No backend changes needed. The income timeline computation moves from simple API data to client-side growth interpolation using existing projection store data. The sidebar replaces the top navbar with a collapsible left column layout.

**Tech Stack:** Vue 3 Composition API, Tailwind CSS, Heroicons, Pinia stores, custom SVG rendering

---

## Important Context

- **Project root:** `/Users/lh/Documents/School/HBO/HBO 2526/Retakes/Frontend Development 2`
- **Frontend root:** `frontend/`
- **Docker:** Node container `frontenddevelopment2-node-1` runs Vite dev server on port 5173
- **Build check:** `export PATH="/usr/local/bin:/usr/bin:/bin:$PATH" && docker exec frontenddevelopment2-node-1 npm run build`
- **No test framework configured** -- verify via build check + manual review
- **Dark mode:** Uses Tailwind `dark:` classes, toggled via `useTheme()` composable
- **EUR format:** Use `formatEur()` from `src/utils/format.js`
- **Heroicons installed:** `@heroicons/vue` -- use `24/outline` variants
- **No emoji in code or copy**

---

### Task 1: Sidebar Navigation -- Create SideBar.vue

**Files:**
- Create: `frontend/src/components/layout/SideBar.vue`

**Step 1: Create the SideBar component**

Create `frontend/src/components/layout/SideBar.vue` with this content:

```vue
<template>
  <!-- Mobile backdrop -->
  <div
    v-if="mobileOpen"
    class="fixed inset-0 z-40 bg-black/50 lg:hidden"
    @click="mobileOpen = false"
  ></div>

  <!-- Mobile hamburger -->
  <button
    @click="mobileOpen = true"
    class="fixed left-4 top-4 z-30 rounded-lg bg-white p-2 shadow-md dark:bg-slate-800 lg:hidden"
  >
    <Bars3Icon class="h-5 w-5 text-slate-600 dark:text-slate-300" />
  </button>

  <!-- Sidebar -->
  <aside
    :class="[
      'fixed left-0 top-0 z-50 flex h-full flex-col border-r border-slate-200 bg-white transition-all duration-300 dark:border-slate-700 dark:bg-slate-800',
      expanded ? 'w-56' : 'w-16',
      mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
    ]"
  >
    <!-- Brand -->
    <div class="flex h-14 items-center justify-center border-b border-slate-200 px-3 dark:border-slate-700">
      <router-link to="/" class="flex items-center gap-2 overflow-hidden">
        <span class="text-lg font-bold text-blue-600 dark:text-blue-400">DF</span>
        <span
          v-if="expanded"
          class="whitespace-nowrap text-sm font-semibold text-slate-800 dark:text-slate-100"
        >
          DividendFlow
        </span>
      </router-link>
    </div>

    <!-- Nav links -->
    <nav class="flex-1 space-y-1 overflow-y-auto px-2 py-3">
      <template v-if="auth.isUser">
        <router-link
          v-for="link in userLinks"
          :key="link.to"
          :to="link.to"
          @click="mobileOpen = false"
          :title="link.label"
          :class="[
            'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
            isActive(link.to)
              ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400'
              : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700',
            expanded ? '' : 'justify-center',
          ]"
        >
          <component :is="link.icon" class="h-5 w-5 shrink-0" />
          <span v-if="expanded" class="truncate">{{ link.label }}</span>
        </router-link>
      </template>
      <template v-if="auth.isAdmin">
        <router-link
          to="/admin"
          @click="mobileOpen = false"
          title="Users"
          :class="[
            'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
            isActive('/admin')
              ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400'
              : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700',
            expanded ? '' : 'justify-center',
          ]"
        >
          <UsersIcon class="h-5 w-5 shrink-0" />
          <span v-if="expanded" class="truncate">Users</span>
        </router-link>
      </template>
    </nav>

    <!-- Bottom section -->
    <div class="border-t border-slate-200 px-2 py-3 dark:border-slate-700">
      <!-- Theme toggle -->
      <button
        @click="toggle"
        :title="isDark ? 'Light mode' : 'Dark mode'"
        :class="[
          'flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700',
          expanded ? '' : 'justify-center',
        ]"
      >
        <MoonIcon v-if="!isDark" class="h-5 w-5 shrink-0" />
        <SunIcon v-else class="h-5 w-5 shrink-0" />
        <span v-if="expanded" class="truncate">{{ isDark ? 'Light mode' : 'Dark mode' }}</span>
      </button>

      <!-- User info -->
      <div
        v-if="expanded && auth.user"
        class="mt-1 truncate px-3 py-1 text-xs text-slate-400 dark:text-slate-500"
      >
        {{ auth.user.username }}
      </div>

      <!-- Logout -->
      <button
        @click="auth.logout()"
        title="Sign out"
        :class="[
          'flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700',
          expanded ? '' : 'justify-center',
        ]"
      >
        <ArrowRightOnRectangleIcon class="h-5 w-5 shrink-0" />
        <span v-if="expanded" class="truncate">Sign out</span>
      </button>

      <!-- Collapse toggle -->
      <button
        @click="expanded = !expanded"
        title="Toggle sidebar"
        :class="[
          'mt-1 hidden w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-slate-400 hover:bg-slate-100 dark:text-slate-500 dark:hover:bg-slate-700 lg:flex',
          expanded ? '' : 'justify-center',
        ]"
      >
        <ChevronDoubleRightIcon
          class="h-4 w-4 shrink-0 transition-transform duration-300"
          :class="expanded ? 'rotate-180' : ''"
        />
        <span v-if="expanded" class="truncate">Collapse</span>
      </button>
    </div>
  </aside>
</template>

<script setup>
import { ref } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { useTheme } from '../../composables/useTheme'
import {
  HomeIcon,
  BriefcaseIcon,
  ArrowTrendingUpIcon,
  CalendarIcon,
  ArrowUpTrayIcon,
  UsersIcon,
  SunIcon,
  MoonIcon,
  Bars3Icon,
  ArrowRightOnRectangleIcon,
  ChevronDoubleRightIcon,
} from '@heroicons/vue/24/outline'

const auth = useAuthStore()
const route = useRoute()
const { isDark, toggle } = useTheme()

const expanded = ref(false)
const mobileOpen = ref(false)

const userLinks = [
  { to: '/', label: 'Dashboard', icon: HomeIcon },
  { to: '/portfolio', label: 'Portfolio', icon: BriefcaseIcon },
  { to: '/projection', label: 'Projection', icon: ArrowTrendingUpIcon },
  { to: '/calendar', label: 'Calendar', icon: CalendarIcon },
  { to: '/import', label: 'Import', icon: ArrowUpTrayIcon },
]

function isActive(to) {
  if (to === '/') return route.path === '/'
  return route.path.startsWith(to)
}
</script>
```

**Step 2: Update AppLayout.vue to use SideBar**

Replace the content of `frontend/src/components/layout/AppLayout.vue` with:

```vue
<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-900">
    <SideBar />
    <main
      :class="[
        'min-h-screen transition-all duration-300 lg:ml-16',
        'px-4 py-6 pt-16 sm:px-6 lg:px-8 lg:pt-6',
      ]"
    >
      <div class="mx-auto max-w-7xl">
        <slot />
      </div>
    </main>
  </div>
</template>

<script setup>
import SideBar from './SideBar.vue'
</script>
```

**Step 3: Verify build**

Run: `export PATH="/usr/local/bin:/usr/bin:/bin:$PATH" && docker exec frontenddevelopment2-node-1 npm run build`
Expected: Build succeeds with no errors

**Step 4: Commit**

```bash
git add frontend/src/components/layout/SideBar.vue frontend/src/components/layout/AppLayout.vue
git commit -m "Replace top navbar with collapsible sidebar navigation"
```

---

### Task 2: Income Timeline -- Custom SVG Scrollable Component

**Files:**
- Rewrite: `frontend/src/components/dashboard/IncomeTimeline.vue`

**Step 1: Rewrite IncomeTimeline.vue with custom SVG**

Replace the entire content of `frontend/src/components/dashboard/IncomeTimeline.vue` with the custom SVG timeline. The component:

- Accepts `monthlyIncome` (12-month base pattern), `projectionData` (year-by-year), `projYears`, `divGrowth`, `priceGrowth` props
- Computes a full multi-year `timelineIncome` array client-side
- Renders SVG bars in a natively scrollable container
- Has a floating Y-axis overlay with animated transitions
- Emits `scroll` event with the current `viewStart` index
- Bars are clickable with smooth scroll
- Green-to-blue color gradient
- Year separator lines, "NOW" indicator, value labels on every bar

Key implementation details from JSX source:
- Bar width: 48px pitch, 28px bar, 4px border-radius
- SVG height: 340px (290px for bars, 50px for labels)
- Y-axis headroom: 25% above max visible bar
- Y-axis animation: 12% ease per frame via requestAnimationFrame
- Color interpolation: `rgb(16 + ratio*(37-16), 185 + ratio*(99-185), 129 + ratio*(235-129))`
- Month labels: 3-letter abbreviations, January shows full year in bold
- Current month (index 0 if starting from current month) highlighted amber
- Native `<title>` for tooltips

```vue
<template>
  <div class="rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
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
        class="pointer-events-none absolute bottom-[50px] left-0 top-0 z-10 flex w-16 flex-col justify-between py-2 pl-1"
        :style="{
          background: isDark
            ? 'linear-gradient(to right, rgb(30 41 59) 80%, transparent)'
            : 'linear-gradient(to right, rgb(255 255 255) 80%, transparent)',
        }"
      >
        <span
          v-for="(tick, i) in yTicks"
          :key="i"
          class="pr-2 text-right text-[11px] tabular-nums text-slate-400 dark:text-slate-500"
        >
          {{ formatYTick(tick) }}
        </span>
      </div>

      <!-- Scrollable chart container -->
      <div
        ref="scrollEl"
        class="overflow-x-auto overflow-y-hidden pl-16"
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
    <div v-if="timeline.length > 0" class="mt-2 flex items-center justify-between text-xs">
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
```

**Step 2: Verify build**

Run: `export PATH="/usr/local/bin:/usr/bin:/bin:$PATH" && docker exec frontenddevelopment2-node-1 npm run build`
Expected: Build succeeds

**Step 3: Commit**

```bash
git add frontend/src/components/dashboard/IncomeTimeline.vue
git commit -m "Replace Chart.js bar chart with custom SVG scrollable timeline"
```

---

### Task 3: Dynamic Summary Cards + DashboardView Wiring

**Files:**
- Modify: `frontend/src/components/dashboard/SummaryCards.vue`
- Modify: `frontend/src/views/DashboardView.vue`

**Step 1: Rewrite SummaryCards.vue to support dynamic timeline data**

Replace `frontend/src/components/dashboard/SummaryCards.vue` with:

```vue
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
```

**Step 2: Update DashboardView.vue to wire timeline + cards**

Replace `frontend/src/views/DashboardView.vue` with:

```vue
<template>
  <AppLayout>
    <div v-if="portfolio.loading && !portfolio.hasData">
      <LoadingSpinner />
    </div>
    <div v-else-if="portfolio.error">
      <ErrorAlert :message="portfolio.error" />
    </div>
    <div v-else class="space-y-6">
      <SummaryCards
        v-if="portfolio.summary"
        :summary="portfolio.summary"
        :timeline-income="timelineIncome"
        :timeline-view-start="timelineViewStart"
        :projection-data="projection.data"
        :proj-years="projection.years"
      />

      <IncomeTimeline
        v-if="portfolio.calendar"
        :monthly-income="portfolio.calendar.monthly_income"
        :projection-data="projection.data"
        :proj-years="projection.years"
        :div-growth="projection.divGrowth / 100"
        :price-growth="projection.priceGrowth / 100"
        @scroll="timelineViewStart = $event"
      />

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <SectorPieChart v-if="portfolio.sectors" :sectors="portfolio.sectors" />
        <TopEarners :holdings="portfolio.holdings" />
      </div>

      <BestBuyWindows :windows="portfolio.calendar?.investment_windows || []" />
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import AppLayout from '../components/layout/AppLayout.vue'
import LoadingSpinner from '../components/shared/LoadingSpinner.vue'
import ErrorAlert from '../components/shared/ErrorAlert.vue'
import SummaryCards from '../components/dashboard/SummaryCards.vue'
import IncomeTimeline from '../components/dashboard/IncomeTimeline.vue'
import SectorPieChart from '../components/dashboard/SectorPieChart.vue'
import TopEarners from '../components/dashboard/TopEarners.vue'
import BestBuyWindows from '../components/dashboard/BestBuyWindows.vue'
import { usePortfolioStore } from '../stores/portfolio'
import { useProjectionStore } from '../stores/projection'

const portfolio = usePortfolioStore()
const projection = useProjectionStore()

const timelineViewStart = ref(0)

// Compute extended timeline income from IncomeTimeline's internal logic
// This is passed through to SummaryCards for dynamic updates
const timelineIncome = computed(() => {
  const mi = portfolio.calendar?.monthly_income
  if (!mi || mi.length === 0) return []

  const startYear = new Date().getFullYear()
  const currentMonth = new Date().getMonth()
  const basePortfolio = projection.data[0]?.portfolio_value || 1
  const divGrowth = projection.divGrowth / 100
  const projYears = projection.years
  const data = []
  const totalMonths = projYears * 12

  for (let m = currentMonth; m <= totalMonths; m++) {
    const yearFrac = m / 12
    const mIdx = m % 12
    const divMultiplier = Math.pow(1 + divGrowth, yearFrac)

    const lowerYear = Math.floor(yearFrac)
    const upperYear = Math.min(Math.ceil(yearFrac), projYears)
    const frac = yearFrac - lowerYear
    const lowerVal = projection.data[lowerYear]?.portfolio_value || basePortfolio
    const upperVal = projection.data[upperYear]?.portfolio_value || lowerVal
    const interpolatedValue = lowerVal + (upperVal - lowerVal) * frac
    const valueMultiplier = interpolatedValue / basePortfolio

    const baseIncome = mi[mIdx]?.income || 0
    const scaled = baseIncome * divMultiplier * valueMultiplier

    data.push({
      income: Math.round(scaled * 100) / 100,
      year: Math.floor(yearFrac),
      monthIndex: mIdx,
    })
  }
  return data
})

onMounted(async () => {
  if (!portfolio.hasData) {
    await portfolio.fetchAll()
  }
  if (projection.data.length === 0) {
    await projection.fetchProjection()
  }
})
</script>
```

**Step 3: Verify build**

Run: `export PATH="/usr/local/bin:/usr/bin:/bin:$PATH" && docker exec frontenddevelopment2-node-1 npm run build`
Expected: Build succeeds

**Step 4: Commit**

```bash
git add frontend/src/components/dashboard/SummaryCards.vue frontend/src/views/DashboardView.vue
git commit -m "Add dynamic summary cards that update with timeline scroll position"
```

---

### Task 4: Portfolio Table Pagination

**Files:**
- Modify: `frontend/src/components/portfolio/HoldingsTable.vue`

**Step 1: Add pagination to HoldingsTable.vue**

Add pagination state, a computed `paginatedRows`, and a pagination bar to the template. The key changes:

1. Add refs: `perPage = ref(10)`, `currentPage = ref(1)`
2. Add computed `paginatedRows` that slices `sortedRows`
3. Add watchers to reset `currentPage` when search or sort changes
4. Add pagination UI at bottom: showing range, prev/next buttons, per-page dropdown

In the `<script setup>` section, after the `sortedRows` computed, add:

```javascript
const perPage = ref(10)
const currentPage = ref(1)

const totalPages = computed(() => Math.ceil(sortedRows.value.length / perPage.value))

const paginatedRows = computed(() => {
  const start = (currentPage.value - 1) * perPage.value
  return sortedRows.value.slice(start, start + perPage.value)
})

watch([search, sortCol, sortDir], () => {
  currentPage.value = 1
})

watch(perPage, () => {
  currentPage.value = 1
})
```

In the template, replace `sortedRows` with `paginatedRows` in the `v-for`, and add the pagination bar after the table. Also update the footer text.

The full pagination bar template (add after the closing `</div>` of the table wrapper, before the existing `<p>` tag):

```html
<div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
  <div class="flex items-center gap-2">
    <label class="text-xs text-slate-500 dark:text-slate-400">Per page</label>
    <select
      v-model.number="perPage"
      class="rounded border border-slate-300 bg-white px-2 py-1 text-xs dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100"
    >
      <option :value="10">10</option>
      <option :value="25">25</option>
      <option :value="50">50</option>
    </select>
  </div>
  <div class="flex items-center gap-1">
    <button
      :disabled="currentPage <= 1"
      @click="currentPage--"
      class="rounded px-2 py-1 text-xs font-medium text-slate-600 hover:bg-slate-100 disabled:opacity-40 dark:text-slate-300 dark:hover:bg-slate-700"
    >
      Prev
    </button>
    <span class="px-2 text-xs tabular-nums text-slate-500 dark:text-slate-400">
      {{ (currentPage - 1) * perPage + 1 }}-{{ Math.min(currentPage * perPage, sortedRows.length) }}
      of {{ sortedRows.length }}
    </span>
    <button
      :disabled="currentPage >= totalPages"
      @click="currentPage++"
      class="rounded px-2 py-1 text-xs font-medium text-slate-600 hover:bg-slate-100 disabled:opacity-40 dark:text-slate-300 dark:hover:bg-slate-700"
    >
      Next
    </button>
  </div>
</div>
```

**Step 2: Verify build**

Run: `export PATH="/usr/local/bin:/usr/bin:/bin:$PATH" && docker exec frontenddevelopment2-node-1 npm run build`
Expected: Build succeeds

**Step 3: Commit**

```bash
git add frontend/src/components/portfolio/HoldingsTable.vue
git commit -m "Add pagination to holdings table with 10/25/50 per page options"
```

---

### Task 5: Explanation Texts Across Components

**Files:**
- Modify: `frontend/src/components/dashboard/SectorPieChart.vue`
- Modify: `frontend/src/components/dashboard/TopEarners.vue`
- Modify: `frontend/src/components/dashboard/BestBuyWindows.vue`
- Modify: `frontend/src/components/projection/GrowthChart.vue`
- Modify: `frontend/src/components/projection/DividendGrowthChart.vue`
- Modify: `frontend/src/components/calendar/MonthlyOverview.vue`
- Modify: `frontend/src/components/calendar/UpcomingEvents.vue`
- Modify: `frontend/src/views/ProjectionView.vue`

**Step 1: Add subtitles to dashboard components**

For each component, add a `<p>` subtitle after the `<h3>` title.

**SectorPieChart.vue** -- after the `<h3>` tag, add:
```html
<p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Portfolio value distribution (EUR)</p>
```

**TopEarners.vue** -- after the `<h3>` tag, add:
```html
<p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Your highest-paying holdings (EUR)</p>
```

**BestBuyWindows.vue** -- after the `<h3>` tag, add:
```html
<p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Buy before these dates to capture upcoming dividends</p>
```

Also in BestBuyWindows.vue, after the last window div (before closing `</div>` of the v-else), add the T+1 settlement note:
```html
<p class="mt-3 text-[11px] leading-relaxed text-slate-400 dark:text-slate-500">
  <span class="font-semibold">T+1 settlement:</span> You must buy at least 1 business day before
  the ex-dividend date. Buying on or after the ex-date means the dividend goes to the seller.
  "Buy by" dates account for weekends. Windows are grouped within 7 days and ranked by total
  capturable income.
</p>
```

**Step 2: Add subtitles to projection components**

**GrowthChart.vue** -- needs props for subtitle values. Add props `monthly` (Number), `priceGrowth` (Number). After `<h3>`, add:
```html
<p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
  {{ '\u20AC' }}{{ monthly }}/mo + DRIP + {{ (priceGrowth).toFixed(1) }}% price growth
</p>
```

Update `ProjectionView.vue` to pass: `:monthly="projection.monthly"` and `:price-growth="projection.priceGrowth"` to GrowthChart.

**DividendGrowthChart.vue** -- add prop `divGrowth` (Number). After `<h3>`, add:
```html
<p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
  Annual dividends with {{ (divGrowth).toFixed(1) }}% dividend growth
</p>
```

Update `ProjectionView.vue` to pass: `:div-growth="projection.divGrowth"` to DividendGrowthChart.

**ProjectionView.vue** -- add "How this projection works" info box before the controls. Insert after the `<h1>` heading:

```html
<div class="rounded-lg border border-sky-200 bg-sky-50 p-5 dark:border-sky-800 dark:bg-sky-900/20">
  <h4 class="text-sm font-bold text-sky-700 dark:text-sky-300">How this projection works</h4>
  <p class="mt-2 text-sm leading-relaxed text-sky-800 dark:text-sky-200">
    <span class="font-semibold">Starting point:</span> Your current portfolio with a weighted
    dividend yield. Each month the model adds your contribution, reinvests all dividends (DRIP),
    and applies stock price growth. The dividend growth rate increases the yield annually to
    simulate companies raising their dividends over time. All values are in EUR.
  </p>
</div>
```

**Step 3: Add subtitles to calendar components**

**UpcomingEvents.vue** -- after `<h3>`, add:
```html
<p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
  Next ex-dividend and payment dates for your holdings. Buy before the ex-dividend date to qualify for the next payment.
</p>
```

**MonthlyOverview.vue** -- after `<h3>`, add:
```html
<p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
  Which stocks pay dividends in each month and your estimated income
</p>
```

**Step 4: Verify build**

Run: `export PATH="/usr/local/bin:/usr/bin:/bin:$PATH" && docker exec frontenddevelopment2-node-1 npm run build`
Expected: Build succeeds

**Step 5: Commit**

```bash
git add frontend/src/components/dashboard/SectorPieChart.vue frontend/src/components/dashboard/TopEarners.vue frontend/src/components/dashboard/BestBuyWindows.vue frontend/src/components/projection/GrowthChart.vue frontend/src/components/projection/DividendGrowthChart.vue frontend/src/components/calendar/MonthlyOverview.vue frontend/src/components/calendar/UpcomingEvents.vue frontend/src/views/ProjectionView.vue
git commit -m "Add explanation texts and subtitles throughout the app"
```

---

### Task 6: Filter Passed Upcoming Dividends

**Files:**
- Modify: `frontend/src/components/calendar/UpcomingEvents.vue`

**Step 1: Filter out passed events**

In `UpcomingEvents.vue`, add a computed property that filters the events:

```javascript
import { computed } from 'vue'

const props = defineProps({
  events: { type: Array, required: true },
})

const activeEvents = computed(() => {
  return props.events.filter((e) => e.days_until_ex >= 0)
})
```

Then in the template, replace `events` with `activeEvents` in the `v-if` and `v-for`:

- Change `v-if="events.length === 0"` to `v-if="activeEvents.length === 0"`
- Change `v-for="e in events"` to `v-for="e in activeEvents"`

**Step 2: Verify build**

Run: `export PATH="/usr/local/bin:/usr/bin:/bin:$PATH" && docker exec frontenddevelopment2-node-1 npm run build`
Expected: Build succeeds

**Step 3: Commit**

```bash
git add frontend/src/components/calendar/UpcomingEvents.vue
git commit -m "Filter out passed dividend events from upcoming list"
```

---

### Task 7: Manual Stock Entry with Combobox

**Files:**
- Modify: `frontend/src/components/import/ManualAddForm.vue`

**Step 1: Replace select with combobox and add "create new" flow**

Replace the entire `ManualAddForm.vue`. The new version:
- Has a text input that filters stocks as you type
- Shows dropdown of matching stocks
- Has "Add new stock" option when no match
- When creating a new stock, shows additional fields (ticker, name, price, dividend_per_share, sector)
- Creates stock via `POST /stocks` first, then creates holding

```vue
<template>
  <div class="rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Add Holding Manually</h3>
    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
      Select an existing stock or type to create a new one
    </p>

    <form @submit.prevent="handleSubmit" class="mt-4 space-y-4">
      <!-- Stock search / select -->
      <div class="relative">
        <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Stock</label>
        <input
          ref="searchInput"
          v-model="searchQuery"
          @focus="dropdownOpen = true"
          @input="dropdownOpen = true"
          type="text"
          placeholder="Search or type a new ticker..."
          autocomplete="off"
          class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100"
        />

        <!-- Selected stock badge -->
        <div
          v-if="selectedStock && !dropdownOpen"
          class="mt-1 flex items-center gap-2"
        >
          <span class="rounded bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
            {{ selectedStock.ticker }}
          </span>
          <span class="text-xs text-slate-500 dark:text-slate-400">{{ selectedStock.name }}</span>
          <button
            type="button"
            @click="clearSelection"
            class="text-xs text-slate-400 hover:text-red-500"
          >
            &#x2715;
          </button>
        </div>

        <!-- Dropdown -->
        <div
          v-if="dropdownOpen && searchQuery.length > 0"
          class="absolute z-20 mt-1 max-h-48 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-lg dark:border-slate-600 dark:bg-slate-700"
        >
          <button
            v-for="s in filteredStocks"
            :key="s.id"
            type="button"
            @click="selectStock(s)"
            class="flex w-full items-center justify-between px-3 py-2 text-left text-sm hover:bg-slate-50 dark:hover:bg-slate-600"
          >
            <div>
              <span class="font-medium text-slate-800 dark:text-slate-100">{{ s.ticker }}</span>
              <span class="ml-2 text-slate-500 dark:text-slate-400">{{ s.name }}</span>
            </div>
            <span class="text-xs text-slate-400 dark:text-slate-500">{{ s.sector }}</span>
          </button>

          <!-- Create new option -->
          <button
            type="button"
            @click="startCreating"
            class="flex w-full items-center gap-2 border-t border-slate-100 px-3 py-2 text-left text-sm text-blue-600 hover:bg-blue-50 dark:border-slate-600 dark:text-blue-400 dark:hover:bg-slate-600"
          >
            <span class="text-lg leading-none">+</span>
            <span>Add new stock "{{ searchQuery.toUpperCase() }}"</span>
          </button>
        </div>
      </div>

      <!-- New stock fields (only when creating) -->
      <div v-if="creatingNew" class="space-y-3 rounded-lg border border-blue-200 bg-blue-50/50 p-4 dark:border-blue-800 dark:bg-blue-900/10">
        <p class="text-xs font-medium text-blue-700 dark:text-blue-400">New stock details</p>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Ticker</label>
            <input
              v-model="newStock.ticker"
              type="text"
              required
              class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm uppercase text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100"
            />
          </div>
          <div>
            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Name</label>
            <input
              v-model="newStock.name"
              type="text"
              required
              class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100"
            />
          </div>
          <div>
            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Price (USD)</label>
            <input
              v-model.number="newStock.price"
              type="number"
              step="0.01"
              min="0"
              required
              class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm tabular-nums text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100"
            />
          </div>
          <div>
            <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Annual Dividend (USD)</label>
            <input
              v-model.number="newStock.dividend_per_share"
              type="number"
              step="0.01"
              min="0"
              required
              class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm tabular-nums text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100"
            />
          </div>
        </div>
        <div>
          <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Sector</label>
          <select
            v-model="newStock.sector"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100"
          >
            <option value="">Select sector...</option>
            <option v-for="sec in sectors" :key="sec" :value="sec">{{ sec }}</option>
          </select>
        </div>
      </div>

      <!-- Holding fields -->
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
          <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Shares</label>
          <input
            type="number"
            v-model.number="form.shares"
            step="0.0001"
            min="0"
            required
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm tabular-nums text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100"
          />
        </div>
        <div>
          <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Invested (USD)</label>
          <input
            type="number"
            v-model.number="form.invested"
            step="0.01"
            min="0"
            required
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm tabular-nums text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100"
          />
        </div>
      </div>

      <div>
        <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Date Bought</label>
        <input
          type="date"
          v-model="form.bought_on"
          class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 dark:[color-scheme:dark]"
        />
      </div>

      <button
        type="submit"
        :disabled="submitting || (!selectedStock && !creatingNew)"
        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50 dark:bg-blue-500 dark:hover:bg-blue-600"
      >
        {{ submitting ? 'Adding...' : 'Add Holding' }}
      </button>

      <div
        v-if="success"
        class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-400"
      >
        {{ success }}
      </div>
      <div
        v-if="error"
        class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400"
      >
        {{ error }}
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import api from '../../api/axios'
import { usePortfolioStore } from '../../stores/portfolio'
import { useStockStore } from '../../stores/stocks'

const portfolio = usePortfolioStore()
const stocks = useStockStore()

const submitting = ref(false)
const success = ref('')
const error = ref('')

const searchQuery = ref('')
const dropdownOpen = ref(false)
const selectedStock = ref(null)
const creatingNew = ref(false)
const searchInput = ref(null)

const sectors = [
  'Technology', 'Real Estate', 'Healthcare', 'Energy',
  'Consumer Defensive', 'Consumer Cyclical', 'Financial Services',
  'Industrials', 'Basic Materials', 'Utilities',
]

const newStock = reactive({
  ticker: '',
  name: '',
  price: null,
  dividend_per_share: null,
  sector: '',
})

const form = reactive({
  shares: null,
  invested: null,
  bought_on: '',
})

const filteredStocks = computed(() => {
  const q = searchQuery.value.toLowerCase().trim()
  if (!q) return []
  return stocks.stocks.filter(
    (s) => s.ticker.toLowerCase().includes(q) || s.name.toLowerCase().includes(q),
  ).slice(0, 8)
})

function selectStock(s) {
  selectedStock.value = s
  searchQuery.value = s.ticker
  dropdownOpen.value = false
  creatingNew.value = false
}

function startCreating() {
  creatingNew.value = true
  selectedStock.value = null
  newStock.ticker = searchQuery.value.toUpperCase()
  newStock.name = ''
  newStock.price = null
  newStock.dividend_per_share = null
  newStock.sector = ''
  dropdownOpen.value = false
}

function clearSelection() {
  selectedStock.value = null
  searchQuery.value = ''
  creatingNew.value = false
}

function resetForm() {
  selectedStock.value = null
  searchQuery.value = ''
  creatingNew.value = false
  Object.assign(newStock, { ticker: '', name: '', price: null, dividend_per_share: null, sector: '' })
  Object.assign(form, { shares: null, invested: null, bought_on: '' })
}

async function handleSubmit() {
  submitting.value = true
  success.value = ''
  error.value = ''

  try {
    let stockId

    if (creatingNew.value) {
      // Create the stock first
      const { data: created } = await api.post('/stocks', {
        ticker: newStock.ticker,
        name: newStock.name,
        price: newStock.price,
        dividend_per_share: newStock.dividend_per_share,
        sector: newStock.sector,
      })
      stockId = created.id
      // Refresh stocks list
      await stocks.fetchStocks()
    } else if (selectedStock.value) {
      stockId = selectedStock.value.id
    } else {
      error.value = 'Please select or create a stock.'
      submitting.value = false
      return
    }

    await portfolio.createHolding({
      stock_id: stockId,
      shares: form.shares,
      invested: form.invested,
      bought_on: form.bought_on || undefined,
    })

    success.value = 'Holding added successfully.'
    resetForm()
  } catch (e) {
    error.value = e.response?.data?.error || 'Failed to add holding.'
  } finally {
    submitting.value = false
  }
}

// Close dropdown when clicking outside
function handleClickOutside(e) {
  if (searchInput.value && !searchInput.value.closest('.relative')?.contains(e.target)) {
    dropdownOpen.value = false
  }
}

onMounted(() => {
  if (stocks.stocks.length === 0) {
    stocks.fetchStocks()
  }
  document.addEventListener('click', handleClickOutside)
})
</script>
```

**Step 2: Verify build**

Run: `export PATH="/usr/local/bin:/usr/bin:/bin:$PATH" && docker exec frontenddevelopment2-node-1 npm run build`
Expected: Build succeeds

**Step 3: Commit**

```bash
git add frontend/src/components/import/ManualAddForm.vue
git commit -m "Replace stock select with combobox supporting search and new stock creation"
```

---

## Post-Implementation

After all 7 tasks are complete, run a final build check and verify the app visually in the browser at `http://localhost:5173`.
