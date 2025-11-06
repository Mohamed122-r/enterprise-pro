<template>
  <div class="stats-card">
    <div class="stats-card__content">
      <div class="stats-card__header">
        <div
          :class="[
            'stats-card__icon',
            `stats-card__icon--${color}`
          ]"
        >
          <component :is="icon" class="h-6 w-6" />
        </div>
        
        <div
          v-if="trend !== null"
          :class="[
            'stats-card__trend',
            `stats-card__trend--${trendDirection}`
          ]"
        >
          <TrendingUpIcon v-if="trendDirection === 'up'" class="h-4 w-4" />
          <TrendingDownIcon v-else class="h-4 w-4" />
          <span>{{ Math.abs(trend) }}%</span>
        </div>
      </div>

      <div class="stats-card__body">
        <p class="stats-card__value">{{ formattedValue }}</p>
        <p class="stats-card__label">{{ label }}</p>
      </div>

      <div v-if="description" class="stats-card__footer">
        <p class="stats-card__description">{{ description }}</p>
      </div>
    </div>

    <!-- Progress Bar -->
    <div v-if="progress !== null" class="stats-card__progress">
      <div class="stats-card__progress-bar">
        <div
          :class="[
            'stats-card__progress-fill',
            `stats-card__progress-fill--${color}`
          ]"
          :style="{ width: `${progress}%` }"
        ></div>
      </div>
      <span class="stats-card__progress-text">{{ progress }}%</span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import {
  TrendingUpIcon,
  TrendingDownIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  value: {
    type: [Number, String],
    required: true
  },
  label: {
    type: String,
    required: true
  },
  icon: {
    type: Object,
    required: true
  },
  color: {
    type: String,
    default: 'blue',
    validator: (value) => ['blue', 'green', 'red', 'yellow', 'purple', 'indigo'].includes(value)
  },
  trend: {
    type: Number,
    default: null
  },
  description: {
    type: String,
    default: ''
  },
  progress: {
    type: Number,
    default: null
  },
  format: {
    type: String,
    default: 'number', // 'number', 'currency', 'percentage'
    validator: (value) => ['number', 'currency', 'percentage'].includes(value)
  },
  currency: {
    type: String,
    default: 'USD'
  }
})

const trendDirection = computed(() => {
  return props.trend >= 0 ? 'up' : 'down'
})

const formattedValue = computed(() => {
  const value = typeof props.value === 'string' ? parseFloat(props.value) : props.value
  
  switch (props.format) {
    case 'currency':
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: props.currency
      }).format(value)
    
    case 'percentage':
      return `${value}%`
    
    case 'number':
    default:
      return new Intl.NumberFormat('en-US').format(value)
  }
})

const colorClasses = {
  blue: 'bg-blue-500 text-blue-600 bg-blue-50',
  green: 'bg-green-500 text-green-600 bg-green-50',
  red: 'bg-red-500 text-red-600 bg-red-50',
  yellow: 'bg-yellow-500 text-yellow-600 bg-yellow-50',
  purple: 'bg-purple-500 text-purple-600 bg-purple-50',
  indigo: 'bg-indigo-500 text-indigo-600 bg-indigo-50'
}

const progressColorClasses = {
  blue: 'bg-blue-600',
  green: 'bg-green-600',
  red: 'bg-red-600',
  yellow: 'bg-yellow-600',
  purple: 'bg-purple-600',
  indigo: 'bg-indigo-600'
}
</script>

<style scoped>
.stats-card {
  @apply bg-white rounded-lg border border-gray-200 p-6 shadow-sm;
}

.stats-card__content {
  @apply space-y-4;
}

.stats-card__header {
  @apply flex items-center justify-between;
}

.stats-card__icon {
  @apply p-3 rounded-lg;
}

.stats-card__icon--blue {
  @apply bg-blue-50 text-blue-600;
}

.stats-card__icon--green {
  @apply bg-green-50 text-green-600;
}

.stats-card__icon--red {
  @apply bg-red-50 text-red-600;
}

.stats-card__icon--yellow {
  @apply bg-yellow-50 text-yellow-600;
}

.stats-card__icon--purple {
  @apply bg-purple-50 text-purple-600;
}

.stats-card__icon--indigo {
  @apply bg-indigo-50 text-indigo-600;
}

.stats-card__trend {
  @apply flex items-center gap-1 text-sm font-medium;
}

.stats-card__trend--up {
  @apply text-green-600;
}

.stats-card__trend--down {
  @apply text-red-600;
}

.stats-card__body {
  @apply space-y-1;
}

.stats-card__value {
  @apply text-3xl font-bold text-gray-900;
}

.stats-card__label {
  @apply text-sm text-gray-500;
}

.stats-card__footer {
  @apply pt-2;
}

.stats-card__description {
  @apply text-xs text-gray-400;
}

.stats-card__progress {
  @apply mt-4 flex items-center gap-3;
}

.stats-card__progress-bar {
  @apply flex-1 h-2 bg-gray-200 rounded-full overflow-hidden;
}

.stats-card__progress-fill {
  @apply h-full rounded-full transition-all duration-500 ease-out;
}

.stats-card__progress-fill--blue {
  @apply bg-blue-600;
}

.stats-card__progress-fill--green {
  @apply bg-green-600;
}

.stats-card__progress-fill--red {
  @apply bg-red-600;
}

.stats-card__progress-fill--yellow {
  @apply bg-yellow-600;
}

.stats-card__progress-fill--purple {
  @apply bg-purple-600;
}

.stats-card__progress-fill--indigo {
  @apply bg-indigo-600;
}

.stats-card__progress-text {
  @apply text-sm font-medium text-gray-600;
}
</style>