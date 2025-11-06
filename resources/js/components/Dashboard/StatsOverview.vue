<template>
  <div>
    <h2 class="text-lg leading-6 font-medium text-gray-900">Overview</h2>
    <div class="mt-2 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
      <div
        v-for="stat in statsCards"
        :key="stat.name"
        class="bg-white overflow-hidden shadow rounded-lg"
      >
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <component
                :is="stat.icon"
                class="h-6 w-6 text-gray-400"
                aria-hidden="true"
              />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">
                  {{ stat.name }}
                </dt>
                <dd>
                  <div class="text-lg font-medium text-gray-900">
                    {{ stat.value }}
                  </div>
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  UsersIcon,
  UserGroupIcon,
  CurrencyDollarIcon,
  ClockIcon
} from '@heroicons/vue/outline'

const props = defineProps({
  stats: {
    type: Object,
    default: () => ({})
  }
})

const statsCards = computed(() => [
  {
    name: 'Total Users',
    value: props.stats.total_users || props.stats.my_contacts || 0,
    icon: UsersIcon,
  },
  {
    name: 'Active Contacts',
    value: props.stats.total_contacts || props.stats.my_invoices || 0,
    icon: UserGroupIcon,
  },
  {
    name: 'Revenue',
    value: props.stats.revenue ? `$${props.stats.revenue.toLocaleString()}` : '$0',
    icon: CurrencyDollarIcon,
  },
  {
    name: 'Attendance Days',
    value: props.stats.pending_invoices || props.stats.attendance_days || 0,
    icon: ClockIcon,
  },
])
</script>