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
          v-for="link in adminLinks"
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
    </nav>

    <!-- Bottom section -->
    <div class="border-t border-slate-200 px-2 py-3 dark:border-slate-700">
      <!-- User dropdown -->
      <div class="relative">
        <button
          @click="userMenuOpen = !userMenuOpen"
          :title="auth.user?.username || 'Account'"
          :class="[
            'flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700',
            expanded ? '' : 'justify-center',
          ]"
        >
          <UserCircleIcon class="h-5 w-5 shrink-0" />
          <span v-if="expanded" class="truncate">{{ auth.user?.username || 'Account' }}</span>
          <ChevronUpIcon
            v-if="expanded"
            class="ml-auto h-4 w-4 shrink-0 transition-transform duration-200"
            :class="userMenuOpen ? '' : 'rotate-180'"
          />
        </button>

        <!-- Dropdown menu -->
        <div
          v-if="userMenuOpen"
          :class="[
            'absolute z-50 mb-1 rounded-lg border border-slate-200 bg-white py-1 shadow-lg dark:border-slate-600 dark:bg-slate-700',
            expanded ? 'bottom-full left-0 right-0' : 'bottom-0 left-full ml-2 w-44',
          ]"
        >
          <button
            @click="toggle(); userMenuOpen = false"
            class="flex w-full items-center gap-3 px-3 py-2 text-sm text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-600"
          >
            <MoonIcon v-if="!isDark" class="h-4 w-4 shrink-0" />
            <SunIcon v-else class="h-4 w-4 shrink-0" />
            <span>{{ isDark ? 'Light mode' : 'Dark mode' }}</span>
          </button>
          <button
            @click="auth.logout()"
            class="flex w-full items-center gap-3 px-3 py-2 text-sm text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-600"
          >
            <ArrowRightOnRectangleIcon class="h-4 w-4 shrink-0" />
            <span>Sign out</span>
          </button>
        </div>
      </div>

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
import { ref, inject, onMounted, onUnmounted } from 'vue'
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
  CurrencyDollarIcon,
  SunIcon,
  MoonIcon,
  Bars3Icon,
  ArrowRightOnRectangleIcon,
  ChevronDoubleRightIcon,
  UserCircleIcon,
  ChevronUpIcon,
} from '@heroicons/vue/24/outline'

const auth = useAuthStore()
const route = useRoute()
const { isDark, toggle } = useTheme()

const expanded = inject('sidebarExpanded', ref(false))
const mobileOpen = ref(false)
const userMenuOpen = ref(false)

function closeUserMenu(e) {
  if (userMenuOpen.value && !e.target.closest('.relative')) {
    userMenuOpen.value = false
  }
}

onMounted(() => document.addEventListener('click', closeUserMenu))
onUnmounted(() => document.removeEventListener('click', closeUserMenu))

const userLinks = [
  { to: '/', label: 'Dashboard', icon: HomeIcon },
  { to: '/portfolio', label: 'Portfolio', icon: BriefcaseIcon },
  { to: '/projection', label: 'Projection', icon: ArrowTrendingUpIcon },
  { to: '/calendar', label: 'Calendar', icon: CalendarIcon },
  { to: '/import', label: 'Import', icon: ArrowUpTrayIcon },
]

const adminLinks = [
  { to: '/admin/overview', label: 'Overview', icon: HomeIcon },
  { to: '/admin/users', label: 'Users', icon: UsersIcon },
  { to: '/admin/stocks', label: 'Stocks', icon: CurrencyDollarIcon },
]

function isActive(to) {
  if (to === '/') return route.path === '/'
  return route.path.startsWith(to)
}
</script>
