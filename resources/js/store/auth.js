import { defineStore } from 'pinia'
import { authApi } from '@/utils/api'
import { useRouter } from 'vue-router'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('auth_token') || null,
    isLoading: false
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    userRole: (state) => state.user?.role?.name,
    hasPermission: (state) => (permission) => {
      return state.user?.role?.permissions?.includes(permission) || false
    }
  },

  actions: {
    async login(credentials) {
      this.isLoading = true
      try {
        const response = await authApi.login(credentials)
        
        this.user = response.data.user
        this.token = response.data.token
        
        localStorage.setItem('auth_token', this.token)
        
        return { success: true }
      } catch (error) {
        console.error('Login failed:', error)
        return { 
          success: false, 
          message: error.response?.data?.message || 'Login failed' 
        }
      } finally {
        this.isLoading = false
      }
    },

    async logout() {
      try {
        await authApi.logout()
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        this.user = null
        this.token = null
        localStorage.removeItem('auth_token')
        
        const router = useRouter()
        router.push('/login')
      }
    },

    async fetchUser() {
      try {
        const response = await authApi.user()
        this.user = response.data.user
      } catch (error) {
        console.error('Failed to fetch user:', error)
        this.logout()
      }
    },

    initialize() {
      if (this.token) {
        this.fetchUser()
      }
    }
  }
})