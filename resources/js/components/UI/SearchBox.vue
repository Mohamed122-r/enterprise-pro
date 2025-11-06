<template>
  <div class="search-box">
    <div class="search-box__container">
      <MagnifyingGlassIcon class="search-box__icon" />
      <input
        ref="inputRef"
        v-model="searchQuery"
        type="text"
        class="search-box__input"
        :placeholder="placeholder"
        @input="handleInput"
        @focus="handleFocus"
        @blur="handleBlur"
        @keydown="handleKeydown"
      />
      <button
        v-if="searchQuery"
        class="search-box__clear"
        @click="clearSearch"
      >
        <XMarkIcon class="h-4 w-4" />
      </button>
    </div>

    <!-- Search Results Dropdown -->
    <div
      v-if="showResults && filteredResults.length > 0"
      class="search-box__results"
    >
      <div
        v-for="(result, index) in filteredResults"
        :key="result.id"
        :class="[
          'search-box__result',
          { 'search-box__result--active': activeIndex === index }
        ]"
        @click="selectResult(result)"
        @mouseenter="activeIndex = index"
      >
        <div class="search-box__result-content">
          <span class="search-box__result-title">{{ result.title }}</span>
          <span v-if="result.subtitle" class="search-box__result-subtitle">
            {{ result.subtitle }}
          </span>
        </div>
        <span class="search-box__result-type">{{ result.type }}</span>
      </div>
    </div>

    <!-- No Results -->
    <div
      v-else-if="showResults && searchQuery && filteredResults.length === 0"
      class="search-box__no-results"
    >
      No results found for "{{ searchQuery }}"
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { MagnifyingGlassIcon, XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  placeholder: {
    type: String,
    default: 'Search...'
  },
  data: {
    type: Array,
    default: () => []
  },
  searchKeys: {
    type: Array,
    default: () => ['title', 'subtitle']
  },
  debounce: {
    type: Number,
    default: 300
  }
})

const emit = defineEmits(['search', 'select', 'clear'])

const searchQuery = ref('')
const showResults = ref(false)
const activeIndex = ref(-1)
const inputRef = ref(null)
let debounceTimer = null

const filteredResults = computed(() => {
  if (!searchQuery.value) return []

  const query = searchQuery.value.toLowerCase()
  return props.data.filter(item => {
    return props.searchKeys.some(key => {
      const value = item[key]
      return value && value.toString().toLowerCase().includes(query)
    })
  }).slice(0, 10) // Limit results
})

const handleInput = () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    emit('search', searchQuery.value)
  }, props.debounce)
}

const handleFocus = () => {
  showResults.value = true
}

const handleBlur = () => {
  // Delay hiding to allow for click events
  setTimeout(() => {
    showResults.value = false
    activeIndex.value = -1
  }, 200)
}

const handleKeydown = (event) => {
  switch (event.key) {
    case 'ArrowDown':
      event.preventDefault()
      activeIndex.value = Math.min(activeIndex.value + 1, filteredResults.value.length - 1)
      break
    case 'ArrowUp':
      event.preventDefault()
      activeIndex.value = Math.max(activeIndex.value - 1, -1)
      break
    case 'Enter':
      event.preventDefault()
      if (activeIndex.value >= 0) {
        selectResult(filteredResults.value[activeIndex.value])
      }
      break
    case 'Escape':
      clearSearch()
      break
  }
}

const clearSearch = () => {
  searchQuery.value = ''
  showResults.value = false
  activeIndex.value = -1
  emit('clear')
  inputRef.value?.focus()
}

const selectResult = (result) => {
  searchQuery.value = result.title
  showResults.value = false
  activeIndex.value = -1
  emit('select', result)
}

const focus = () => {
  inputRef.value?.focus()
}

// Expose methods
defineExpose({
  focus,
  clear: clearSearch
})
</script>

<style scoped>
.search-box {
  @apply relative w-full;
}

.search-box__container {
  @apply relative flex items-center;
}

.search-box__icon {
  @apply absolute left-3 h-5 w-5 text-gray-400;
}

.search-box__input {
  @apply w-full pl-10 pr-10 py-2 border border-gray-300 rounded-lg 
         focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 
         placeholder-gray-400 bg-white;
}

.search-box__clear {
  @apply absolute right-3 p-1 text-gray-400 hover:text-gray-600 
         transition-colors rounded;
}

.search-box__results {
  @apply absolute top-full left-0 right-0 mt-1 bg-white border 
         border-gray-200 rounded-lg shadow-lg z-50 max-h-64 overflow-y-auto;
}

.search-box__result {
  @apply flex items-center justify-between p-3 hover:bg-gray-50 
         cursor-pointer border-b border-gray-100 last:border-b-0;
}

.search-box__result--active {
  @apply bg-indigo-50;
}

.search-box__result-content {
  @apply flex flex-col;
}

.search-box__result-title {
  @apply font-medium text-gray-900;
}

.search-box__result-subtitle {
  @apply text-sm text-gray-500;
}

.search-box__result-type {
  @apply px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 
         rounded-full capitalize;
}

.search-box__no-results {
  @apply absolute top-full left-0 right-0 mt-1 p-4 bg-white border 
         border-gray-200 rounded-lg shadow-lg text-gray-500 text-center;
}
</style>