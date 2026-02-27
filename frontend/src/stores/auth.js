import { defineStore } from 'pinia'
import api from '../api/axios'
import router from '../router'
import { usePortfolioStore } from './portfolio'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('token') || null,
    user: JSON.parse(localStorage.getItem('user') || 'null'),
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    isAdmin: (state) => state.user?.role === 1,
    isUser: (state) => state.user?.role === 0,
  },

  actions: {
    async login(email, password) {
      usePortfolioStore().clearData()
      const { data } = await api.post('/login', { email, password })
      this.token = data.token
      this.user = data.user
      localStorage.setItem('token', data.token)
      localStorage.setItem('user', JSON.stringify(data.user))
    },

    async register(username, email, password) {
      usePortfolioStore().clearData()
      const { data } = await api.post('/register', { username, email, password })
      this.token = data.token
      this.user = data.user
      localStorage.setItem('token', data.token)
      localStorage.setItem('user', JSON.stringify(data.user))
    },

    logout() {
      this.token = null
      this.user = null
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      usePortfolioStore().clearData()
      router.push('/login')
    },
  },
})
