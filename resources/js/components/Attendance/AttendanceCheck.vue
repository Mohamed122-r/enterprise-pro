<template>
  <div class="bg-white shadow rounded-lg p-6">
    <h2 class="text-lg font-medium text-gray-900 mb-4">Today's Attendance</h2>
    
    <div v-if="todayAttendance" class="space-y-4">
      <div class="flex items-center justify-between p-4 border rounded-lg">
        <div>
          <p class="text-sm font-medium text-gray-900">Check In</p>
          <p class="text-sm text-gray-500">{{ formatTime(todayAttendance.check_in) }}</p>
        </div>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
          Checked In
        </span>
      </div>

      <div v-if="todayAttendance.check_out" class="flex items-center justify-between p-4 border rounded-lg">
        <div>
          <p class="text-sm font-medium text-gray-900">Check Out</p>
          <p class="text-sm text-gray-500">{{ formatTime(todayAttendance.check_out) }}</p>
        </div>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
          Checked Out
        </span>
      </div>

      <div v-if="todayAttendance.total_hours" class="flex items-center justify-between p-4 border rounded-lg">
        <div>
          <p class="text-sm font-medium text-gray-900">Total Hours</p>
          <p class="text-sm text-gray-500">{{ todayAttendance.total_hours }} hours</p>
        </div>
      </div>
    </div>

    <div v-else class="text-center py-8">
      <p class="text-gray-500 mb-4">You haven't checked in today</p>
      <button
        @click="checkIn"
        :disabled="loading"
        class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50"
      >
        {{ loading ? 'Checking In...' : 'Check In' }}
      </button>
    </div>

    <div v-if="todayAttendance && !todayAttendance.check_out" class="mt-6">
      <button
        @click="checkOut"
        :disabled="loading"
        class="w-full bg-red-600 text-white px-6 py-2 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 disabled:opacity-50"
      >
        {{ loading ? 'Checking Out...' : 'Check Out' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { attendanceApi } from '@/utils/api'
import { formatTime } from '@/utils/formatters'

const todayAttendance = ref(null)
const loading = ref(false)

const loadTodayAttendance = async () => {
  try {
    const response = await attendanceApi.list({ 
      date_from: new Date().toISOString().split('T')[0],
      date_to: new Date().toISOString().split('T')[0]
    })
    
    if (response.data.data.length > 0) {
      todayAttendance.value = response.data.data[0]
    }
  } catch (error) {
    console.error('Failed to load today attendance:', error)
  }
}

const checkIn = async () => {
  loading.value = true
  try {
    const response = await attendanceApi.checkIn()
    todayAttendance.value = response.data.data
  } catch (error) {
    console.error('Failed to check in:', error)
    alert('Failed to check in: ' + (error.response?.data?.message || error.message))
  } finally {
    loading.value = false
  }
}

const checkOut = async () => {
  loading.value = true
  try {
    const response = await attendanceApi.checkOut()
    todayAttendance.value = response.data.data
  } catch (error) {
    console.error('Failed to check out:', error)
    alert('Failed to check out: ' + (error.response?.data?.message || error.message))
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadTodayAttendance()
})
</script>