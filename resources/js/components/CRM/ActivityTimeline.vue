<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Activity Timeline</h1>
        <p class="text-gray-600">Track all customer interactions and activities</p>
      </div>
      <button
        v-if="authStore.hasPermission('activities.create')"
        @click="showActivityForm = true"
        class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
      >
        Log Activity
      </button>
    </div>

    <!-- Filters -->
    <FilterBar
      :filter-config="filterConfig"
      :select-filters="selectFilters"
      @filter-change="handleFilterChange"
    />

    <!-- Activities Timeline -->
    <div class="bg-white shadow rounded-lg">
      <div class="p-6">
        <div v-if="loading" class="flex justify-center py-8">
          <LoadingSpinner size="medium" />
        </div>

        <div v-else-if="activities.length === 0" class="text-center py-8">
          <div class="text-gray-400">
            <CalendarIcon class="mx-auto h-12 w-12" />
            <h3 class="mt-2 text-sm font-medium text-gray-900">No activities found</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by logging your first activity.</p>
          </div>
        </div>

        <div v-else class="space-y-6">
          <div
            v-for="activity in activities"
            :key="activity.id"
            class="flex space-x-4"
          >
            <!-- Timeline line -->
            <div class="flex flex-col items-center">
              <div
                :class="[
                  'w-3 h-3 rounded-full border-2 border-white shadow',
                  getActivityColor(activity.type)
                ]"
              ></div>
              <div class="w-0.5 h-full bg-gray-200 mt-1"></div>
            </div>

            <!-- Activity content -->
            <div class="flex-1 bg-gray-50 rounded-lg p-4 mb-4">
              <div class="flex items-center justify-between mb-2">
                <div class="flex items-center space-x-2">
                  <span
                    :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                      getTypeClass(activity.type)
                    ]"
                  >
                    {{ getTypeLabel(activity.type) }}
                  </span>
                  <span
                    :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                      getPriorityClass(activity.priority)
                    ]"
                  >
                    {{ activity.priority }}
                  </span>
                  <span
                    v-if="activity.contact"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800"
                  >
                    {{ activity.contact.full_name }}
                  </span>
                </div>
                <span class="text-sm text-gray-500">
                  {{ formatDateTime(activity.due_date) }}
                </span>
              </div>

              <h4 class="font-medium text-gray-900 mb-1">{{ activity.title }}</h4>
              
              <p v-if="activity.description" class="text-sm text-gray-600 mb-3">
                {{ activity.description }}
              </p>

              <div class="flex items-center justify-between text-sm">
                <div class="flex items-center space-x-4">
                  <span class="text-gray-500">
                    Assigned to: <span class="font-medium">{{ activity.assigned_user?.name }}</span>
                  </span>
                  <span
                    :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                      getStatusClass(activity.status)
                    ]"
                  >
                    {{ activity.status }}
                  </span>
                </div>
                
                <div class="flex space-x-2">
                  <button
                    v-if="activity.status !== 'completed' && authStore.hasPermission('activities.update')"
                    @click="completeActivity(activity)"
                    class="text-green-600 hover:text-green-800 text-sm font-medium"
                  >
                    Complete
                  </button>
                  <button
                    v-if="authStore.hasPermission('activities.update')"
                    @click="editActivity(activity)"
                    class="text-indigo-600 hover:text-indigo-800 text-sm font-medium"
                  >
                    Edit
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="activities.length > 0" class="mt-6 flex justify-between items-center">
          <div class="text-sm text-gray-500">
            Showing {{ activities.length }} activities
          </div>
          <Pagination
            :current-page="pagination.current_page"
            :last-page="pagination.last_page"
            :total="pagination.total"
            @page-change="handlePageChange"
          />
        </div>
      </div>
    </div>

    <!-- Activity Form Modal -->
    <ActivityForm
      v-if="showActivityForm"
      :activity="selectedActivity"
      @save="handleActivitySave"
      @close="showActivityForm = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/store/auth'
import { useCrmStore } from '@/store/crm'
import { CalendarIcon } from '@heroicons/vue/24/outline'
import { formatDateTime } from '@/utils/formatters'
import FilterBar from '@/components/UI/FilterBar.vue'
import Pagination from '@/components/UI/Pagination.vue'
import LoadingSpinner from '@/components/UI/LoadingSpinner.vue'
import ActivityForm from './ActivityForm.vue'

const authStore = useAuthStore()
const crmStore = useCrmStore()

const activities = ref([])
const loading = ref(false)
const showActivityForm = ref(false)
const selectedActivity = ref(null)
const filters = ref({})
const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0
})

const filterConfig = {
  search: true,
  status: {
    options: [
      { value: 'pending', label: 'Pending' },
      { value: 'in_progress', label: 'In Progress' },
      { value: 'completed', label: 'Completed' },
      { value: 'cancelled', label: 'Cancelled' }
    ]
  },
  dateRange: true
}

const selectFilters = [
  {
    key: 'type',
    label: 'Type',
    options: [
      { value: 'call', label: 'Phone Call' },
      { value: 'meeting', label: 'Meeting' },
      { value: 'email', label: 'Email' },
      { value: 'task', label: 'Task' },
      { value: 'note', label: 'Note' }
    ]
  },
  {
    key: 'priority',
    label: 'Priority',
    options: [
      { value: 'low', label: 'Low' },
      { value: 'medium', label: 'Medium' },
      { value: 'high', label: 'High' }
    ]
  }
]

const loadActivities = async (page = 1) => {
  loading.value = true
  try {
    const response = await crmStore.fetchActivities({ ...filters.value, page })
    activities.value = response.data.data
    pagination.value = response.data.meta
  } catch (error) {
    console.error('Failed to load activities:', error)
  } finally {
    loading.value = false
  }
}

const getActivityColor = (type) => {
  const colors = {
    call: 'bg-blue-500',
    meeting: 'bg-green-500',
    email: 'bg-yellow-500',
    task: 'bg-purple-500',
    note: 'bg-gray-500'
  }
  return colors[type] || 'bg-gray-500'
}

const getTypeClass = (type) => {
  const classes = {
    call: 'bg-blue-100 text-blue-800',
    meeting: 'bg-green-100 text-green-800',
    email: 'bg-yellow-100 text-yellow-800',
    task: 'bg-purple-100 text-purple-800',
    note: 'bg-gray-100 text-gray-800'
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}

const getTypeLabel = (type) => {
  const labels = {
    call: 'Phone Call',
    meeting: 'Meeting',
    email: 'Email',
    task: 'Task',
    note: 'Note'
  }
  return labels[type] || type
}

const getPriorityClass = (priority) => {
  const classes = {
    low: 'bg-green-100 text-green-800',
    medium: 'bg-yellow-100 text-yellow-800',
    high: 'bg-red-100 text-red-800'
  }
  return classes[priority] || 'bg-gray-100 text-gray-800'
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    in_progress: 'bg-blue-100 text-blue-800',
    completed: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const handleFilterChange = (newFilters) => {
  filters.value = newFilters
  loadActivities()
}

const handlePageChange = (page) => {
  loadActivities(page)
}

const editActivity = (activity) => {
  selectedActivity.value = activity
  showActivityForm.value = true
}

const completeActivity = async (activity) => {
  try {
    await crmStore.completeActivity(activity.id)
    await loadActivities()
  } catch (error) {
    console.error('Failed to complete activity:', error)
  }
}

const handleActivitySave = () => {
  showActivityForm.value = false
  selectedActivity.value = null
  loadActivities()
}

onMounted(() => {
  loadActivities()
})
</script>