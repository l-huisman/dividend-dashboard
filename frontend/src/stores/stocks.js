import { defineStore } from 'pinia'
import api from '../api/axios'

export const useStockStore = defineStore('stocks', {
  state: () => ({
    stocks: [],
    total: 0,
    page: 1,
    limit: 50,
    search: '',
    sector: '',
    sort: 'ticker',
    direction: 'asc',
    loading: false,
    error: null,
  }),

  actions: {
    async fetchStocks() {
      this.loading = true
      this.error = null
      try {
        const params = {
          page: this.page,
          limit: this.limit,
          sort: this.sort,
          direction: this.direction,
        }
        if (this.search) params.search = this.search
        if (this.sector) params.sector = this.sector

        const { data } = await api.get('/stocks', { params })
        this.stocks = data.data
        this.total = data.total
      } catch (e) {
        this.error = e.response?.data?.error || 'Failed to load stocks'
      } finally {
        this.loading = false
      }
    },

    async refreshStock(ticker) {
      const { data } = await api.post(`/stocks/refresh/${ticker}`)
      return data
    },

    setSearch(value) {
      this.search = value
      this.page = 1
    },

    setSector(value) {
      this.sector = value
      this.page = 1
    },

    setSort(column) {
      if (this.sort === column) {
        this.direction = this.direction === 'asc' ? 'desc' : 'asc'
      } else {
        this.sort = column
        this.direction = 'asc'
      }
    },

    setPage(page) {
      this.page = page
    },
  },
})
