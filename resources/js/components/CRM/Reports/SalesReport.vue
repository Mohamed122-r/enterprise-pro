<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Sales Report</h1>
        <p class="text-gray-600">Comprehensive sales performance analysis</p>
      </div>
      <div class="flex space-x-4">
        <button
          @click="exportToCsv"
          class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700"
        >
          Export CSV
        </button>
        <button
          @click="generatePDF"
          class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700"
        >
          Export PDF
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-6 rounded-lg shadow">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Date Range</label>
          <select v-model="filters.period" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
            <option value="7">Last 7 Days</option>
            <option value="30">Last 30 Days</option>
            <option value="90">Last 90 Days</option>
            <option value="365">Last Year</option>
            <option value="custom">Custom Range</option>
          </select>
        </div>
        <div v-if="filters.period === 'custom'">
          <label class="block text-sm font-medium text-gray-700">From Date</label>
          <input v-model="filters.start_date" type="date" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
        </div>
        <div v-if="filters.period === 'custom'">
          <label class="block text-sm font-medium text-gray-700">To Date</label>
          <input v-model="filters.end_date" type="date" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Sales Rep</label>
          <select v-model="filters.sales_rep" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
            <option value="">All Reps</option>
            <option v-for="user in salesReps" :key="user.id" :value="user.id">
              {{ user.name }}
            </option>
          </select>
        </div>
      </div>
      <div class="mt-4">
        <button
          @click="generateReport"
          class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700"
        >
          Generate Report
        </button>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <StatsCard
        title="Total Revenue"
        :value="summary.total_revenue"
        icon="CurrencyDollarIcon"
        color="green"
        format="currency"
      />
      <StatsCard
        title="Total Invoices"
        :value="summary.total_invoices"
        icon="DocumentTextIcon"
        color="blue"
      />
      <StatsCard
        title="Average Invoice"
        :value="summary.average_invoice"
        icon="ChartBarIcon"
        color="purple"
        format="currency"
      />
      <StatsCard
        title="Conversion Rate"
        :value="summary.conversion_rate"
        icon="TrendingUpIcon"
        color="orange"
        format="percentage"
      />
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Revenue Trend</h3>
        <SalesChart :data="revenueData" />
      </div>
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Sales by Rep</h3>
        <div class="space-y-3">
          <div
            v-for="rep in salesByRep"
            :key="rep.id"
            class="flex items-center justify-between"
          >
            <span class="text-sm text-gray-600">{{ rep.name }}</span>
            <div class="flex items-center space-x-2">
              <div class="w-32 bg-gray-200 rounded-full h-2">
                <div
                  class="bg-indigo-600 h-2 rounded-full"
                  :style="{ width: `${(rep.revenue / summary.total_revenue) * 100}%` }"
                ></div>
              </div>
              <span class="text-sm font-medium text-gray-900">
                {{ formatCurrency(rep.revenue) }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Detailed Table -->
    <div class="bg-white rounded-lg shadow">
      <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Sales Details</h3>
      </div>
      <DataTable
        :columns="columns"
        :data="salesData"
        :pagination="pagination"
        @page-change="handlePageChange"
      >
        <template #column-amount="{ item }">
          {{ formatCurrency(item.amount) }}
        </template>
        <template #column-status="{ item }">
          <span :class="getStatusClass(item.status)">
            {{ item.status }}
          </span>
        </template>
      </DataTable>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { reportApi } from '@/utils/api'
import { formatCurrency } from '@/utils/formatters'
import StatsCard from '@/components/UI/StatsCard.vue'
import DataTable from '@/components/UI/DataTable.vue'
import SalesChart from '@/components/Dashboard/Charts/SalesChart.vue'

const filters = ref({
  period: '30',
  start_date: '',
  end_date: '',
  sales_rep: ''
})

const summary = ref({
  total_revenue: 0,
  total_invoices: 0,
  average_invoice: 0,
  conversion_rate: 0
})

const salesData = ref([])
const revenueData = ref([])
const salesByRep = ref([])
const salesReps = ref([])
const pagination = ref({})

const columns = [
  { key: 'date', label: 'Date' },
  { key: 'invoice_number', label: 'Invoice' },
  { key: 'client_name', label: 'Client' },
  { key: 'sales_rep', label: 'Sales Rep' },
  { key: 'amount', label: 'Amount' },
  { key: 'status', label: 'Status' }
]

const generateReport = async () => {
  try {
    const response = await reportApi.salesReport({
      start_date: filters.value.start_date,
      end_date: filters.value.end_date,
      period: filters.value.period,
      sales_rep: filters.value.sales_rep
    })
    
    const data = response.data.data
    summary.value = data.summary
    salesData.value = data.sales
    revenueData.value = data.revenue_trend
    salesByRep.value = data.sales_by_rep
  } catch (error) {
    console.error('Failed to generate report:', error)
  }
}

const getStatusClass = (status) => {
  const classes = {
    paid: 'bg-green-100 text-green-800',
    pending: 'bg-yellow-100 text-yellow-800',
    overdue: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const exportToCsv = () => {
  const csvContent = convertToCsv(salesData.value)
  downloadCsv(csvContent, 'sales-report.csv')
}

const generatePDF = () => {
  // PDF generation logic would go here
  alert('PDF generation would be implemented here')
}

const convertToCsv = (data) => {
  const headers = ['Date', 'Invoice', 'Client', 'Sales Rep', 'Amount', 'Status']
  const rows = data.map(item => [
    item.date,
    item.invoice_number,
    item.client_name,
    item.sales_rep,
    item.amount,
    item.status
  ])
  
  return [headers, ...rows].map(row => row.join(',')).join('\n')
}

const downloadCsv = (content, filename) => {
  const blob = new Blob([content], { type: 'text/csv' })
  const url = window.URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = filename
  link.click()
  window.URL.revokeObjectURL(url)
}

const handlePageChange = (page) => {
  // Handle pagination
}

onMounted(() => {
  generateReport()
})
</script>