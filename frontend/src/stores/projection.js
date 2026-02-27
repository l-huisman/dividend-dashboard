import { defineStore } from 'pinia'
import api from '../api/axios'

export const useProjectionStore = defineStore('projection', {
  state: () => ({
    monthly: 100,
    years: 20,
    divGrowth: 5,
    priceGrowth: 7,
    data: [],
    loading: false,
    error: null,
  }),

  getters: {
    finalYear: (state) => (state.data.length > 0 ? state.data[state.data.length - 1] : null),
    milestones: (state) => {
      const targets = [500, 1000, 2000, 3000, 5000, 10000]
      return targets.map((target) => {
        const year = state.data.find((d) => d.monthly_dividends >= target / 12)
        return { target, year: year ? year.year : null, reached: !!year }
      })
    },
  },

  actions: {
    async fetchProjection() {
      this.loading = true
      this.error = null
      try {
        const { data } = await api.get('/portfolio/projection', {
          params: {
            monthly: this.monthly,
            years: this.years,
            divgrowth: this.divGrowth,
            pricegrowth: this.priceGrowth,
          },
        })
        this.data = data
      } catch (e) {
        this.error = e.response?.data?.error || 'Failed to load projection'
      } finally {
        this.loading = false
      }
    },

    setParams(params) {
      if (params.monthly !== undefined) this.monthly = params.monthly
      if (params.years !== undefined) this.years = params.years
      if (params.divGrowth !== undefined) this.divGrowth = params.divGrowth
      if (params.priceGrowth !== undefined) this.priceGrowth = params.priceGrowth
    },
  },
})
