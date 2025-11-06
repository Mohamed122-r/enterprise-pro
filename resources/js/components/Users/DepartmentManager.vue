<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Department Management</h1>
        <p class="text-gray-600">Manage organizational departments</p>
      </div>
      <button
        v-if="authStore.hasPermission('settings.manage')"
        @click="showDepartmentForm = true"
        class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
      >
        Add Department
      </button>
    </div>

    <!-- Departments Table -->
    <DataTable
      :columns="columns"
      :data="departments"
      title="Departments"
      description="List of all organizational departments"
    >
      <template #column-name="{ item }">
        <div class="flex items-center">
          <div class="ml-4">
            <div class="text-sm font-medium text-gray-900">{{ item.name }}</div>
            <div class="text-sm text-gray-500">{{ item.description }}</div>
          </div>
        </div>
      </template>

      <template #column-manager="{ item }">
        <span v-if="item.manager" class="text-sm text-gray-900">
          {{ item.manager.name }}
        </span>
        <span v-else class="text-sm text-gray-500">No manager</span>
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

      <template #column-users_count="{ item }">
        <span class="text-sm text-gray-900">{{ item.users_count }} users</span>
      </template>

      <template #actions="{ item }">
        <div class="flex justify-end space-x-2">
          <button
            v-if="authStore.hasPermission('settings.manage')"
            @click="editDepartment(item)"
            class="text-indigo-600 hover:text-indigo-900"
          >
            Edit
          </button>
          <button
            v-if="authStore.hasPermission('settings.manage') && item.users_count === 0"
            @click="deleteDepartment(item)"
            class="text-red-600 hover:text-red-900"
          >
            Delete
          </button>
        </div>
      </template>
    </DataTable>

    <!-- Department Form Modal -->
    <DepartmentForm
      v-if="showDepartmentForm"
      :department="selectedDepartment"
      @save="handleDepartmentSave"
      @close="showDepartmentForm = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/store/auth'
import DataTable from '@/components/UI/DataTable.vue'
import DepartmentForm from './DepartmentForm.vue'

const authStore = useAuthStore()

const departments = ref([])
const showDepartmentForm = ref(false)
const selectedDepartment = ref(null)

const columns = [
  { key: 'name', label: 'Department' },
  { key: 'manager', label: 'Manager' },
  { key: 'status', label: 'Status' },
  { key: 'users_count', label: 'Users' },
]

const loadDepartments = async () => {
  try {
    // Mock data - in real app, this would come from API
    departments.value = [
      {
        id: 1,
        name: 'Sales Department',
        description: 'Handles all sales operations and customer acquisition',
        manager: { id: 2, name: 'Sarah Johnson' },
        status: true,
        users_count: 8
      },
      {
        id: 2,
        name: 'Marketing Department',
        description: 'Responsible for brand promotion and marketing campaigns',
        manager: { id: 3, name: 'Mike Chen' },
        status: true,
        users_count: 6
      },
      {
        id: 3,
        name: 'IT Department',
        description: 'Manages technology infrastructure and software development',
        manager: { id: 4, name: 'Alex Rodriguez' },
        status: true,
        users_count: 12
      },
      {
        id: 4,
        name: 'HR Department',
        description: 'Handles human resources and employee relations',
        manager: null,
        status: true,
        users_count: 4
      },
      {
        id: 5,
        name: 'Finance Department',
        description: 'Manages financial operations and accounting',
        manager: { id: 5, name: 'Lisa Wang' },
        status: true,
        users_count: 5
      }
    ]
  } catch (error) {
    console.error('Failed to load departments:', error)
  }
}

const editDepartment = (department) => {
  selectedDepartment.value = department
  showDepartmentForm.value = true
}

const deleteDepartment = async (department) => {
  if (!confirm(`Are you sure you want to delete the "${department.name}" department?`)) {
    return
  }

  try {
    // await departmentApi.delete(department.id)
    await loadDepartments()
  } catch (error) {
    console.error('Failed to delete department:', error)
    alert('Failed to delete department')
  }
}

const handleDepartmentSave = () => {
  showDepartmentForm.value = false
  selectedDepartment.value = null
  loadDepartments()
}

onMounted(() => {
  loadDepartments()
})
</script>