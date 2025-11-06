import { ref, computed } from 'vue'

export function useTable(initialFilters = {}) {
  const data = ref([])
  const loading = ref(false)
  const error = ref('')
  
  const filters = ref({
    search: '',
    page: 1,
    per_page: 15,
    sort_by: 'created_at',
    sort_order: 'desc',
    ...initialFilters
  })
  
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0
  })

  const sortedData = computed(() => {
    return [...data.value].sort((a, b) => {
      const aValue = a[filters.value.sort_by]
      const bValue = b[filters.value.sort_by]
      
      if (filters.value.sort_order === 'asc') {
        return aValue < bValue ? -1 : aValue > bValue ? 1 : 0
      } else {
        return aValue > bValue ? -1 : aValue < bValue ? 1 : 0
      }
    })
  })

  const filteredData = computed(() => {
    if (!filters.value.search) {
      return sortedData.value
    }
    
    const searchTerm = filters.value.search.toLowerCase()
    return sortedData.value.filter(item => {
      return Object.values(item).some(value => 
        String(value).toLowerCase().includes(searchTerm)
      )
    })
  })

  const paginatedData = computed(() => {
    const start = (filters.value.page - 1) * filters.value.per_page
    const end = start + filters.value.per_page
    return filteredData.value.slice(start, end)
  })

  const updateFilters = (newFilters) => {
    filters.value = { ...filters.value, ...newFilters, page: 1 }
  }

  const updatePagination = (newPagination) => {
    pagination.value = { ...pagination.value, ...newPagination }
  }

  const setData = (newData) => {
    data.value = newData
  }

  const setLoading = (isLoading) => {
    loading.value = isLoading
  }

  const setError = (errorMessage) => {
    error.value = errorMessage
  }

  const clearError = () => {
    error.value = ''
  }

  const clearFilters = () => {
    Object.keys(filters.value).forEach(key => {
      if (key !== 'per_page' && key !== 'sort_by' && key !== 'sort_order') {
        filters.value[key] = initialFilters[key] || ''
      }
    })
    filters.value.page = 1
  }

  const sort = (column) => {
    if (filters.value.sort_by === column) {
      filters.value.sort_order = filters.value.sort_order === 'asc' ? 'desc' : 'asc'
    } else {
      filters.value.sort_by = column
      filters.value.sort_order = 'asc'
    }
  }

  return {
    // State
    data,
    loading,
    error,
    filters,
    pagination,
    
    // Computed
    sortedData,
    filteredData,
    paginatedData,
    
    // Actions
    updateFilters,
    updatePagination,
    setData,
    setLoading,
    setError,
    clearError,
    clearFilters,
    sort
  }
}