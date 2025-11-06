<template>
  <div class="space-y-6">
    <!-- Stats Overview -->
    <StatsOverview :stats="stats" />
    
    <!-- Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <SalesChart :data="salesData" />
      <AttendanceChart :data="attendanceData" />
    </div>
    
    <!-- Recent Activities -->
    <RecentActivities :activities="recentActivities" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { dashboardApi } from '@/utils/api'
import StatsOverview from './StatsOverview.vue'
import SalesChart from './Charts/SalesChart.vue'
import AttendanceChart from './Charts/AttendanceChart.vue'
import RecentActivities from './RecentActivities.vue'

const stats = ref({})
const salesData = ref([])
const attendanceData = ref([])
const recentActivities = ref([])

const loadDashboardData = async () => {
  try {
    const [statsResponse, activitiesResponse] = await Promise.all([
      dashboardApi.stats(),
      dashboardApi.recentActivities()
    ])
    
    stats.value = statsResponse.data.data
    recentActivities.value = activitiesResponse.data.data
    
    // Mock chart data - replace with actual API calls
    salesData.value = generateSalesData()
    attendanceData.value = generateAttendanceData()
  } catch (error) {
    console.error('Failed to load dashboard data:', error)
  }
}

const generateSalesData = () => {
  return Array.from({ length: 12 }, (_, i) => ({
    month: new Date(2024, i).toLocaleString('default', { month: 'short' }),
    sales: Math.floor(Math.random() * 10000) + 5000
  }))
}

const generateAttendanceData = () => {
  return Array.from({ length: 30 }, (_, i) => ({
    date: new Date(2024, 0, i + 1).toISOString().split('T')[0],
    present: Math.floor(Math.random() * 50) + 70,
    absent: Math.floor(Math.random() * 10) + 5
  }))
}

onMounted(() => {
  loadDashboardData()
})
</script>