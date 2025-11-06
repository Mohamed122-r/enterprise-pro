import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('auth_token') || null)
  const isLoading = ref(false)

  const isAuthenticated = computed(() => !!token.value)
  const userRole = computed(() => user.value?.role?.name)
  
  const hasPermission = (permission) => {
    return user.value?.role?.permissions?.includes(permission) || false
  }

  // دالة تسجيل الدخول المعدلة
  const login = async (credentials) => {
    isLoading.value = true
    try {
      // محاكاة API call - استبدل هذا ب API حقيقي
      await new Promise(resolve => setTimeout(resolve, 1500))
      
      // بيانات مستخدم وهمية للعرض
      const userData = {
        id: 1,
        name: 'John Doe',
        email: credentials.email,
        role: {
          name: 'admin',
          permissions: ['users.read', 'users.write', 'contacts.manage']
        },
        avatar: '/images/avatars/1.jpg'
      }
      
      const authToken = 'fake-jwt-token-' + Date.now()
      
      user.value = userData
      token.value = authToken
      
      localStorage.setItem('auth_token', authToken)
      localStorage.setItem('user', JSON.stringify(userData))
      
      return { success: true }
    } catch (error) {
      console.error('Login failed:', error)
      return { 
        success: false, 
        message: error.response?.data?.message || 'Login failed' 
      }
    } finally {
      isLoading.value = false
    }
  }

  const logout = () => {
    try {
      // محاكاة API call للخروج
      console.log('Logging out...')
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      user.value = null
      token.value = null
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user')
    }
  }

  const fetchUser = async () => {
    try {
      // محاكاة جلب بيانات المستخدم
      const savedUser = localStorage.getItem('user')
      if (savedUser) {
        user.value = JSON.parse(savedUser)
      }
    } catch (error) {
      console.error('Failed to fetch user:', error)
      logout()
    }
  }

  const initialize = () => {
    if (token.value) {
      fetchUser()
    }
  }

  // الدالة الجديدة التي يستخدمها مكون Login
  const setAuth = (newToken, userData) => {
    token.value = newToken
    user.value = userData
    localStorage.setItem('auth_token', newToken)
    localStorage.setItem('user', JSON.stringify(userData))
  }

  return {
    user,
    token,
    isLoading,
    isAuthenticated,
    userRole,
    hasPermission,
    login,
    logout,
    fetchUser,
    initialize,
    setAuth
  }
})
