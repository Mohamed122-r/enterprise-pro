import { defineStore } from 'pinia'
import { invoiceApi, paymentApi } from '@/utils/api'

export const useAccountingStore = defineStore('accounting', {
  state: () => ({
    invoices: [],
    payments: [],
    currentInvoice: null,
    loading: false,
    error: null
  }),

  getters: {
    getInvoiceById: (state) => (id) => {
      return state.invoices.find(invoice => invoice.id === id)
    },
    
    getPaymentsByInvoice: (state) => (invoiceId) => {
      return state.payments.filter(payment => payment.invoice_id === invoiceId)
    },
    
    invoicesByStatus: (state) => {
      return (status) => state.invoices.filter(invoice => invoice.status === status)
    },
    
    overdueInvoices: (state) => {
      const today = new Date().toISOString().split('T')[0]
      return state.invoices.filter(invoice => 
        invoice.status === 'overdue' || 
        (invoice.due_date < today && invoice.status === 'sent')
      )
    },
    
    totalRevenue: (state) => {
      return state.invoices
        .filter(invoice => invoice.status === 'paid')
        .reduce((sum, invoice) => sum + invoice.total, 0)
    }
  },

  actions: {
    // Invoices
    async fetchInvoices(filters = {}) {
      this.loading = true
      this.error = null
      
      try {
        const response = await invoiceApi.list(filters)
        this.invoices = response.data.data
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch invoices'
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchInvoice(id) {
      this.loading = true
      this.error = null
      
      try {
        const response = await invoiceApi.show(id)
        this.currentInvoice = response.data.data
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch invoice'
        throw error
      } finally {
        this.loading = false
      }
    },

    async createInvoice(invoiceData) {
      this.loading = true
      this.error = null
      
      try {
        const response = await invoiceApi.create(invoiceData)
        this.invoices.unshift(response.data.data)
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create invoice'
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateInvoice(id, invoiceData) {
      this.loading = true
      this.error = null
      
      try {
        const response = await invoiceApi.update(id, invoiceData)
        const index = this.invoices.findIndex(invoice => invoice.id === id)
        if (index !== -1) {
          this.invoices[index] = response.data.data
        }
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update invoice'
        throw error
      } finally {
        this.loading = false
      }
    },

    async deleteInvoice(id) {
      this.loading = true
      this.error = null
      
      try {
        await invoiceApi.delete(id)
        this.invoices = this.invoices.filter(invoice => invoice.id !== id)
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to delete invoice'
        throw error
      } finally {
        this.loading = false
      }
    },

    async sendInvoice(id) {
      this.loading = true
      this.error = null
      
      try {
        const response = await invoiceApi.send(id)
        const index = this.invoices.findIndex(invoice => invoice.id === id)
        if (index !== -1) {
          this.invoices[index] = response.data.data
        }
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to send invoice'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Payments
    async fetchPayments(filters = {}) {
      this.loading = true
      this.error = null
      
      try {
        const response = await paymentApi.list(filters)
        this.payments = response.data.data
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch payments'
        throw error
      } finally {
        this.loading = false
      }
    },

    async recordPayment(paymentData) {
      this.loading = true
      this.error = null
      
      try {
        const response = await paymentApi.create(paymentData)
        this.payments.unshift(response.data.data)
        
        // Update the corresponding invoice
        const invoiceIndex = this.invoices.findIndex(
          invoice => invoice.id === paymentData.invoice_id
        )
        if (invoiceIndex !== -1) {
          // Refresh invoice data
          await this.fetchInvoice(paymentData.invoice_id)
        }
        
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to record payment'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Utility methods
    clearCurrentInvoice() {
      this.currentInvoice = null
    },

    clearError() {
      this.error = null
    },

    // Financial calculations
    calculateMonthlyRevenue(year, month) {
      const monthlyInvoices = this.invoices.filter(invoice => {
        const invoiceDate = new Date(invoice.paid_at || invoice.issue_date)
        return invoiceDate.getFullYear() === year && 
               invoiceDate.getMonth() + 1 === month &&
               invoice.status === 'paid'
      })
      
      return monthlyInvoices.reduce((sum, invoice) => sum + invoice.total, 0)
    },

    getRevenueByClient(clientId) {
      const clientInvoices = this.invoices.filter(
        invoice => invoice.client_id === clientId && invoice.status === 'paid'
      )
      
      return clientInvoices.reduce((sum, invoice) => sum + invoice.total, 0)
    }
  }
})