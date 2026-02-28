<template>
  <div class="space-y-6">
    <h1 class="text-xl font-semibold text-slate-800 dark:text-slate-100">User Management</h1>

    <!-- Search + Role filter -->
    <div class="flex flex-wrap items-center gap-3">
      <input
        v-model="searchInput"
        @input="onSearch"
        type="text"
        placeholder="Search by username or email..."
        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 dark:placeholder-slate-500 sm:w-72"
      />
      <select
        v-model="roleFilter"
        @change="onRoleChange"
        class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100"
      >
        <option value="">All Roles</option>
        <option value="1">Admin</option>
        <option value="0">User</option>
      </select>
    </div>

    <div v-if="loading">
      <LoadingSpinner />
    </div>
    <div v-else-if="error">
      <ErrorAlert :message="error" />
    </div>
    <div v-else>
      <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800/50">
              <th class="px-4 py-3 text-left">
                <input
                  type="checkbox"
                  :checked="allSelected"
                  :indeterminate="someSelected && !allSelected"
                  @change="toggleSelectAll"
                  class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600"
                />
              </th>
              <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-slate-400">Username</th>
              <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-slate-400">Email</th>
              <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-slate-400">Role</th>
              <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-slate-400">Joined</th>
              <th class="px-4 py-3 text-right font-medium text-slate-500 dark:text-slate-400">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="u in users" :key="u.id"
              class="border-b border-slate-100 last:border-0 dark:border-slate-700/50"
              :class="selected.has(u.id) ? 'bg-blue-50/50 dark:bg-blue-900/10' : ''"
            >
              <td class="px-4 py-3">
                <input
                  v-if="u.id !== currentUserId"
                  type="checkbox"
                  :checked="selected.has(u.id)"
                  @change="toggleSelect(u.id)"
                  class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600"
                />
              </td>
              <td class="max-w-[150px] truncate px-4 py-3 text-slate-800 dark:text-slate-100">{{ u.username }}</td>
              <td class="max-w-[200px] truncate px-4 py-3 text-slate-500 dark:text-slate-400">{{ u.email }}</td>
              <td class="px-4 py-3">
                <span class="rounded-full px-2 py-0.5 text-xs font-medium"
                  :class="u.role === 1
                    ? 'bg-purple-50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400'
                    : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300'">
                  {{ u.role === 1 ? 'Admin' : 'User' }}
                </span>
              </td>
              <td class="px-4 py-3 text-slate-500 dark:text-slate-400">{{ formatDate(u.created_at) }}</td>
              <td class="px-4 py-3 text-right">
                <div v-if="u.id !== currentUserId" class="flex items-center justify-end gap-2">
                  <button @click="toggleRole(u)"
                    class="rounded px-2 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/20">
                    {{ u.role === 1 ? 'Make User' : 'Make Admin' }}
                  </button>
                  <button @click="resetPassword(u.id)"
                    class="rounded px-2 py-1 text-xs font-medium text-amber-600 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-900/20">
                    Reset PW
                  </button>
                  <button v-if="confirmDelete !== u.id" @click="confirmDelete = u.id"
                    class="rounded px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                    Delete
                  </button>
                  <div v-else class="flex items-center gap-1">
                    <button @click="deleteUser(u.id)"
                      class="rounded bg-red-600 px-2 py-1 text-xs font-medium text-white hover:bg-red-700">
                      Confirm
                    </button>
                    <button @click="confirmDelete = null"
                      class="rounded px-2 py-1 text-xs font-medium text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700">
                      Cancel
                    </button>
                  </div>
                </div>
                <span v-else class="text-xs text-slate-400 dark:text-slate-500">You</span>

                <!-- Temp password display -->
                <div v-if="tempPasswords[u.id]" class="mt-1 flex items-center justify-end gap-1">
                  <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs text-slate-700 dark:bg-slate-700 dark:text-slate-300">{{ tempPasswords[u.id] }}</code>
                  <button @click="copyPassword(u.id)"
                    class="rounded px-1.5 py-0.5 text-xs text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700">
                    {{ copied === u.id ? 'Copied!' : 'Copy' }}
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="mt-4 flex items-center justify-between">
        <p class="text-xs text-slate-500 dark:text-slate-400">{{ total }} total users</p>
        <div v-if="totalPages > 1" class="flex items-center gap-1">
          <button
            v-for="p in totalPages"
            :key="p"
            @click="goToPage(p)"
            class="rounded px-2.5 py-1 text-xs font-medium"
            :class="p === page
              ? 'bg-blue-600 text-white'
              : 'text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700'">
            {{ p }}
          </button>
        </div>
      </div>
    </div>

    <!-- Floating bulk action bar -->
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="translate-y-4 opacity-0"
      enter-to-class="translate-y-0 opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="translate-y-0 opacity-100"
      leave-to-class="translate-y-4 opacity-0"
    >
      <div
        v-if="selected.size > 0"
        class="fixed bottom-6 left-1/2 z-40 flex -translate-x-1/2 items-center gap-3 rounded-lg border border-slate-200 bg-white px-4 py-2.5 shadow-lg dark:border-slate-700 dark:bg-slate-800"
      >
        <span class="text-sm font-medium text-slate-600 dark:text-slate-300">{{ selected.size }} selected</span>
        <div class="h-4 w-px bg-slate-200 dark:bg-slate-700"></div>
        <button @click="bulkRole(1)"
          class="rounded px-2.5 py-1 text-xs font-medium text-purple-600 hover:bg-purple-50 dark:text-purple-400 dark:hover:bg-purple-900/20">
          Make Admin
        </button>
        <button @click="bulkRole(0)"
          class="rounded px-2.5 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/20">
          Make User
        </button>
        <button @click="bulkDelete"
          class="rounded px-2.5 py-1 text-xs font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
          Delete
        </button>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import api from '../../api/axios'
import { useAuthStore } from '../../stores/auth'
import LoadingSpinner from '../shared/LoadingSpinner.vue'
import ErrorAlert from '../shared/ErrorAlert.vue'

const authStore = useAuthStore()
const currentUserId = computed(() => authStore.user?.id)

const users = ref([])
const total = ref(0)
const page = ref(1)
const limit = 20
const loading = ref(false)
const error = ref(null)
const confirmDelete = ref(null)

// Search + filter
const searchInput = ref('')
const search = ref('')
const roleFilter = ref('')
let searchTimeout = null

// Password reset
const tempPasswords = reactive({})
const copied = ref(null)

// Bulk selection
const selected = ref(new Set())

const totalPages = computed(() => Math.ceil(total.value / limit))

const selectableUsers = computed(() => users.value.filter(u => u.id !== currentUserId.value))
const allSelected = computed(() => selectableUsers.value.length > 0 && selectableUsers.value.every(u => selected.value.has(u.id)))
const someSelected = computed(() => selectableUsers.value.some(u => selected.value.has(u.id)))

function onSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    search.value = searchInput.value
    page.value = 1
    fetchUsers()
  }, 300)
}

function onRoleChange() {
  page.value = 1
  fetchUsers()
}

async function fetchUsers() {
  loading.value = true
  error.value = null
  try {
    const params = { page: page.value, limit }
    if (search.value) params.search = search.value
    if (roleFilter.value !== '') params.role = roleFilter.value
    const { data } = await api.get('/users', { params })
    users.value = data.data
    total.value = data.total
  } catch (err) {
    error.value = err.response?.data?.error || 'Failed to load users.'
  } finally {
    loading.value = false
  }
}

async function toggleRole(user) {
  const newRole = user.role === 1 ? 0 : 1
  try {
    await api.put(`/users/${user.id}`, { role: newRole })
    user.role = newRole
  } catch (err) {
    error.value = err.response?.data?.error || 'Failed to update user role.'
  }
}

async function deleteUser(id) {
  try {
    await api.delete(`/users/${id}`)
    confirmDelete.value = null
    users.value = users.value.filter((u) => u.id !== id)
    total.value--
  } catch (err) {
    error.value = err.response?.data?.error || 'Failed to delete user.'
    confirmDelete.value = null
  }
}

async function resetPassword(id) {
  try {
    const { data } = await api.put(`/users/${id}/password`)
    tempPasswords[id] = data.temporary_password
  } catch (err) {
    error.value = err.response?.data?.error || 'Failed to reset password.'
  }
}

async function copyPassword(id) {
  await navigator.clipboard.writeText(tempPasswords[id])
  copied.value = id
  setTimeout(() => { copied.value = null }, 2000)
}

function toggleSelect(id) {
  const next = new Set(selected.value)
  if (next.has(id)) {
    next.delete(id)
  } else {
    next.add(id)
  }
  selected.value = next
}

function toggleSelectAll() {
  if (allSelected.value) {
    selected.value = new Set()
  } else {
    selected.value = new Set(selectableUsers.value.map(u => u.id))
  }
}

async function bulkRole(role) {
  try {
    await api.post('/users/bulk-role', { ids: [...selected.value], role })
    selected.value = new Set()
    fetchUsers()
  } catch (err) {
    error.value = err.response?.data?.error || 'Failed to update roles.'
  }
}

async function bulkDelete() {
  if (!confirm(`Delete ${selected.value.size} user(s)? This cannot be undone.`)) return
  try {
    await api.post('/users/bulk-delete', { ids: [...selected.value] })
    selected.value = new Set()
    fetchUsers()
  } catch (err) {
    error.value = err.response?.data?.error || 'Failed to delete users.'
  }
}

function goToPage(p) {
  page.value = p
  selected.value = new Set()
  fetchUsers()
}

function formatDate(str) {
  return new Date(str).toLocaleDateString('nl-NL')
}

onMounted(fetchUsers)
</script>
