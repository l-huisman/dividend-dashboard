<template>
  <div>
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
              <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-slate-400">Username</th>
              <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-slate-400">Email</th>
              <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-slate-400">Role</th>
              <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-slate-400">Joined</th>
              <th class="px-4 py-3 text-right font-medium text-slate-500 dark:text-slate-400">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="u in users" :key="u.id"
              class="border-b border-slate-100 last:border-0 dark:border-slate-700/50">
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
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
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

const totalPages = computed(() => Math.ceil(total.value / limit))

async function fetchUsers() {
  loading.value = true
  error.value = null
  try {
    const { data } = await api.get('/users', {
      params: { page: page.value, limit },
    })
    users.value = data.data
    total.value = data.total
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load users.'
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
    error.value = err.response?.data?.message || 'Failed to update user role.'
  }
}

async function deleteUser(id) {
  try {
    await api.delete(`/users/${id}`)
    confirmDelete.value = null
    users.value = users.value.filter((u) => u.id !== id)
    total.value--
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to delete user.'
    confirmDelete.value = null
  }
}

function goToPage(p) {
  page.value = p
  fetchUsers()
}

function formatDate(str) {
  return new Date(str).toLocaleDateString('nl-NL')
}

onMounted(fetchUsers)
</script>
