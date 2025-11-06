<template>
  <Modal :open="true" @close="$emit('close')">
    <template #title>
      {{ user ? 'Edit User' : 'Create New User' }}
    </template>
    
    <form @submit.prevent="handleSubmit">
      <div class="space-y-4">
        <!-- Name -->
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700">
            Full Name *
          </label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            required
            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
            :class="{ 'border-red-500': errors.name }"
          />
          <p v-if="errors.name" class="mt-1 text-sm text-red-600">
            {{ errors.name[0] }}
          </p>
        </div>

        <!-- Email -->
        <div>
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

        <!-- Department -->
        <div>
          <label for="department" class="block text-sm font-medium text-gray-700">
            Department *
          </label>
          <select
            id="department"
            v-model="form.department_id"
            required
            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
            :class="{ 'border-red-500': errors.department_id }"
          >
            <option value="">Select Department</option>
            <option
              v-for="department in departments"
              :key="department.id"
              :value="department.id"
            >
              {{ department.name }}
            </option>
          </select>
          <p v-if="errors.department_id" class="mt-1 text-sm text-red-600">
            {{ errors.department_id[0] }}
          </p>
        </div>

        <!-- Role -->
        <div>
          <label for="role" class="block text-sm font-medium text-gray-700">
            Role *
          </label>
          <select
            id="role"
            v-model="form.role_id"
            required
            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
            :class="{ 'border-red-500': errors.role_id }"
          >
            <option value="">Select Role</option>
            <option
              v-for="role in roles"
              :key="role.id"
              :value="role.id"
            >
              {{ role.name }}
            </option>
          </select>
          <p v-if="errors.role_id" class="mt-1 text-sm text-red-600">
            {{ errors.role_id[0] }}
          </p>
        </div>

        <!-- Password -->
        <div v-if="!user">
          <label for="password" class="block text-sm font-medium text-gray-700">
            Password *
          </label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            required
            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
            :class="{ 'border-red-500': errors.password }"
          />
          <p v-if="errors.password" class="mt-1 text-sm text-red-600">
            {{ errors.password[0] }}
          </p>
        </div>

        <!-- Confirm Password -->
        <div v-if="!user">
          <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
            Confirm Password *
          </label>
          <input
            id="password_confirmation"
            v-model="form.password_confirmation"
            type="password"
            required
            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
          />
        </div>

        <!-- Status -->
        <div>
          <label class="flex items-center">
            <input
              v-model="form.status"
              type="checkbox"
              class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
            />
            <span class="ml-2 text-sm text-gray-700">Active User</span>
          </label>
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
          {{ loading ? 'Saving...' : (user ? 'Update' : 'Create') }}
        </button>
      </template>
    </form>
  </Modal>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { XCircleIcon } from '@heroicons/vue/24/outline'
import { userApi } from '@/utils/api'
import Modal from '@/components/UI/Modal.vue'

const props = defineProps({
  user: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['save', 'close'])

const form = ref({
  name: '',
  email: '',
  phone: '',
  department_id: '',
  role_id: '',
  password: '',
  password_confirmation: '',
  status: true
})

const departments = ref([])
const roles = ref([])
const loading = ref(false)
const error = ref('')
const errors = ref({})

const loadFormData = async () => {
  try {
    // In a real app, these would come from API
    departments.value = [
      { id: 1, name: 'Sales' },
      { id: 2, name: 'Marketing' },
      { id: 3, name: 'IT' },
      { id: 4, name: 'HR' },
      { id: 5, name: 'Finance' }
    ]
    
    roles.value = [
      { id: 1, name: 'Admin' },
      { id: 2, name: 'Manager' },
      { id: 3, name: 'User' }
    ]
  } catch (err) {
    console.error('Failed to load form data:', err)
  }
}

const handleSubmit = async () => {
  loading.value = true
  error.value = ''
  errors.value = {}

  try {
    if (props.user) {
      await userApi.update(props.user.id, form.value)
    } else {
      await userApi.create(form.value)
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

// Initialize form with user data if editing
onMounted(() => {
  loadFormData()
  
  if (props.user) {
    form.value = {
      name: props.user.name,
      email: props.user.email,
      phone: props.user.phone || '',
      department_id: props.user.department_id,
      role_id: props.user.role_id,
      status: props.user.status,
      password: '',
      password_confirmation: ''
    }
  }
})
</script>