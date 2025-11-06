<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Invoice Management</h1>
        <p class="text-gray-600">Create and manage customer invoices</p>
      </div>
      <button
        v-if="authStore.hasPermission('invoices.create')"
        @click="showInvoiceForm = true"
        class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
      >
        Create Invoice
      </button>
    </div>

    <!-- Filters -->
    <FilterBar
      :filter-config="filterConfig"
      :select-filters="selectFilters"
      @filter-change="handleFilterChange"
    />

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <StatsCard
        title="Total Revenue"
        :value="stats.total_revenue"
        icon="CurrencyDollarIcon"
        color="green"
        format="currency"
      />
      <StatsCard
        title="Pending Invoices"
        :value="stats.pending_invoices"
        icon="DocumentTextIcon"
        color="yellow"
      />
      <StatsCard
        title="Overdue Invoices"
        :value="stats.overdue_invoices"
        icon="ClockIcon"
        color="red"
      />
      <StatsCard
        title="Paid This Month"
        :value="stats.paid_this_month"
        icon="CheckCircleIcon"
        color="blue"
        format="currency"
      />
    </div>

    <!-- Invoices Table -->
    <DataTable
      :columns="columns"
      :data="invoices.data"
      :pagination="invoices.meta"
      title="Invoices"
      description="List of all customer invoices"
      @page-change="handlePageChange"
    >
      <template #column-invoice_number="{ item }">
        <div class="flex items-center">
          <DocumentTextIcon class="h-5 w-5 text-gray-400 mr-2" />
          <div>
            <div class="text-sm font-medium text-gray-900">{{ item.invoice_number }}</div>
            <div class="text-sm text-gray-500">{{ item.client_name }}</div>
          </div>
        </div>
      </template>

      <template #column-total="{ item }">
        <div class="text-sm font-medium text-gray-900">
          {{ formatCurrency(item.total) }}
        </div>
        <div class="text-sm text-gray-500">
          Due: {{ formatDate(item.due_date) }}
        </div>
      </template>

      <template #column-status="{ item }">
        <span
          :class="[
            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
            getStatusClass(item.status)
          ]"
        >
          {{ getStatusLabel(item.status) }}
        </span>
      </template>

      <template #column-balance="{ item }">
        <div class="text-sm font-medium" :class="getBalanceColor(item.balance)">
          {{ formatCurrency(item.balance) }}
        </div>
        <div v-if="item.balance > 0" class="text-xs text-gray-500">
          {{ getDaysOverdue(item.due_date) }} days overdue
        </div>
      </template>

      <template #actions="{ item }">
        <div class="flex justify-end space-x-2">
          <button
            @click="viewInvoice(item)"
            class="text-indigo-600 hover:text-indigo-900"
          >
            View
          </button>
          <button
            v-if="authStore.hasPermission('invoices.update') && item.status === 'draft'"
            @click="editInvoice(item)"
            class="text-gray-600 hover:text-gray-900"
          >
            Edit
          </button>
          <button
            v-if="authStore.hasPermission('invoices.update') && item.status === 'draft'"
            @click="sendInvoice(item)"
            class="text-green-600 hover:text-green-900"
          >
            Send
          </button>
          <button
            v-if="authStore.hasPermission('invoices.delete') && item.status === 'draft'"
            @click="deleteInvoice(item)"
            class="text-red-600 hover:text-red-900"
          >
            Delete
          </button>
        </div>
      </template>
    </DataTable>

    <!-- Invoice Form Modal -->
    <InvoiceForm
      v-if="showInvoiceForm"
      :invoice="selectedInvoice"
      @save="handleInvoiceSave"
      @close="showInvoiceForm = false"
    />

    <!-- Invoice Details Modal -->
    <InvoiceDetails
      v-if="showInvoiceDetails && selectedInvoice"
      :invoice="selectedInvoice"
      @close="showInvoiceDetails = false"
      @update="handleInvoiceUpdate"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '@/store/auth'
import { useAccountingStore } from '@/store/accounting'
import { 
  DocumentTextIcon,
  CurrencyDollarIcon,
  ClockIcon,
  CheckCircleIcon 
} from '@heroicons/vue/24/outline'
import { formatCurrency, formatDate } from '@/utils/formatters'
import FilterBar from '@/components/UI/FilterBar.vue'
import DataTable from '@/components/UI/DataTable.vue'
import StatsCard from '@/components/UI/StatsCard.vue'
import InvoiceForm from './InvoiceForm.vue'
import InvoiceDetails from './InvoiceDetails.vue'

const authStore = useAuthStore()
const accountingStore = useAccountingStore()

const invoices = ref({ data: [], meta: {} })
const showInvoiceForm = ref(false)
const showInvoiceDetails = ref(false)
const selectedInvoice = ref(null)
const filters = ref({})

const filterConfig = {
  search: true,
  status: {
    options: [
      { value: 'draft', label: 'Draft' },
      { value: 'sent', label: 'Sent' },
      { value: 'paid', label: 'Paid' },
      { value: 'partial', label: 'Partial' },
      { value: 'overdue', label: 'Overdue' }
    ]
  },
  dateRange: true
}

const selectFilters = [
  {
    key: 'client_id',
    label: 'Client',
    options: [] // Would be populated from API
  }
]

const columns = [
  { key: 'invoice_number', label: 'Invoice' },
  { key: 'issue_date', label: 'Issue Date' },
  { key: 'due_date', label: 'Due Date' },
  { key: 'total', label: 'Amount' },
  { key: 'status', label: 'Status' },
  { key: 'balance', label: 'Balance' }
]

const stats = computed(() => {
  const totalRevenue = invoices.value.data.reduce((sum, invoice) => sum + invoice.total, 0)
  const pendingInvoices = invoices.value.data.filter(invoice => 
    ['sent', 'partial'].includes(invoice.status)
  ).length
  const overdueInvoices = invoices.value.data.filter(invoice => 
    invoice.status === 'overdue'
  ).length
  const paidThisMonth = invoices.value.data
    .filter(invoice => invoice.status === 'paid')
    .reduce((sum, invoice) => sum + invoice.total, 0)

  return {
    total_revenue: totalRevenue,
    pending_invoices: pendingInvoices,
    overdue_invoices: overdueInvoices,
    paid_this_month: paidThisMonth
  }
})

const loadInvoices = async (page = 1) => {
  try {
    const response = await accountingStore.fetchInvoices({ ...filters.value, page })
    invoices.value = response.data
  } catch (error) {
    console.error('Failed to load invoices:', error)
  }
}

const getStatusClass = (status) => {
  const classes = {
    draft: 'bg-gray-100 text-gray-800',
    sent: 'bg-blue-100 text-blue-800',
    paid: 'bg-green-100 text-green-800',
    partial: 'bg-yellow-100 text-yellow-800',
    overdue: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getStatusLabel = (status) => {
  const labels = {
    draft: 'Draft',
    sent: 'Sent',
    paid: 'Paid',
    partial: 'Partial',
    overdue: 'Overdue'
  }
  return labels[status] || status
}

const getBalanceColor = (balance) => {
  return balance > 0 ? 'text-red-600' : 'text-green-600'
}

const getDaysOverdue = (dueDate) => {
  const today = new Date()
  const due = new Date(dueDate)
  const diffTime = today - due
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  return Math.max(0, diffDays)
}

const handleFilterChange = (newFilters) => {
  filters.value = newFilters
  loadInvoices()
}

const handlePageChange = (page) => {
  loadInvoices(page)
}

const viewInvoice = (invoice) => {
  selectedInvoice.value = invoice
  showInvoiceDetails.value = true
}

const editInvoice = (invoice) => {
  selectedInvoice.value = invoice
  showInvoiceForm.value = true
}

const sendInvoice = async (invoice) => {
  if (!confirm(`Send invoice ${invoice.invoice_number} to ${invoice.client_name}?`)) {
    return
  }

  try {
    await accountingStore.sendInvoice(invoice.id)
    await loadInvoices()
  } catch (error) {
    console.error('Failed to send invoice:', error)
    alert('Failed to send invoice')
  }
}

const deleteInvoice = async (invoice) => {
  if (!confirm(`Are you sure you want to delete invoice ${invoice.invoice_number}?`)) {
    return
  }

  try {
    await accountingStore.deleteInvoice(invoice.id)
    await loadInvoices()
  } catch (error) {
    console.error('Failed to delete invoice:', error)
    alert('Failed to delete invoice')
  }
}

const handleInvoiceSave = () => {
  showInvoiceForm.value = false
  selectedInvoice.value = null
  loadInvoices()
}

const handleInvoiceUpdate = () => {
  showInvoiceDetails.value = false
  selectedInvoice.value = null
  loadInvoices()
}

onMounted(() => {
  loadInvoices()
})
</script>