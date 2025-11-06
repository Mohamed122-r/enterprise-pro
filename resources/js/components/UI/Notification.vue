<template>
  <TransitionGroup name="notification">
    <div
      v-for="notification in notifications"
      :key="notification.id"
      :class="[
        'notification',
        `notification--${notification.type}`,
        { 'notification--closing': notification.closing }
      ]"
      @mouseenter="pauseTimeout(notification)"
      @mouseleave="resumeTimeout(notification)"
    >
      <div class="notification__icon">
        <component :is="getIcon(notification.type)" class="h-5 w-5" />
      </div>
      
      <div class="notification__content">
        <h4 class="notification__title">{{ notification.title }}</h4>
        <p class="notification__message">{{ notification.message }}</p>
      </div>
      
      <button
        class="notification__close"
        @click="removeNotification(notification.id)"
      >
        <XMarkIcon class="h-4 w-4" />
      </button>
    </div>
  </TransitionGroup>
</template>

<script setup>
import { ref, onUnmounted } from 'vue'
import {
  CheckCircleIcon,
  ExclamationTriangleIcon,
  XCircleIcon,
  InformationCircleIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline'

const notifications = ref([])
let nextId = 1
const timeouts = new Map()

const getIcon = (type) => {
  const icons = {
    success: CheckCircleIcon,
    warning: ExclamationTriangleIcon,
    error: XCircleIcon,
    info: InformationCircleIcon
  }
  return icons[type] || InformationCircleIcon
}

const addNotification = (notification) => {
  const id = nextId++
  const notificationWithId = {
    id,
    type: 'info',
    duration: 5000,
    ...notification
  }

  notifications.value.push(notificationWithId)

  if (notificationWithId.duration > 0) {
    const timeout = setTimeout(() => {
      removeNotification(id)
    }, notificationWithId.duration)
    
    timeouts.set(id, timeout)
  }

  return id
}

const removeNotification = (id) => {
  const notification = notifications.value.find(n => n.id === id)
  if (notification) {
    notification.closing = true
    
    setTimeout(() => {
      notifications.value = notifications.value.filter(n => n.id !== id)
      clearTimeout(timeouts.get(id))
      timeouts.delete(id)
    }, 300)
  }
}

const pauseTimeout = (notification) => {
  const timeout = timeouts.get(notification.id)
  if (timeout) {
    clearTimeout(timeout)
    timeouts.delete(notification.id)
  }
}

const resumeTimeout = (notification) => {
  if (notification.duration > 0) {
    const timeout = setTimeout(() => {
      removeNotification(notification.id)
    }, notification.duration)
    
    timeouts.set(notification.id, timeout)
  }
}

const clearAll = () => {
  notifications.value.forEach(notification => {
    clearTimeout(timeouts.get(notification.id))
  })
  timeouts.clear()
  notifications.value = []
}

onUnmounted(() => {
  clearAll()
})

// Expose methods to parent component
defineExpose({
  addNotification,
  removeNotification,
  clearAll
})
</script>

<style scoped>
.notification {
  @apply flex items-start p-4 mb-2 rounded-lg shadow-lg bg-white border-l-4 transform transition-all duration-300 ease-in-out;
}

.notification--success {
  @apply border-green-500;
}

.notification--warning {
  @apply border-yellow-500;
}

.notification--error {
  @apply border-red-500;
}

.notification--info {
  @apply border-blue-500;
}

.notification__icon {
  @apply flex-shrink-0 mr-3;
}

.notification--success .notification__icon {
  @apply text-green-500;
}

.notification--warning .notification__icon {
  @apply text-yellow-500;
}

.notification--error .notification__icon {
  @apply text-red-500;
}

.notification--info .notification__icon {
  @apply text-blue-500;
}

.notification__content {
  @apply flex-1;
}

.notification__title {
  @apply text-sm font-medium text-gray-900;
}

.notification__message {
  @apply mt-1 text-sm text-gray-600;
}

.notification__close {
  @apply flex-shrink-0 ml-4 text-gray-400 hover:text-gray-600 transition-colors;
}

.notification-enter-from {
  @apply opacity-0 translate-x-full;
}

.notification-enter-to {
  @apply opacity-100 translate-x-0;
}

.notification-leave-from {
  @apply opacity-100 translate-x-0;
}

.notification-leave-to {
  @apply opacity-0 translate-x-full;
}

.notification--closing {
  @apply opacity-0 translate-x-full;
}
</style>