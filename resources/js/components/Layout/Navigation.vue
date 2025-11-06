<template>
  <nav class="flex-1 px-2 pb-4 space-y-1">
    <router-link
      v-for="item in navigation"
      :key="item.name"
      :to="item.href"
      :class="[
        isCurrentRoute(item.href)
          ? 'bg-gray-100 text-gray-900'
          : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900',
        'group flex items-center px-2 py-2 text-sm font-medium rounded-md'
      ]"
    >
      <component
        :is="item.icon"
        :class="[
          isCurrentRoute(item.href)
            ? 'text-gray-500'
            : 'text-gray-400 group-hover:text-gray-500',
          'mr-3 flex-shrink-0 h-6 w-6'
        ]"
        aria-hidden="true"
      />
      {{ item.name }}
      
      <span
        v-if="item.badge"
        :class="[
          'ml-auto inline-block py-0.5 px-2 text-xs rounded-full',
          item.badgeColor || 'bg-gray-100 text-gray-800'
        ]"
      >
        {{ item.badge }}
      </span>
    </router-link>
  </nav>
</template>

<script setup>
import { useRoute } from 'vue-router'
import {
  HomeIcon,
  UsersIcon,
  UserGroupIcon,
  ClockIcon,
  DocumentTextIcon,
  ChatBubbleLeftRightIcon,
  ChartBarIcon,
  CogIcon
} from '@heroicons/vue/24/outline'

const route = useRoute()

const navigation = [
  { name: 'Dashboard', href: '/', icon: HomeIcon },
  { name: 'Users', href: '/users', icon: UsersIcon },
  { name: 'Contacts', href: '/contacts', icon: UserGroupIcon },
  { name: 'Attendance', href: '/attendance', icon: ClockIcon },
  { name: 'Invoices', href: '/invoices', icon: DocumentTextIcon },
  { name: 'Chat', href: '/chat', icon: ChatBubbleLeftRightIcon, badge: 'New' },
  { name: 'Reports', href: '/reports', icon: ChartBarIcon },
  { name: 'Settings', href: '/settings', icon: CogIcon },
]

const isCurrentRoute = (href) => {
  if (href === '/') {
    return route.path === '/'
  }
  return route.path.startsWith(href)
}
</script>