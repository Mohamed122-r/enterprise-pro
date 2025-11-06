<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Role Management</h1>
        <p class="text-gray-600">Manage system roles and permissions</p>
      </div>
      <button
        v-if="authStore.hasPermission('settings.manage')"
        @click="showRoleForm = true"
        class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
      >
        Add Role
      </button>
    </div>

    <!-- Roles Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="role in roles"
        :key="role.id"
        class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm"
      >
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium text-gray-900">{{ role.name }}</h3>
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
            {{ role.users_count }} users
          </span>
        </div>
        
        <p class="text-gray-600 text-sm mb-4">{{ role.description }}</p>
        
        <div class="space-y-2 mb-4">
          <h4 class="text-sm font-medium text-gray-700">Permissions:</h4>
          <div class="flex flex-wrap gap-1">
            <span
              v-for="permission in role.permissions.slice(0, 5)"
              :key="permission"
              class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800"
            >
              {{ permission }}
            </span>
            <span
              v-if="role.permissions.length > 5"
              class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800"
            >
              +{{ role.permissions.length - 5 }} more
            </span>
          </div>
        </div>
        
        <div class="flex space-x-2">
          <button
            v-if="authStore.hasPermission('settings.manage')"
            @click="editRole(role)"
            class="flex-1 bg-gray-100 text-gray-700 px-3 py-2 rounded text-sm font-medium hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500"
          >
            Edit
          </button>
          <button
            v-if="authStore.hasPermission('settings.manage') && role.name !== 'admin'"
            @click="deleteRole(role)"
            class="flex-1 bg-red-100 text-red-700 px-3 py-2 rounded text-sm font-medium hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500"
          >
            Delete
          </button>
        </div>
      </div>
    </div>

    <!-- Role Form Modal -->
    <RoleForm
      v-if="showRoleForm"
      :role="selectedRole"
      @save="handleRoleSave"
      @close="showRoleForm = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/store/auth'
import RoleForm from './RoleForm.vue'

const authStore = useAuthStore()

const roles = ref([])
const showRoleForm = ref(false)
const selectedRole = ref(null)

const loadRoles = async () => {
  try {
    // Mock data - in real app, this would come from API
    roles.value = [
      {
        id: 1,
        name: 'admin',
        description: 'System Administrator with full access',
        permissions: [
          'users.create', 'users.read', 'users.update', 'users.delete',
          'contacts.create', 'contacts.read', 'contacts.update', 'contacts.delete',
          'invoices.create', 'invoices.read', 'invoices.update', 'invoices.delete',
          'reports.view', 'settings.manage'
        ],
        users_count: 1
      },
      {
        id: 2,
        name: 'manager',
        description: 'Department Manager with limited administrative access',
        permissions: [
          'users.read', 'contacts.create', 'contacts.read', 'contacts.update',
          'invoices.create', 'invoices.read', 'invoices.update', 'reports.view'
        ],
        users_count: 3
      },
      {
        id: 3,
        name: 'user',
        description: 'Regular system user',
        permissions: ['contacts.read', 'invoices.read'],
        users_count: 15
      }
    ]
  } catch (error) {
    console.error('Failed to load roles:', error)
  }
}

const editRole = (role) => {
  selectedRole.value = role
  showRoleForm.value = true
}

const deleteRole = async (role) => {
  if (!confirm(`Are you sure you want to delete the "${role.name}" role?`)) {
    return
  }

  try {
    // await roleApi.delete(role.id)
    await loadRoles()
  } catch (error) {
    console.error('Failed to delete role:', error)
    alert('Failed to delete role')
  }
}

const handleRoleSave = () => {
  showRoleForm.value = false
  selectedRole.value = null
  loadRoles()
}

onMounted(() => {
  loadRoles()
})
</script>