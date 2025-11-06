<template>
  <div :class="[
    'loading-spinner',
    sizeClass,
    colorClass,
    { 'loading-spinner--overlay': overlay }
  ]">
    <svg
      :class="['loading-spinner__svg', { 'loading-spinner__svg--center': center }]"
      viewBox="0 0 50 50"
      :width="svgSize"
      :height="svgSize"
    >
      <circle
        class="loading-spinner__circle"
        cx="25"
        cy="25"
        r="20"
        fill="none"
        :stroke-width="strokeWidth"
      />
    </svg>
    <span v-if="text" class="loading-spinner__text">{{ text }}</span>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  size: {
    type: String,
    default: 'medium',
    validator: (value) => ['small', 'medium', 'large', 'xlarge'].includes(value)
  },
  color: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'white', 'gray'].includes(value)
  },
  text: {
    type: String,
    default: ''
  },
  overlay: {
    type: Boolean,
    default: false
  },
  center: {
    type: Boolean,
    default: false
  }
})

const sizeClass = computed(() => `loading-spinner--${props.size}`)
const colorClass = computed(() => `loading-spinner--${props.color}`)

const svgSize = computed(() => {
  const sizes = {
    small: 20,
    medium: 30,
    large: 40,
    xlarge: 50
  }
  return sizes[props.size] || 30
})

const strokeWidth = computed(() => {
  const widths = {
    small: 3,
    medium: 4,
    large: 5,
    xlarge: 6
  }
  return widths[props.size] || 4
})
</script>

<style scoped>
.loading-spinner {
  @apply inline-flex items-center;
}

.loading-spinner--overlay {
  @apply fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50;
}

.loading-spinner--small {
  @apply text-sm;
}

.loading-spinner--medium {
  @apply text-base;
}

.loading-spinner--large {
  @apply text-lg;
}

.loading-spinner--xlarge {
  @apply text-xl;
}

.loading-spinner--primary .loading-spinner__circle {
  stroke: theme('colors.indigo.600');
}

.loading-spinner--white .loading-spinner__circle {
  stroke: theme('colors.white');
}

.loading-spinner--gray .loading-spinner__circle {
  stroke: theme('colors.gray.600');
}

.loading-spinner__svg {
  @apply animate-spin;
}

.loading-spinner__svg--center {
  @apply mx-auto;
}

.loading-spinner__circle {
  stroke-dasharray: 90, 150;
  stroke-dashoffset: -35;
  stroke-linecap: round;
  animation: loading-spinner 1.5s ease-in-out infinite;
}

.loading-spinner__text {
  @apply ml-2 font-medium;
}

.loading-spinner--primary .loading-spinner__text {
  @apply text-indigo-600;
}

.loading-spinner--white .loading-spinner__text {
  @apply text-white;
}

.loading-spinner--gray .loading-spinner__text {
  @apply text-gray-600;
}

@keyframes loading-spinner {
  0% {
    stroke-dasharray: 1, 150;
    stroke-dashoffset: 0;
  }
  50% {
    stroke-dasharray: 90, 150;
    stroke-dashoffset: -35;
  }
  100% {
    stroke-dasharray: 90, 150;
    stroke-dashoffset: -124;
  }
}
</style>