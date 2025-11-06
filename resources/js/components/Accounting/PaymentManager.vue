<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Payment Management</h1>
        <p class="text-gray-600">Track and manage customer payments</p>
      </div>
      <button
        v-if="authStore.hasPermission('payments.create')"
        @click="showPaymentForm = true"
        class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700"
      >
        Record Payment
      </button>
    </div>

    <!-- Payment Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <StatsCard
        title="Total Received"
        :value="stats.total_received"
        icon="CurrencyDollarIcon"
        color="green"
        format="currency"
      />
      <StatsCard
        title="This Month"
        :value="stats.this_month"
        icon="CalendarIcon"
        color="blue"
        format="currency"
      />
      <StatsCard
        title="Pending"
        :value="stats.pending"
        icon="ClockIcon"
        color="yellow"
        format="currency"
      />
      <StatsCard
        title="Average Payment"
        :value="stats.average_payment"
        icon="ChartBarIcon"
        color="purple"
        format="currency"
      />
    </div>

    <!-- Payments Table -->
    <div class="bg-white rounded-lg shadow">
      <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Payment History</h3>
      </div>
      <DataTable
        :columns="columns"
        :data="payments.data"
        :pagination="payments.meta"
        @page-change="handlePageChange"
      >
        <template #column-amount="{ item }">
          <div class="text-sm font-medium text-gray-900">
            {{ formatCurrency(item.amount) }}
          </div>
        </template>

        <template #column-payment_method="{ item }">
          <span :class="getMethodClass(item.payment_method)">
            {{ getMethodLabel(item.payment_method) }}
          </span>
        </template>

        <template #column-invoice.invoice_number="{ item }">
          <div class="text-sm text-gray-900">{{ item.invoice.invoice_number }}</div>
          <div class="text-sm text-gray-500">{{ item.invoice.client_name }}</div>
        </template>

        <template #actions="{ item }">
          <div class="flex justify-end space-x-2">
            <button
              @click="viewPayment(item)"
              class="text-indigo-600 hover:text-indigo-900"
            >
              View
            </button>
            <button
              v-if="authStore.hasPermission('payments.delete')"
              @click="deletePayment(item)"
              class="text-red-600 hover:text-red-900"
            >
              Delete
            </button>
          </div>
        </template>
      </DataTable>
    </div>

    <!-- Payment Form Modal -->
    <PaymentForm
      v-if="showPaymentForm"
      @save="handlePaymentSave"
      @close="showPaymentForm = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '@/store/auth'
import { useAccountingStore } from '@/store/accounting'
import { formatCurrency } from '@/utils/formatters'
import StatsCard from '@/components/UI/StatsCard.vue'
import DataTable from '@/components/UI/DataTable.vue'
import PaymentForm from './PaymentForm.vue'

const authStore = useAuthStore()
const accountingStore = useAccountingStore()

const payments = ref({ data: [], meta: {} })
const showPaymentForm = ref(false)

const columns = [
  { key: 'payment_date', label: 'Date' },
  { key: 'invoice.invoice_number', label: 'Invoice' },
  { key: 'amount', label: 'Amount' },
  { key: 'payment_method', label: 'Method' },
  { key: 'reference', label: 'Reference' }
]

const stats = computed(() => {
  const totalReceived = payments.value.data.reduce((sum, payment) => sum + payment.amount, 0)
  const thisMonth = payments.value.data
    .filter(payment => {
      const paymentDate = new Date(payment.payment_date)
      const now = new Date()
      return paymentDate.getMonth() === now.getMonth() && 
             paymentDate.getFullYear() === now.getFullYear()
    })
    .reduce((sum, payment) => sum + payment.amount, 0)
  
  const pending = 0 // This would come from API
  const averagePayment = payments.value.data.length > 0 ? 
    totalReceived / payments.value.data.length : 0

  return {
    total_received: totalReceived,
    this_month: thisMonth,
    pending: pending,
    average_payment: averagePayment
  }
})

const loadPayments = async (page = 1) => {
  try {
    const response = await accountingStore.fetchPayments({ page })
    payments.value = response.data
  } catch (error) {
    console.error('Failed to load payments:', error)
  }
}

const getMethodClass = (method) => {
  const classes = {
    cash: 'bg-green-100 text-green-800',
    card: 'bg-blue-100 text-blue-800',
    bank_transfer: 'bg-purple-100 text-purple-800',
    check: 'bg-yellow-100 text-yellow-800',
    digital: 'bg-indigo-100 text-indigo-800'
  }
  return classes[method] || 'bg-gray-100 text-gray-800'
}

const getMethodLabel = (method) => {
  const labels = {
    cash: 'Cash',
    card: 'Card',
    bank_transfer: 'Bank Transfer',
    check: 'Check',
    digital: 'Digital'
  }
  return labels[method] || method
}

const viewPayment = (payment) => {
  // Implement view payment details
  console.log('View payment:', payment)
}

const deletePayment = async (payment) => {
  if (!confirm('Are you sure you want to delete this payment?')) return

  try {
    // await accountingStore.deletePayment(payment.id)
    await loadPayments()
  } catch (error) {
    console.error('Failed to delete payment:', error)
  }
}

const handlePaymentSave = () => {
  showPaymentForm.value = false
  loadPayments()
}

const handlePageChange = (page) => {
  loadPayments(page)
}

onMounted(() => {
  loadPayments()
})
</script>