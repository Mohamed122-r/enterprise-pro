<template>
  <div class="bg-white shadow-sm rounded-lg overflow-hidden">
    <!-- Table Header -->
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
      <div>
        <h3 class="text-lg font-medium text-gray-900">{{ title }}</h3>
        <p class="text-sm text-gray-500" v-if="description">{{ description }}</p>
      </div>
      <slot name="header-actions" />
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th 
              v-for="column in columns" 
              :key="column.key"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              {{ column.label }}
            </th>
            <th v-if="$slots.actions" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="(item, index) in data" :key="item.id || index">
            <td 
              v-for="column in columns" 
              :key="column.key"
              class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
            >
              <slot :name="`column-${column.key}`" :item="item">
                {{ getNestedValue(item, column.key) }}
              </slot>
            </td>
            <td v-if="$slots.actions" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <slot name="actions" :item="item" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Empty State -->
    <div v-if="data.length === 0" class="text-center py-12">
      <div class="text-gray-400">
        <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No data found</h3>
        <p class="mt-1 text-sm text-gray-500">Get started by creating a new record.</p>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="pagination && data.length > 0" class="px-6 py-4 border-t border-gray-200">
      <Pagination 
        :current-page="pagination.current_page"
        :last-page="pagination.last_page"
        :total="pagination.total"
        @page-change="$emit('page-change', $event)"
      />
    </div>
  </div>
</template>

<script setup>
import Pagination from './Pagination.vue'

defineProps({
  title: String,
  description: String,
  columns: {
    type: Array,
    required: true
  },
  data: {
    type: Array,
    default: () => []
  },
  pagination: Object
})

defineEmits(['page-change'])

const getNestedValue = (obj, path) => {
  return path.split('.').reduce((acc, part) => acc && acc[part], obj)
}
</script>