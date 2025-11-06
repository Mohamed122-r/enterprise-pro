import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/store/auth'

export function useAuth() {
  const router = useRouter()
  const authStore = useAuthStore()
  
  const loading = ref(false)
  const error = ref('')

  const isAuthenticated = computed(() => authStore.isAuthenticated)
  const user = computed(() => authStore.user)
  const userRole = computed(() => authStore.userRole)

  const login = async (credentials) => {
    loading.value = true
    error.value = ''
    
    try {
      const result = await authStore.login(credentials)
      
      if (result.success) {
        router.push('/')
        return { success: true }
      } else {
        error.value = result.message
        return { success: false, message: result.message }
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Login failed'
      return { success: false, message: error.value }
    } finally {
      loading.value = false
    }
  }

  const register = async (userData) => {
    loading.value = true
    error.value = ''
    
    try {
      const result = await authStore.register(userData)
      
      if (result.success) {
        router.push('/')
        return { success: true }
      } else {
        error.value = result.message
        return { success: false, message: result.message }
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Registration failed'
      return { success: false, message: error.value }
    } finally {
      loading.value = false
    }
  }

  const logout = async () => {
    loading.value = true
    
    try {
      await authStore.logout()
      router.push('/login')
    } catch (err) {
      console.error('Logout error:', err)
    } finally {
      loading.value = false
    }
  }

  const checkPermission = (permission) => {
    return authStore.hasPermission(permission)
  }

  const checkAnyPermission = (permissions) => {
    return permissions.some(permission => authStore.hasPermission(permission))
  }

  const checkAllPermissions = (permissions) => {
    return permissions.every(permission => authStore.hasPermission(permission))
  }

  const refreshUser = async () => {
    try {
      await authStore.fetchUser()
    } catch (err) {
      console.error('Failed to refresh user:', err)
    }
  }

  const clearError = () => {
    error.value = ''
  }

  return {
    // State
    loading,
    error,
    
    // Getters
    isAuthenticated,
    user,
    userRole,
    
    // Actions
    login,
    register,
    logout,
    checkPermission,
    checkAnyPermission,
    checkAllPermissions,
    refreshUser,
    clearError
  }
}