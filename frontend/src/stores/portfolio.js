import { defineStore } from 'pinia'
import api from '../api/axios'

export const usePortfolioStore = defineStore('portfolio', {
  state: () => ({
    holdings: [],
    summary: null,
    sectors: null,
    calendar: null,
    loading: false,
    error: null,
  }),

  getters: {
    holdingCount: (state) => state.holdings.length,
    hasData: (state) => state.summary !== null,
  },

  actions: {
    async fetchHoldings() {
      this.loading = true
      this.error = null
      try {
        const { data } = await api.get('/holdings')
        this.holdings = data
      } catch (e) {
        this.error = e.response?.data?.error || 'Failed to load holdings'
      } finally {
        this.loading = false
      }
    },

    async fetchSummary() {
      try {
        const { data } = await api.get('/portfolio/summary')
        this.summary = data
      } catch (e) {
        this.error = e.response?.data?.error || 'Failed to load summary'
      }
    },

    async fetchSectors() {
      try {
        const { data } = await api.get('/portfolio/sectors')
        this.sectors = data
      } catch (e) {
        this.error = e.response?.data?.error || 'Failed to load sectors'
      }
    },

    async fetchCalendar() {
      try {
        const { data } = await api.get('/portfolio/calendar')
        this.calendar = data
      } catch (e) {
        this.error = e.response?.data?.error || 'Failed to load calendar'
      }
    },

    async fetchAll() {
      this.loading = true
      this.error = null
      try {
        const [holdings, summary, sectors, calendar] = await Promise.all([
          api.get('/holdings'),
          api.get('/portfolio/summary'),
          api.get('/portfolio/sectors'),
          api.get('/portfolio/calendar'),
        ])
        this.holdings = holdings.data
        this.summary = summary.data
        this.sectors = sectors.data
        this.calendar = calendar.data
      } catch (e) {
        this.error = e.response?.data?.error || 'Failed to load portfolio data'
      } finally {
        this.loading = false
      }
    },

    async createHolding(holding) {
      const { data } = await api.post('/holdings', holding)
      await this.fetchAll()
      return data
    },

    async updateHolding(id, holding) {
      const { data } = await api.put(`/holdings/${id}`, holding)
      await this.fetchAll()
      return data
    },

    async deleteHolding(id) {
      await api.delete(`/holdings/${id}`)
      await this.fetchAll()
    },

    async importCsv(csvText) {
      const { data } = await api.post('/holdings/import', csvText, {
        headers: { 'Content-Type': 'text/plain' },
      })
      await this.fetchAll()
      return data
    },

    clearData() {
      this.holdings = []
      this.summary = null
      this.sectors = null
      this.calendar = null
      this.error = null
    },
  },
})
