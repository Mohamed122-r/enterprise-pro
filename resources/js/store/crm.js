import { defineStore } from 'pinia'
import { contactApi, opportunityApi, activityApi } from '@/utils/api'

export const useCrmStore = defineStore('crm', {
  state: () => ({
    contacts: [],
    opportunities: [],
    activities: [],
    currentContact: null,
    currentOpportunity: null,
    loading: false,
    error: null
  }),

  getters: {
    getContactById: (state) => (id) => {
      return state.contacts.find(contact => contact.id === id)
    },
    
    getOpportunityById: (state) => (id) => {
      return state.opportunities.find(opportunity => opportunity.id === id)
    },
    
    getActivitiesByContact: (state) => (contactId) => {
      return state.activities.filter(activity => activity.contact_id === contactId)
    },
    
    getOpportunitiesByContact: (state) => (contactId) => {
      return state.opportunities.filter(opportunity => opportunity.contact_id === contactId)
    },
    
    contactsByStatus: (state) => {
      return (status) => state.contacts.filter(contact => contact.status === status)
    },
    
    opportunitiesByStage: (state) => {
      return (stage) => state.opportunities.filter(opportunity => opportunity.stage === stage)
    }
  },

  actions: {
    // Contacts
    async fetchContacts(filters = {}) {
      this.loading = true
      this.error = null
      
      try {
        const response = await contactApi.list(filters)
        this.contacts = response.data.data
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch contacts'
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchContact(id) {
      this.loading = true
      this.error = null
      
      try {
        const response = await contactApi.show(id)
        this.currentContact = response.data.data
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch contact'
        throw error
      } finally {
        this.loading = false
      }
    },

    async createContact(contactData) {
      this.loading = true
      this.error = null
      
      try {
        const response = await contactApi.create(contactData)
        this.contacts.unshift(response.data.data)
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create contact'
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateContact(id, contactData) {
      this.loading = true
      this.error = null
      
      try {
        const response = await contactApi.update(id, contactData)
        const index = this.contacts.findIndex(contact => contact.id === id)
        if (index !== -1) {
          this.contacts[index] = response.data.data
        }
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update contact'
        throw error
      } finally {
        this.loading = false
      }
    },

    async deleteContact(id) {
      this.loading = true
      this.error = null
      
      try {
        await contactApi.delete(id)
        this.contacts = this.contacts.filter(contact => contact.id !== id)
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to delete contact'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Opportunities
    async fetchOpportunities(filters = {}) {
      this.loading = true
      this.error = null
      
      try {
        const response = await opportunityApi.list(filters)
        this.opportunities = response.data.data
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch opportunities'
        throw error
      } finally {
        this.loading = false
      }
    },

    async createOpportunity(opportunityData) {
      this.loading = true
      this.error = null
      
      try {
        const response = await opportunityApi.create(opportunityData)
        this.opportunities.unshift(response.data.data)
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create opportunity'
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateOpportunity(id, opportunityData) {
      this.loading = true
      this.error = null
      
      try {
        const response = await opportunityApi.update(id, opportunityData)
        const index = this.opportunities.findIndex(opportunity => opportunity.id === id)
        if (index !== -1) {
          this.opportunities[index] = response.data.data
        }
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update opportunity'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Activities
    async fetchActivities(filters = {}) {
      this.loading = true
      this.error = null
      
      try {
        const response = await activityApi.list(filters)
        this.activities = response.data.data
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch activities'
        throw error
      } finally {
        this.loading = false
      }
    },

    async createActivity(activityData) {
      this.loading = true
      this.error = null
      
      try {
        const response = await activityApi.create(activityData)
        this.activities.unshift(response.data.data)
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create activity'
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateActivity(id, activityData) {
      this.loading = true
      this.error = null
      
      try {
        const response = await activityApi.update(id, activityData)
        const index = this.activities.findIndex(activity => activity.id === id)
        if (index !== -1) {
          this.activities[index] = response.data.data
        }
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update activity'
        throw error
      } finally {
        this.loading = false
      }
    },

    async completeActivity(id) {
      this.loading = true
      this.error = null
      
      try {
        const response = await activityApi.complete(id)
        const index = this.activities.findIndex(activity => activity.id === id)
        if (index !== -1) {
          this.activities[index] = response.data.data
        }
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to complete activity'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Utility methods
    clearCurrentContact() {
      this.currentContact = null
    },

    clearCurrentOpportunity() {
      this.currentOpportunity = null
    },

    clearError() {
      this.error = null
    }
  }
})