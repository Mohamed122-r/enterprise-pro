import { defineStore } from 'pinia'
import { attendanceApi, leaveApi } from '@/utils/api'

export const useAttendanceStore = defineStore('attendance', {
  state: () => ({
    attendances: [],
    leaves: [],
    todayAttendance: null,
    loading: false,
    error: null
  }),

  getters: {
    getAttendanceByDate: (state) => (date) => {
      return state.attendances.find(attendance => attendance.date === date)
    },
    
    getLeavesByUser: (state) => (userId) => {
      return state.leaves.filter(leave => leave.user_id === userId)
    },
    
    getPendingLeaves: (state) => {
      return state.leaves.filter(leave => leave.status === 'pending')
    },
    
    monthlySummary: (state) => (userId, month, year) => {
      const monthAttendances = state.attendances.filter(attendance => {
        const attendanceDate = new Date(attendance.date)
        return attendance.user_id === userId &&
               attendanceDate.getMonth() + 1 === month &&
               attendanceDate.getFullYear() === year
      })
      
      const presentDays = monthAttendances.filter(a => a.status === 'present').length
      const absentDays = monthAttendances.filter(a => a.status === 'absent').length
      const lateDays = monthAttendances.filter(a => a.status === 'late').length
      
      return {
        present: presentDays,
        absent: absentDays,
        late: lateDays,
        total: monthAttendances.length
      }
    }
  },

  actions: {
    // Attendance
    async fetchAttendances(filters = {}) {
      this.loading = true
      this.error = null
      
      try {
        const response = await attendanceApi.list(filters)
        this.attendances = response.data.data
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch attendances'
        throw error
      } finally {
        this.loading = false
      }
    },

    async checkIn() {
      this.loading = true
      this.error = null
      
      try {
        const response = await attendanceApi.checkIn()
        this.todayAttendance = response.data.data
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to check in'
        throw error
      } finally {
        this.loading = false
      }
    },

    async checkOut() {
      this.loading = true
      this.error = null
      
      try {
        const response = await attendanceApi.checkOut()
        this.todayAttendance = response.data.data
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to check out'
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchTodayAttendance() {
      this.loading = true
      this.error = null
      
      try {
        const today = new Date().toISOString().split('T')[0]
        const response = await attendanceApi.list({
          date_from: today,
          date_to: today
        })
        
        if (response.data.data.length > 0) {
          this.todayAttendance = response.data.data[0]
        } else {
          this.todayAttendance = null
        }
        
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch today attendance'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Leaves
    async fetchLeaves(filters = {}) {
      this.loading = true
      this.error = null
      
      try {
        const response = await leaveApi.list(filters)
        this.leaves = response.data.data
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch leaves'
        throw error
      } finally {
        this.loading = false
      }
    },

    async createLeave(leaveData) {
      this.loading = true
      this.error = null
      
      try {
        const response = await leaveApi.create(leaveData)
        this.leaves.unshift(response.data.data)
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create leave request'
        throw error
      } finally {
        this.loading = false
      }
    },

    async approveLeave(leaveId) {
      this.loading = true
      this.error = null
      
      try {
        const response = await leaveApi.approve(leaveId)
        const index = this.leaves.findIndex(leave => leave.id === leaveId)
        if (index !== -1) {
          this.leaves[index] = response.data.data
        }
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to approve leave'
        throw error
      } finally {
        this.loading = false
      }
    },

    async rejectLeave(leaveId, notes) {
      this.loading = true
      this.error = null
      
      try {
        const response = await leaveApi.reject(leaveId, { notes })
        const index = this.leaves.findIndex(leave => leave.id === leaveId)
        if (index !== -1) {
          this.leaves[index] = response.data.data
        }
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to reject leave'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Utility methods
    clearTodayAttendance() {
      this.todayAttendance = null
    },

    clearError() {
      this.error = null
    }
  }
})