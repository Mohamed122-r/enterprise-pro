import { ref } from 'vue'

export function useNotifications() {
  const notifications = ref([])
  const unreadCount = ref(0)

  const addNotification = (notification) => {
    notifications.value.unshift({
      id: Date.now(),
      timestamp: new Date(),
      ...notification
    })
    unreadCount.value++
  }

  const removeNotification = (id) => {
    const index = notifications.value.findIndex(n => n.id === id)
    if (index !== -1) {
      notifications.value.splice(index, 1)
    }
  }

  const markAsRead = (id) => {
    const notification = notifications.value.find(n => n.id === id)
    if (notification && !notification.read) {
      notification.read = true
      unreadCount.value = Math.max(0, unreadCount.value - 1)
    }
  }

  const clearAll = () => {
    notifications.value = []
    unreadCount.value = 0
  }

  const showSuccess = (message) => {
    addNotification({
      type: 'success',
      title: 'Success',
      message,
      duration: 5000
    })
  }

  const showError = (message) => {
    addNotification({
      type: 'error',
      title: 'Error',
      message,
      duration: 8000
    })
  }

  const showWarning = (message) => {
    addNotification({
      type: 'warning',
      title: 'Warning',
      message,
      duration: 6000
    })
  }

  return {
    notifications,
    unreadCount,
    addNotification,
    removeNotification,
    markAsRead,
    clearAll,
    showSuccess,
    showError,
    showWarning
  }
}