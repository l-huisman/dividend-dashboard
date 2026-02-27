<template>
  <nav class="sticky top-0 z-40 border-b border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="flex h-14 items-center justify-between">
        <!-- Brand -->
        <router-link to="/" class="text-lg font-semibold text-slate-800 dark:text-slate-100">
          DividendFlow
        </router-link>

        <!-- Desktop nav links -->
        <div class="hidden items-center gap-1 md:flex">
          <template v-if="auth.isUser">
            <router-link
              v-for="link in userLinks"
              :key="link.to"
              :to="link.to"
              :class="[
                'rounded-lg px-3 py-2 text-sm font-medium',
                isActive(link.to)
                  ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400'
                  : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700'
              ]"
            >
              {{ link.label }}
            </router-link>
          </template>
          <template v-if="auth.isAdmin">
            <router-link
              to="/admin"
              :class="[
                'rounded-lg px-3 py-2 text-sm font-medium',
                isActive('/admin')
                  ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400'
                  : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700'
              ]"
            >
              Users
            </router-link>
          </template>
        </div>

        <!-- Right side -->
        <div class="flex items-center gap-2">
          <!-- Theme toggle -->
          <button
            @click="toggle"
            class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700"
          >
            <MoonIcon v-if="!isDark" class="h-5 w-5" />
            <SunIcon v-else class="h-5 w-5" />
          </button>

          <!-- Username -->
          <span class="hidden text-sm text-slate-500 dark:text-slate-400 sm:block">
            {{ auth.user?.username }}
          </span>

          <!-- Logout -->
          <button
            @click="auth.logout()"
            class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700"
          >
            <ArrowRightOnRectangleIcon class="h-5 w-5" />
          </button>

          <!-- Mobile menu button -->
          <button
            @click="menuOpen = !menuOpen"
            class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700 md:hidden"
          >
            <XMarkIcon v-if="menuOpen" class="h-5 w-5" />
            <Bars3Icon v-else class="h-5 w-5" />
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile menu -->
    <div v-if="menuOpen" class="border-t border-slate-200 dark:border-slate-700 md:hidden">
      <div class="space-y-1 px-4 py-3">
        <template v-if="auth.isUser">
          <router-link
            v-for="link in userLinks"
            :key="link.to"
            :to="link.to"
            @click="menuOpen = false"
            :class="[
              'block rounded-lg px-3 py-2 text-sm font-medium',
              isActive(link.to)
                ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400'
                : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700'
            ]"
          >
            {{ link.label }}
          </router-link>
        </template>
        <template v-if="auth.isAdmin">
          <router-link
            to="/admin"
            @click="menuOpen = false"
            :class="[
              'block rounded-lg px-3 py-2 text-sm font-medium',
              isActive('/admin')
                ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400'
                : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700'
            ]"
          >
            Users
          </router-link>
        </template>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { useTheme } from '../../composables/useTheme'
import {
  SunIcon,
  MoonIcon,
  Bars3Icon,
  XMarkIcon,
  ArrowRightOnRectangleIcon,
} from '@heroicons/vue/24/outline'

const auth = useAuthStore()
const route = useRoute()
const { isDark, toggle } = useTheme()

const menuOpen = ref(false)

const userLinks = [
  { to: '/', label: 'Dashboard' },
  { to: '/portfolio', label: 'Portfolio' },
  { to: '/projection', label: 'Projection' },
  { to: '/calendar', label: 'Calendar' },
  { to: '/import', label: 'Import' },
]

function isActive(to) {
  if (to === '/') {
    return route.path === '/'
  }
  return route.path.startsWith(to)
}
</script>
