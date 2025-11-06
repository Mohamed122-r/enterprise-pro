<template>
  <div class="filter-bar">
    <div class="filter-bar__header">
      <h3 class="filter-bar__title">Filters</h3>
      <button
        v-if="hasActiveFilters"
        class="filter-bar__clear"
        @click="clearAllFilters"
      >
        Clear All
      </button>
    </div>

    <div class="filter-bar__filters">
      <!-- Search Filter -->
      <div class="filter-bar__group">
        <label class="filter-bar__label">Search</label>
        <input
          v-model="filters.search"
          type="text"
          class="filter-bar__input"
          placeholder="Search..."
          @input="handleFilterChange"
        />
      </div>

      <!-- Status Filter -->
      <div v-if="filterConfig.status" class="filter-bar__group">
        <label class="filter-bar__label">Status</label>
        <select
          v-model="filters.status"
          class="filter-bar__select"
          @change="handleFilterChange"
        >
          <option value="">All Status</option>
          <option
            v-for="option in filterConfig.status.options"
            :key="option.value"
            :value="option.value"
          >
            {{ option.label }}
          </option>
        </select>
      </div>

      <!-- Date Range Filter -->
      <div v-if="filterConfig.dateRange" class="filter-bar__group">
        <label class="filter-bar__label">Date Range</label>
        <div class="filter-bar__date-range">
          <input
            v-model="filters.date_from"
            type="date"
            class="filter-bar__input"
            @change="handleFilterChange"
          />
          <span class="filter-bar__date-separator">to</span>
          <input
            v-model="filters.date_to"
            type="date"
            class="filter-bar__input"
            @change="handleFilterChange"
          />
        </div>
      </div>

      <!-- Select Filter (for relationships) -->
      <div
        v-for="selectFilter in selectFilters"
        :key="selectFilter.key"
        class="filter-bar__group"
      >
        <label class="filter-bar__label">{{ selectFilter.label }}</label>
        <select
          v-model="filters[selectFilter.key]"
          class="filter-bar__select"
          @change="handleFilterChange"
        >
          <option value="">All {{ selectFilter.label }}</option>
          <option
            v-for="option in selectFilter.options"
            :key="option.value"
            :value="option.value"
          >
            {{ option.label }}
          </option>
        </select>
      </div>

      <!-- Custom Slot for Additional Filters -->
      <div class="filter-bar__custom">
        <slot name="custom-filters" :filters="filters" :update="handleFilterChange" />
      </div>
    </div>

    <!-- Active Filters Display -->
    <div v-if="hasActiveFilters" class="filter-bar__active">
      <div class="filter-bar__active-title">Active Filters:</div>
      <div class="filter-bar__active-tags">
        <span
          v-for="filter in activeFilters"
          :key="filter.key"
          class="filter-bar__active-tag"
        >
          {{ filter.label }}: {{ filter.value }}
          <button
            class="filter-bar__active-remove"
            @click="removeFilter(filter.key)"
          >
            ×
          </button>
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  filterConfig: {
    type: Object,
    default: () => ({
      search: true,
      status: {
        options: []
      },
      dateRange: true
    })
  },
  initialFilters: {
    type: Object,
    default: () => ({})
  },
  selectFilters: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['filter-change'])

const filters = ref({
  search: '',
  status: '',
  date_from: '',
  date_to: '',
  ...props.initialFilters
})

// Initialize select filters from props
props.selectFilters.forEach(selectFilter => {
  if (!(selectFilter.key in filters.value)) {
    filters.value[selectFilter.key] = ''
  }
})

const hasActiveFilters = computed(() => {
  return Object.values(filters.value).some(value => 
    value !== '' && value !== null && value !== undefined
  )
})

const activeFilters = computed(() => {
  const active = []
  
  Object.entries(filters.value).forEach(([key, value]) => {
    if (value && value !== '') {
      let label = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
      let displayValue = value
      
      // Format display values
      if (key === 'date_from' || key === 'date_to') {
        displayValue = new Date(value).toLocaleDateString()
      }
      
      // Get label from select filters
      const selectFilter = props.selectFilters.find(f => f.key === key)
      if (selectFilter) {
        label = selectFilter.label
        const option = selectFilter.options.find(o => o.value === value)
        if (option) {
          displayValue = option.label
        }
      }
      
      // Get label from status options
      if (key === 'status' && props.filterConfig.status) {
        const option = props.filterConfig.status.options.find(o => o.value === value)
        if (option) {
          displayValue = option.label
        }
      }
      
      active.push({
        key,
        label,
        value: displayValue
      })
    }
  })
  
  return active
})

const handleFilterChange = () => {
  emit('filter-change', { ...filters.value })
}

const clearAllFilters = () => {
  Object.keys(filters.value).forEach(key => {
    filters.value[key] = ''
  })
  handleFilterChange()
}

const removeFilter = (filterKey) => {
  filters.value[filterKey] = ''
  handleFilterChange()
}

// Watch for external filter changes
watch(() => props.initialFilters, (newFilters) => {
  Object.assign(filters.value, newFilters)
}, { deep: true })

// Debounced filter change
let debounceTimer = null
watch(filters, () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(handleFilterChange, 300)
}, { deep: true })
</script>

<style scoped>
.filter-bar {
  @apply bg-white border border-gray-200 rounded-lg p-4 mb-6;
}

.filter-bar__header {
  @apply flex items-center justify-between mb-4;
}

.filter-bar__title {
  @apply text-lg font-medium text-gray-900;
}

.filter-bar__clear {
  @apply text-sm text-indigo-600 hover:text-indigo-800 font-medium;
}

.filter-bar__filters {
  @apply grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4;
}

.filter-bar__group {
  @apply flex flex-col;
}

.filter-bar__label {
  @apply text-sm font-medium text-gray-700 mb-1;
}

.filter-bar__input {
  @apply px-3 py-2 border border-gray-300 rounded-md focus:ring-2 
         focus:ring-indigo-500 focus:border-indigo-500;
}

.filter-bar__select {
  @apply px-3 py-2 border border-gray-300 rounded-md focus:ring-2 
         focus:ring-indigo-500 focus:border-indigo-500 bg-white;
}

.filter-bar__date-range {
  @apply flex items-center gap-2;
}

.filter-bar__date-separator {
  @apply text-gray-500 text-sm;
}

.filter-bar__custom {
  @apply col-span-full;
}

.filter-bar__active {
  @apply mt-4 pt-4 border-t border-gray-200;
}

.filter-bar__active-title {
  @apply text-sm font-medium text-gray-700 mb-2;
}

.filter-bar__active-tags {
  @apply flex flex-wrap gap-2;
}

.filter-bar__active-tag {
  @apply inline-flex items-center px-3 py-1 rounded-full text-xs 
         font-medium bg-indigo-100 text-indigo-800;
}

.filter-bar__active-remove {
  @apply ml-1 hover:text-indigo-600 focus:outline-none;
}
</style>