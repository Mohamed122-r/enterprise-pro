<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Contacts</h1>
        <p class="text-gray-600">Manage your business contacts and leads</p>
      </div>
      <button
        v-if="authStore.hasPermission('contacts.create')"
        @click="showContactForm = true"
        class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
      >
        Add Contact
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-lg shadow">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Search</label>
          <input
            v-model="filters.search"
            type="text"
            placeholder="Search contacts..."
            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
          >
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Status</label>
          <select
            v-model="filters.status"
            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
          >
            <option value="">All Status</option>
            <option value="lead">Lead</option>
            <option value="prospect">Prospect</option>
            <option value="customer">Customer</option>
            <option value="partner">Partner</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Source</label>
          <select
            v-model="filters.source"
            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
          >
            <option value="">All Sources</option>
            <option value="website">Website</option>
            <option value="referral">Referral</option>
            <option value="social_media">Social Media</option>
            <option value="event">Event</option>
            <option value="other">Other</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Assigned To</label>
          <select
            v-model="filters.assigned_to"
            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
          >
            <option value="">All Users</option>
            <option v-for="user in users" :key="user.id" :value="user.id">
              {{ user.name }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <!-- Data Table -->
    <DataTable
      :columns="columns"
      :data="contacts.data"
      :pagination="contacts.meta"
      title="Contacts"
      description="List of all business contacts"
      @page-change="handlePageChange"
    >
      <template #column-name="{ item }">
        <div class="flex items-center">
          <div class="ml-4">
            <div class="text-sm font-medium text-gray-900">{{ item.full_name }}</div>
            <div class="text-sm text-gray-500">{{ item.email }}</div>
          </div>
        </div>
      </template>

      <template #column-status="{ item }">
        <span
          :class="[
            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
            statusColors[item.status]
          ]"
        >
          {{ item.status }}
        </span>
      </template>

      <template #column-assigned_to="{ item }">
        <span v-if="item.assigned_user" class="text-sm text-gray-900">
          {{ item.assigned_user.name }}
        </span>
        <span v-else class="text-sm text-gray-500">Unassigned</span>
      </template>

      <template #actions="{ item }">
        <div class="flex justify-end space-x-2">
          <button
            v-if="authStore.hasPermission('contacts.update')"
            @click="editContact(item)"
            class="text-indigo-600 hover:text-indigo-900"
          >
            Edit
          </button>
          <button
            v-if="authStore.hasPermission('contacts.delete')"
            @click="deleteContact(item)"
            class="text-red-600 hover:text-red-900"
          >
            Delete
          </button>
        </div>
      </template>
    </DataTable>

    <!-- Contact Form Modal -->
    <ContactForm
      v-if="showContactForm"
      :contact="selectedContact"
      @save="handleContactSave"
      @close="showContactForm = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useAuthStore } from '@/store/auth'
import { contactApi, userApi } from '@/utils/api'
import DataTable from '@/components/UI/DataTable.vue'
import ContactForm from './ContactForm.vue'

const authStore = useAuthStore()

const contacts = ref({ data: [], meta: {} })
const users = ref([])
const showContactForm = ref(false)
const selectedContact = ref(null)

const filters = ref({
  search: '',
  status: '',
  source: '',
  assigned_to: ''
})

const statusColors = {
  lead: 'bg-yellow-100 text-yellow-800',
  prospect: 'bg-blue-100 text-blue-800',
  customer: 'bg-green-100 text-green-800',
  partner: 'bg-purple-100 text-purple-800'
}

const columns = [
  { key: 'name', label: 'Contact' },
  { key: 'company', label: 'Company' },
  { key: 'position', label: 'Position' },
  { key: 'phone', label: 'Phone' },
  { key: 'status', label: 'Status' },
  { key: 'source', label: 'Source' },
  { key: 'assigned_to', label: 'Assigned To' },
]

const loadContacts = async (page = 1) => {
  try {
    const response = await contactApi.list({ ...filters.value, page })
    contacts.value = response.data
  } catch (error) {
    console.error('Failed to load contacts:', error)
  }
}

const loadUsers = async () => {
  try {
    const response = await userApi.list()
    users.value = response.data.data
  } catch (error) {
    console.error('Failed to load users:', error)
  }
}

const editContact = (contact) => {
  selectedContact.value = contact
  showContactForm.value = true
}

const deleteContact = async (contact) => {
  if (!confirm(`Are you sure you want to delete ${contact.full_name}?`)) {
    return
  }

  try {
    await contactApi.delete(contact.id)
    await loadContacts()
  } catch (error) {
    console.error('Failed to delete contact:', error)
    alert('Failed to delete contact')
  }
}

const handleContactSave = () => {
  showContactForm.value = false
  selectedContact.value = null
  loadContacts()
}

const handlePageChange = (page) => {
  loadContacts(page)
}

// Watch filters and reload data
watch(filters, () => {
  loadContacts()
}, { deep: true })

onMounted(() => {
  loadContacts()
  loadUsers()
})
</script>