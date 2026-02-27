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

      <div
        v-if="expanded && auth.user"
        class="mt-1 truncate px-3 py-1 text-xs text-slate-400 dark:text-slate-500"
      >
        {{ auth.user.username }}
      </div>

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
import { ref, provide } from 'vue'
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

provide('sidebarExpanded', expanded)

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
