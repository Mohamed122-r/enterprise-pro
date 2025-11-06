<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Users</h1>
        <p class="text-gray-600">Manage system users and permissions</p>
      </div>
      <button
        v-if="authStore.hasPermission('users.create')"
        @click="showUserForm = true"
        class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
      >
        Add User
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
            placeholder="Search users..."
            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
          >
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Role</label>
          <select
            v-model="filters.role_id"
            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
          >
            <option value="">All Roles</option>
            <option v-for="role in roles" :key="role.id" :value="role.id">
              {{ role.name }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Department</label>
          <select
            v-model="filters.department_id"
            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
          >
            <option value="">All Departments</option>
            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
              {{ dept.name }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Status</label>
          <select
            v-model="filters.status"
            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
          >
            <option value="">All Status</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Data Table -->
    <DataTable
      :columns="columns"
      :data="users.data"
      :pagination="users.meta"
      title="Users"
      description="List of all system users"
      @page-change="handlePageChange"
    >
      <template #column-name="{ item }">
        <div class="flex items-center">
          <img
            v-if="item.avatar"
            :src="item.avatar"
            :alt="item.name"
            class="h-8 w-8 rounded-full"
          >
          <div v-else class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
            <span class="text-sm font-medium text-gray-700">
              {{ item.name.charAt(0).toUpperCase() }}
            </span>
          </div>
          <div class="ml-4">
            <div class="text-sm font-medium text-gray-900">{{ item.name }}</div>
            <div class="text-sm text-gray-500">{{ item.email }}</div>
          </div>
        </div>
      </template>

      <template #column-role.name="{ item }">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
          {{ item.role.name }}
        </span>
      </template>

      <template #column-status="{ item }">
        <span
          :class="[
            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
            item.status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
          ]"
        >
          {{ item.status ? 'Active' : 'Inactive' }}
        </span>
      </template>

      <template #column-last_login_at="{ item }">
        {{ formatDate(item.last_login_at) }}
      </template>

      <template #actions="{ item }">
        <div class="flex justify-end space-x-2">
          <button
            v-if="authStore.hasPermission('users.update')"
            @click="editUser(item)"
            class="text-indigo-600 hover:text-indigo-900"
          >
            Edit
          </button>
          <button
            v-if="authStore.hasPermission('users.delete') && item.id !== authStore.user.id"
            @click="deleteUser(item)"
            class="text-red-600 hover:text-red-900"
          >
            Delete
          </button>
        </div>
      </template>
    </DataTable>

    <!-- User Form Modal -->
    <UserForm
      v-if="showUserForm"
      :user="selectedUser"
      @save="handleUserSave"
      @close="showUserForm = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useAuthStore } from '@/store/auth'
import { userApi } from '@/utils/api'
import DataTable from '@/components/UI/DataTable.vue'
import UserForm from './UserForm.vue'
import { formatDate } from '@/utils/formatters'

const authStore = useAuthStore()

const users = ref({ data: [], meta: {} })
const roles = ref([])
const departments = ref([])
const showUserForm = ref(false)
const selectedUser = ref(null)

const filters = ref({
  search: '',
  role_id: '',
  department_id: '',
  status: ''
})

const columns = [
  { key: 'name', label: 'User' },
  { key: 'role.name', label: 'Role' },
  { key: 'department.name', label: 'Department' },
  { key: 'phone', label: 'Phone' },
  { key: 'status', label: 'Status' },
  { key: 'last_login_at', label: 'Last Login' },
]

const loadUsers = async (page = 1) => {
  try {
    const response = await userApi.list({ ...filters.value, page })
    users.value = response.data
  } catch (error) {
    console.error('Failed to load users:', error)
  }
}

const loadRolesAndDepartments = async () => {
  try {
    // These would typically come from API
    roles.value = [
      { id: 1, name: 'Admin' },
      { id: 2, name: 'Manager' },
      { id: 3, name: 'User' }
    ]
    departments.value = [
      { id: 1, name: 'Sales' },
      { id: 2, name: 'Marketing' },
      { id: 3, name: 'IT' }
    ]
  } catch (error) {
    console.error('Failed to load data:', error)
  }
}

const editUser = (user) => {
  selectedUser.value = user
  showUserForm.value = true
}

const deleteUser = async (user) => {
  if (!confirm(`Are you sure you want to delete ${user.name}?`)) {
    return
  }

  try {
    await userApi.delete(user.id)
    await loadUsers()
  } catch (error) {
    console.error('Failed to delete user:', error)
    alert('Failed to delete user')
  }
}

const handleUserSave = () => {
  showUserForm.value = false
  selectedUser.value = null
  loadUsers()
}

const handlePageChange = (page) => {
  loadUsers(page)
}

// Watch filters and reload data
watch(filters, () => {
  loadUsers()
}, { deep: true })

onMounted(() => {
  loadUsers()
  loadRolesAndDepartments()
})
</script>