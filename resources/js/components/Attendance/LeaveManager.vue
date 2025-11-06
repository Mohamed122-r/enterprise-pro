<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Leave Management</h1>
        <p class="text-gray-600">Manage employee leave requests and approvals</p>
      </div>
      <button
        v-if="authStore.hasPermission('leaves.create')"
        @click="showLeaveForm = true"
        class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700"
      >
        Request Leave
      </button>
    </div>

    <!-- Leave Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <StatsCard
        title="Pending Approval"
        :value="stats.pending"
        icon="ClockIcon"
        color="yellow"
      />
      <StatsCard
        title="Approved This Month"
        :value="stats.approved"
        icon="CheckCircleIcon"
        color="green"
      />
      <StatsCard
        title="Rejected This Month"
        :value="stats.rejected"
        icon="XCircleIcon"
        color="red"
      />
      <StatsCard
        title="Total Leave Days"
        :value="stats.total_days"
        icon="CalendarIcon"
        color="blue"
      />
    </div>

    <!-- Leave Requests Table -->
    <div class="bg-white rounded-lg shadow">
      <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Leave Requests</h3>
      </div>
      <DataTable
        :columns="columns"
        :data="leaves.data"
        :pagination="leaves.meta"
        @page-change="handlePageChange"
      >
        <template #column-user.name="{ item }">
          <div class="flex items-center">
            <div class="ml-4">
              <div class="text-sm font-medium text-gray-900">{{ item.user.name }}</div>
              <div class="text-sm text-gray-500">{{ item.user.department?.name }}</div>
            </div>
          </div>
        </template>

        <template #column-dates="{ item }">
          <div class="text-sm text-gray-900">
            {{ formatDate(item.start_date) }} to {{ formatDate(item.end_date) }}
          </div>
          <div class="text-sm text-gray-500">{{ item.duration }} days</div>
        </template>

        <template #column-status="{ item }">
          <span :class="getStatusClass(item.status)">
            {{ item.status }}
          </span>
        </template>

        <template #actions="{ item }">
          <div class="flex justify-end space-x-2">
            <button
              v-if="authStore.hasPermission('leaves.approve') && item.status === 'pending'"
              @click="approveLeave(item)"
              class="text-green-600 hover:text-green-900"
            >
              Approve
            </button>
            <button
              v-if="authStore.hasPermission('leaves.approve') && item.status === 'pending'"
              @click="rejectLeave(item)"
              class="text-red-600 hover:text-red-900"
            >
              Reject
            </button>
            <button
              v-if="authStore.hasPermission('leaves.update') && item.status === 'pending'"
              @click="editLeave(item)"
              class="text-indigo-600 hover:text-indigo-900"
            >
              Edit
            </button>
          </div>
        </template>
      </DataTable>
    </div>

    <!-- Leave Form Modal -->
    <LeaveForm
      v-if="showLeaveForm"
      :leave="selectedLeave"
      @save="handleLeaveSave"
      @close="showLeaveForm = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '@/store/auth'
import { useAttendanceStore } from '@/store/attendance'
import { formatDate } from '@/utils/formatters'
import StatsCard from '@/components/UI/StatsCard.vue'
import DataTable from '@/components/UI/DataTable.vue'
import LeaveForm from './LeaveForm.vue'

const authStore = useAuthStore()
const attendanceStore = useAttendanceStore()

const leaves = ref({ data: [], meta: {} })
const showLeaveForm = ref(false)
const selectedLeave = ref(null)

const columns = [
  { key: 'user.name', label: 'Employee' },
  { key: 'type', label: 'Type' },
  { key: 'dates', label: 'Dates' },
  { key: 'reason', label: 'Reason' },
  { key: 'status', label: 'Status' }
]

const stats = computed(() => {
  const pending = leaves.value.data.filter(leave => leave.status === 'pending').length
  const approved = leaves.value.data.filter(leave => leave.status === 'approved').length
  const rejected = leaves.value.data.filter(leave => leave.status === 'rejected').length
  const totalDays = leaves.value.data.reduce((sum, leave) => sum + leave.duration, 0)

  return { pending, approved, rejected, total_days: totalDays }
})

const loadLeaves = async (page = 1) => {
  try {
    const response = await attendanceStore.fetchLeaves({ page })
    leaves.value = response.data
  } catch (error) {
    console.error('Failed to load leaves:', error)
  }
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const approveLeave = async (leave) => {
  if (!confirm(`Approve leave request for ${leave.user.name}?`)) return

  try {
    await attendanceStore.approveLeave(leave.id)
    await loadLeaves()
  } catch (error) {
    console.error('Failed to approve leave:', error)
  }
}

const rejectLeave = async (leave) => {
  const notes = prompt('Please provide a reason for rejection:')
  if (!notes) return

  try {
    await attendanceStore.rejectLeave(leave.id, notes)
    await loadLeaves()
  } catch (error) {
    console.error('Failed to reject leave:', error)
  }
}

const editLeave = (leave) => {
  selectedLeave.value = leave
  showLeaveForm.value = true
}

const handleLeaveSave = () => {
  showLeaveForm.value = false
  selectedLeave.value = null
  loadLeaves()
}

const handlePageChange = (page) => {
  loadLeaves(page)
}

onMounted(() => {
  loadLeaves()
})
</script>