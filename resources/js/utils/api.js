// resources/js/utils/api.js

import axios from 'axios'

// إعداد axios الأساسي
const api = axios.create({
    baseURL: '/api',
    timeout: 10000,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
})

// Request Interceptor
api.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('auth_token')
        if (token) {
            config.headers.Authorization = `Bearer ${token}`
        }
        return config
    },
    (error) => {
        return Promise.reject(error)
    }
)

// Response Interceptor
api.interceptors.response.use(
    (response) => {
        return response.data
    },
    (error) => {
        if (error.response?.status === 401) {
            localStorage.removeItem('auth_token')
            localStorage.removeItem('user')
            window.location.href = '/login'
        }
        return Promise.reject(error.response?.data || error)
    }
)

// APIs الأساسية

// Authentication API
export const authApi = {
    login: (credentials) => api.post('/auth/login', credentials),
    register: (userData) => api.post('/auth/register', userData),
    logout: () => api.post('/auth/logout'),
    refresh: () => api.post('/auth/refresh'),
    forgotPassword: (email) => api.post('/auth/forgot-password', { email }),
    resetPassword: (data) => api.post('/auth/reset-password', data),
    getProfile: () => api.get('/auth/profile'),
    updateProfile: (data) => api.put('/auth/profile', data)
}

// Users API
export const userApi = {
    getAll: (params = {}) => api.get('/users', { params }),
    getById: (id) => api.get(`/users/${id}`),
    create: (userData) => api.post('/users', userData),
    update: (id, userData) => api.put(`/users/${id}`, userData),
    delete: (id) => api.delete(`/users/${id}`),
    updateStatus: (id, status) => api.patch(`/users/${id}/status`, { status }),
    updatePermissions: (id, permissions) => api.patch(`/users/${id}/permissions`, { permissions })
}

// Roles API
export const roleApi = {
    getAll: () => api.get('/roles'),
    getById: (id) => api.get(`/roles/${id}`),
    create: (roleData) => api.post('/roles', roleData),
    update: (id, roleData) => api.put(`/roles/${id}`, roleData),
    delete: (id) => api.delete(`/roles/${id}`)
}

// Departments API
export const departmentApi = {
    getAll: () => api.get('/departments'),
    getById: (id) => api.get(`/departments/${id}`),
    create: (departmentData) => api.post('/departments', departmentData),
    update: (id, departmentData) => api.put(`/departments/${id}`, departmentData),
    delete: (id) => api.delete(`/departments/${id}`)
}

// CRM APIs

// Contacts API
export const contactApi = {
    getAll: (params = {}) => api.get('/contacts', { params }),
    getById: (id) => api.get(`/contacts/${id}`),
    create: (contactData) => api.post('/contacts', contactData),
    update: (id, contactData) => api.put(`/contacts/${id}`, contactData),
    delete: (id) => api.delete(`/contacts/${id}`),
    import: (file) => {
        const formData = new FormData()
        formData.append('file', file)
        return api.post('/contacts/import', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
    },
    export: (params = {}) => api.get('/contacts/export', { 
        params,
        responseType: 'blob'
    })
}

// Opportunities API
export const opportunityApi = {
    getAll: (params = {}) => api.get('/opportunities', { params }),
    getById: (id) => api.get(`/opportunities/${id}`),
    create: (opportunityData) => api.post('/opportunities', opportunityData),
    update: (id, opportunityData) => api.put(`/opportunities/${id}`, opportunityData),
    delete: (id) => api.delete(`/opportunities/${id}`),
    updateStage: (id, stage) => api.patch(`/opportunities/${id}/stage`, { stage }),
    getStats: () => api.get('/opportunities/stats')
}

// Activities API
export const activityApi = {
    getAll: (params = {}) => api.get('/activities', { params }),
    getById: (id) => api.get(`/activities/${id}`),
    create: (activityData) => api.post('/activities', activityData),
    update: (id, activityData) => api.put(`/activities/${id}`, activityData),
    delete: (id) => api.delete(`/activities/${id}`),
    getTimeline: (contactId) => api.get(`/contacts/${contactId}/activities`)
}

// Attendance APIs

// Attendance API
export const attendanceApi = {
    getAll: (params = {}) => api.get('/attendance', { params }),
    getById: (id) => api.get(`/attendance/${id}`),
    create: (attendanceData) => api.post('/attendance', attendanceData),
    update: (id, attendanceData) => api.put(`/attendance/${id}`, attendanceData),
    delete: (id) => api.delete(`/attendance/${id}`),
    checkIn: (data) => api.post('/attendance/check-in', data),
    checkOut: (data) => api.post('/attendance/check-out', data),
    getMyAttendance: (params = {}) => api.get('/attendance/my', { params }),
    getStats: (params = {}) => api.get('/attendance/stats', { params })
}

// Leaves API
export const leaveApi = {
    getAll: (params = {}) => api.get('/leaves', { params }),
    getById: (id) => api.get(`/leaves/${id}`),
    create: (leaveData) => api.post('/leaves', leaveData),
    update: (id, leaveData) => api.put(`/leaves/${id}`, leaveData),
    delete: (id) => api.delete(`/leaves/${id}`),
    approve: (id) => api.patch(`/leaves/${id}/approve`),
    reject: (id, reason) => api.patch(`/leaves/${id}/reject`, { reason }),
    getMyLeaves: () => api.get('/leaves/my')
}

// Schedules API
export const scheduleApi = {
    getAll: (params = {}) => api.get('/schedules', { params }),
    getById: (id) => api.get(`/schedules/${id}`),
    create: (scheduleData) => api.post('/schedules', scheduleData),
    update: (id, scheduleData) => api.put(`/schedules/${id}`, scheduleData),
    delete: (id) => api.delete(`/schedules/${id}`),
    getMySchedule: () => api.get('/schedules/my')
}

// Accounting APIs

// Invoices API
export const invoiceApi = {
    getAll: (params = {}) => api.get('/invoices', { params }),
    getById: (id) => api.get(`/invoices/${id}`),
    create: (invoiceData) => api.post('/invoices', invoiceData),
    update: (id, invoiceData) => api.put(`/invoices/${id}`, invoiceData),
    delete: (id) => api.delete(`/invoices/${id}`),
    send: (id) => api.post(`/invoices/${id}/send`),
    markAsPaid: (id) => api.patch(`/invoices/${id}/mark-paid`),
    cancel: (id) => api.patch(`/invoices/${id}/cancel`),
    duplicate: (id) => api.post(`/invoices/${id}/duplicate`),
    getStats: () => api.get('/invoices/stats'),
    export: (params = {}) => api.get('/invoices/export', {
        params,
        responseType: 'blob'
    })
}

// Payments API
export const paymentApi = {
    getAll: (params = {}) => api.get('/payments', { params }),
    getById: (id) => api.get(`/payments/${id}`),
    create: (paymentData) => api.post('/payments', paymentData),
    update: (id, paymentData) => api.put(`/payments/${id}`, paymentData),
    delete: (id) => api.delete(`/payments/${id}`),
    record: (invoiceId, paymentData) => api.post(`/invoices/${invoiceId}/payments`, paymentData),
    getInvoicePayments: (invoiceId) => api.get(`/invoices/${invoiceId}/payments`),
    getStats: () => api.get('/payments/stats')
}

// Expenses API
export const expenseApi = {
    getAll: (params = {}) => api.get('/expenses', { params }),
    getById: (id) => api.get(`/expenses/${id}`),
    create: (expenseData) => api.post('/expenses', expenseData),
    update: (id, expenseData) => api.put(`/expenses/${id}`, expenseData),
    delete: (id) => api.delete(`/expenses/${id}`),
    approve: (id) => api.patch(`/expenses/${id}/approve`),
    reject: (id, reason) => api.patch(`/expenses/${id}/reject`, { reason }),
    getStats: () => api.get('/expenses/stats')
}

// Chat APIs

// Conversations API
export const conversationApi = {
    getAll: (params = {}) => api.get('/conversations', { params }),
    getById: (id) => api.get(`/conversations/${id}`),
    create: (conversationData) => api.post('/conversations', conversationData),
    update: (id, conversationData) => api.put(`/conversations/${id}`, conversationData),
    delete: (id) => api.delete(`/conversations/${id}`),
    markAsRead: (id) => api.patch(`/conversations/${id}/read`),
    getUnreadCount: () => api.get('/conversations/unread-count')
}

// Messages API
export const messageApi = {
    getAll: (conversationId, params = {}) => api.get(`/conversations/${conversationId}/messages`, { params }),
    getById: (conversationId, messageId) => api.get(`/conversations/${conversationId}/messages/${messageId}`),
    create: (conversationId, messageData) => api.post(`/conversations/${conversationId}/messages`, messageData),
    update: (conversationId, messageId, messageData) => api.put(`/conversations/${conversationId}/messages/${messageId}`, messageData),
    delete: (conversationId, messageId) => api.delete(`/conversations/${conversationId}/messages/${messageId}`),
    markAsRead: (conversationId, messageId) => api.patch(`/conversations/${conversationId}/messages/${messageId}/read`)
}

// Reports API
export const reportApi = {
    // تقارير المبيعات
    sales: (params = {}) => api.get('/reports/sales', { params }),
    salesExport: (params = {}) => api.get('/reports/sales/export', {
        params,
        responseType: 'blob'
    }),

    // تقارير الحضور
    attendance: (params = {}) => api.get('/reports/attendance', { params }),
    attendanceExport: (params = {}) => api.get('/reports/attendance/export', {
        params,
        responseType: 'blob'
    }),

    // تقارير مالية
    financial: (params = {}) => api.get('/reports/financial', { params }),
    financialExport: (params = {}) => api.get('/reports/financial/export', {
        params,
        responseType: 'blob'
    }),

    // تقارير العملاء
    customers: (params = {}) => api.get('/reports/customers', { params }),
    customersExport: (params = {}) => api.get('/reports/customers/export', {
        params,
        responseType: 'blob'
    })
}

// Dashboard API
export const dashboardApi = {
    getStats: () => api.get('/dashboard/stats'),
    getRecentActivities: () => api.get('/dashboard/recent-activities'),
    getChartsData: (period = 'monthly') => api.get('/dashboard/charts', { params: { period } })
}

// Notifications API
export const notificationApi = {
    getAll: (params = {}) => api.get('/notifications', { params }),
    getUnread: () => api.get('/notifications/unread'),
    markAsRead: (id) => api.patch(`/notifications/${id}/read`),
    markAllAsRead: () => api.patch('/notifications/mark-all-read'),
    getCount: () => api.get('/notifications/count')
}

// File Upload API
export const uploadApi = {
    upload: (file, options = {}) => {
        const formData = new FormData()
        formData.append('file', file)

        if (options.folder) {
            formData.append('folder', options.folder)
        }

        return api.post('/upload', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
            onUploadProgress: options.onProgress
        })
    },
    delete: (filePath) => api.delete('/upload', { data: { path: filePath } })
}

// Utilities
export const apiUtils = {
    // تحميل الملفات
    downloadFile: (url, filename) => {
        return api.get(url, { responseType: 'blob' }).then(response => {
            const blob = new Blob([response])
            const link = document.createElement('a')
            link.href = URL.createObjectURL(blob)
            link.download = filename
            link.click()
            URL.revokeObjectURL(link.href)
        })
    },

    // إلغاء الطلبات
    createCancelToken: () => {
        return axios.CancelToken.source()
    },

    // تحويل params إلى query string
    toQueryString: (params) => {
        const searchParams = new URLSearchParams()
        Object.keys(params).forEach(key => {
            if (params[key] !== null && params[key] !== undefined) {
                searchParams.append(key, params[key])
            }
        })
        return searchParams.toString()
    }
}

// Export default axios instance for custom requests
export default api
