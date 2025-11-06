<template>
  <div class="bg-white shadow rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200">
      <h3 class="text-lg font-medium text-gray-900">Recent Activities</h3>
      <p class="mt-1 text-sm text-gray-500">Latest system activities and events</p>
    </div>
    
    <div class="p-6">
      <div v-if="loading" class="flex justify-center py-8">
        <LoadingSpinner size="medium" />
      </div>
      
      <div v-else-if="activities.length === 0" class="text-center py-8">
        <div class="text-gray-400">
          <CalendarIcon class="mx-auto h-12 w-12" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">No activities</h3>
          <p class="mt-1 text-sm text-gray-500">Get started by creating a new record.</p>
        </div>
      </div>
      
      <ul v-else class="space-y-4">
        <li
          v-for="activity in activities"
          :key="activity.id"
          class="flex items-start space-x-3"
        >
          <div
            :class="[
              'flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-medium',
              getActivityColor(activity.type)
            ]"
          >
            {{ getActivityInitial(activity.type) }}
          </div>
          
          <div class="min-w-0 flex-1">
            <p class="text-sm text-gray-800">
              <span class="font-medium">{{ activity.user?.name || 'System' }}</span>
              {{ activity.description }}
            </p>
            <p class="text-xs text-gray-500 mt-1">
              {{ formatDateTime(activity.created_at) }}
            </p>
          </div>
          
          <div class="flex-shrink-0">
            <span
              :class="[
                'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium',
                getStatusClass(activity.action)
              ]"
            >
              {{ activity.action }}
            </span>
          </div>
        </li>
      </ul>
      
      <div v-if="activities.length > 0" class="mt-4 text-center">
        <router-link
          to="/activities"
          class="text-sm font-medium text-indigo-600 hover:text-indigo-500"
        >
          View all activities
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { CalendarIcon } from '@heroicons/vue/24/outline'
import { dashboardApi } from '@/utils/api'
import { formatDateTime } from '@/utils/formatters'
import LoadingSpinner from '@/components/UI/LoadingSpinner.vue'

const activities = ref([])
const loading = ref(true)

const loadActivities = async () => {
  try {
    const response = await dashboardApi.recentActivities()
    activities.value = response.data.data
  } catch (error) {
    console.error('Failed to load activities:', error)
  } finally {
    loading.value = false
  }
}

const getActivityColor = (type) => {
  const colors = {
    created: 'bg-green-500',
    updated: 'bg-blue-500',
    deleted: 'bg-red-500',
    login: 'bg-purple-500',
    default: 'bg-gray-500'
  }
  return colors[type] || colors.default
}

const getActivityInitial = (type) => {
  const initials = {
    created: 'C',
    updated: 'U',
    deleted: 'D',
    login: 'L'
  }
  return initials[type] || 'A'
}

const getStatusClass = (action) => {
  const classes = {
    created: 'bg-green-100 text-green-800',
    updated: 'bg-blue-100 text-blue-800',
    deleted: 'bg-red-100 text-red-800',
    login: 'bg-purple-100 text-purple-800',
    default: 'bg-gray-100 text-gray-800'
  }
  return classes[action] || classes.default
}

onMounted(() => {
  loadActivities()
})
</script>