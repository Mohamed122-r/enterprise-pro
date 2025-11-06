import { defineStore } from 'pinia'
import { userApi } from '@/utils/api'

export const useUsersStore = defineStore('users', {
  state: () => ({
    users: [],
    currentUser: null,
    loading: false,
    error: null
  }),

  getters: {
    activeUsers: (state) => state.users.filter(user => user.status),
    getUsersByDepartment: (state) => (departmentId) => {
      return state.users.filter(user => user.department_id === departmentId)
    }
  },

  actions: {
    async fetchUsers(filters = {}) {
      this.loading = true
      this.error = null
      
      try {
        const response = await userApi.list(filters)
        this.users = response.data.data
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch users'
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchUser(id) {
      this.loading = true
      this.error = null
      
      try {
        const response = await userApi.show(id)
        this.currentUser = response.data.data
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch user'
        throw error
      } finally {
        this.loading = false
      }
    },

    async createUser(userData) {
      this.loading = true
      this.error = null
      
      try {
        const response = await userApi.create(userData)
        this.users.unshift(response.data.data)
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create user'
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateUser(id, userData) {
      this.loading = true
      this.error = null
      
      try {
        const response = await userApi.update(id, userData)
        const index = this.users.findIndex(user => user.id === id)
        if (index !== -1) {
          this.users[index] = response.data.data
        }
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update user'
        throw error
      } finally {
        this.loading = false
      }
    },

    async deleteUser(id) {
      this.loading = true
      this.error = null
      
      try {
        await userApi.delete(id)
        this.users = this.users.filter(user => user.id !== id)
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to delete user'
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateUserStatus(id, status) {
      this.loading = true
      this.error = null
      
      try {
        const response = await userApi.updateStatus(id, { status })
        const index = this.users.findIndex(user => user.id === id)
        if (index !== -1) {
          this.users[index].status = status
        }
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update user status'
        throw error
      } finally {
        this.loading = false
      }
    }
  }
})