<template>
  <Modal :open="true" @close="$emit('close')">
    <template #title>
      {{ contact ? 'Edit Contact' : 'Create New Contact' }}
    </template>
    
    <form @submit.prevent="handleSubmit">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- First Name -->
        <div>
          <label for="first_name" class="block text-sm font-medium text-gray-700">
            First Name *
          </label>
          <input
            id="first_name"
            v-model="form.first_name"
            type="text"
            required
            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
            :class="{ 'border-red-500': errors.first_name }"
          />
          <p v-if="errors.first_name" class="mt-1 text-sm text-red-600">
            {{ errors.first_name[0] }}
          </p>
        </div>

        <!-- Last Name -->
        <div>
          <label for="last_name" class="block text-sm font-medium text-gray-700">
            Last Name *
          </label>
          <input
            id="last_name"
            v-model="form.last_name"
            type="text"
            required
            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
            :class="{ 'border-red-500': errors.last_name }"
          />
          <p v-if="errors.last_name" class="mt-1 text-sm text-red-600">
            {{ errors.last_name[0] }}
          </p>
        </div>

        <!-- Email -->
        <div class="md:col-span-2">
          <label for="email" class="block text-sm font-medium text-gray-700">
            Email Address *
          </label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
            :class="{ 'border-red-500': errors.email }"
          />
          <p v-if="errors.email" class="mt-1 text-sm text-red-600">
            {{ errors.email[0] }}
          </p>
        </div>

        <!-- Phone -->
        <div>
          <label for="phone" class="block text-sm font-medium text-gray-700">
            Phone Number
          </label>
          <input
            id="phone"
            v-model="form.phone"
            type="tel"
            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
          />
        </div>

        <!-- Company -->
        <div>
          <label for="company" class="block text-sm font-medium text-gray-700">
            Company
          </label>
          <input
            id="company"
            v-model="form.company"
            type="text"
            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
          />
        </div>

        <!-- Position -->
        <div>
          <label for="position" class="block text-sm font-medium text-gray-700">
            Position
          </label>
          <input
            id="position"
            v-model="form.position"
            type="text"
            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
          />
        </div>

        <!-- Status -->
        <div>
          <label for="status" class="block text-sm font-medium text-gray-700">
            Status *
          </label>
          <select
            id="status"
            v-model="form.status"
            required
            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
            :class="{ 'border-red-500': errors.status }"
          >
            <option value="">Select Status</option>
            <option value="lead">Lead</option>
            <option value="prospect">Prospect</option>
            <option value="customer">Customer</option>
            <option value="partner">Partner</option>
          </select>
          <p v-if="errors.status" class="mt-1 text-sm text-red-600">
            {{ errors.status[0] }}
          </p>
        </div>

        <!-- Source -->
        <div>
          <label for="source" class="block text-sm font-medium text-gray-700">
            Source *
          </label>
          <select
            id="source"
            v-model="form.source"
            required
            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
            :class="{ 'border-red-500': errors.source }"
          >
            <option value="">Select Source</option>
            <option value="website">Website</option>
            <option value="referral">Referral</option>
            <option value="social_media">Social Media</option>
            <option value="event">Event</option>
            <option value="other">Other</option>
          </select>
          <p v-if="errors.source" class="mt-1 text-sm text-red-600">
            {{ errors.source[0] }}
          </p>
        </div>

        <!-- Assigned To -->
        <div class="md:col-span-2">
          <label for="assigned_to" class="block text-sm font-medium text-gray-700">
            Assigned To
          </label>
          <select
            id="assigned_to"
            v-model="form.assigned_to"
            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
          >
            <option value="">Unassigned</option>
            <option
              v-for="user in users"
              :key="user.id"
              :value="user.id"
            >
              {{ user.name }} ({{ user.role?.name }})
            </option>
          </select>
        </div>

        <!-- Notes -->
        <div class="md:col-span-2">
          <label for="notes" class="block text-sm font-medium text-gray-700">
            Notes
          </label>
          <textarea
            id="notes"
            v-model="form.notes"
            rows="3"
            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
          ></textarea>
        </div>
      </div>

      <!-- Error Message -->
      <div v-if="error" class="mt-4 rounded-md bg-red-50 p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <XCircleIcon class="h-5 w-5 text-red-400" />
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">
              {{ error }}
            </h3>
          </div>
        </div>
      </div>

      <template #actions>
        <button
          type="button"
          class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
          @click="$emit('close')"
        >
          Cancel
        </button>
        <button
          type="submit"
          :disabled="loading"
          class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ loading ? 'Saving...' : (contact ? 'Update' : 'Create') }}
        </button>
      </template>
    </form>
  </Modal>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { XCircleIcon } from '@heroicons/vue/24/outline'
import { contactApi, userApi } from '@/utils/api'
import Modal from '@/components/UI/Modal.vue'

const props = defineProps({
  contact: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['save', 'close'])

const form = ref({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  company: '',
  position: '',
  status: 'lead',
  source: 'website',
  assigned_to: '',
  notes: ''
})

const users = ref([])
const loading = ref(false)
const error = ref('')
const errors = ref({})

const loadUsers = async () => {
  try {
    const response = await userApi.list()
    users.value = response.data.data
  } catch (error) {
    console.error('Failed to load users:', error)
  }
}

const handleSubmit = async () => {
  loading.value = true
  error.value = ''
  errors.value = {}

  try {
    if (props.contact) {
      await contactApi.update(props.contact.id, form.value)
    } else {
      await contactApi.create(form.value)
    }
    
    emit('save')
    emit('close')
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors
      error.value = 'Please correct the errors below'
    } else {
      error.value = err.response?.data?.message || 'Operation failed. Please try again.'
    }
  } finally {
    loading.value = false
  }
}

// Initialize form with contact data if editing
onMounted(() => {
  loadUsers()
  
  if (props.contact) {
    form.value = {
      first_name: props.contact.first_name,
      last_name: props.contact.last_name,
      email: props.contact.email,
      phone: props.contact.phone || '',
      company: props.contact.company || '',
      position: props.contact.position || '',
      status: props.contact.status,
      source: props.contact.source,
      assigned_to: props.contact.assigned_to || '',
      notes: props.contact.notes || ''
    }
  }
})
</script>