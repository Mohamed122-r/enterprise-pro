<template>
  <div class="hidden lg:flex lg:flex-shrink-0">
    <div class="flex flex-col w-64">
      <!-- Sidebar component -->
      <div class="flex flex-col flex-grow bg-indigo-800 pt-5 pb-4 overflow-y-auto">
        <div class="flex items-center flex-shrink-0 px-4">
          <img class="h-8 w-auto" src="/logo.png" alt="Enterprise Pro">
          <span class="ml-2 text-white text-xl font-bold">EnterprisePro</span>
        </div>
        <nav class="mt-5 flex-1 flex flex-col divide-y divide-indigo-700 overflow-y-auto" aria-label="Sidebar">
          <div class="px-2 space-y-1">
            <router-link
              v-for="item in navigation"
              :key="item.name"
              :to="item.href"
              :class="[
                $route.path === item.href
                  ? 'bg-indigo-900 text-white'
                  : 'text-indigo-100 hover:bg-indigo-700 hover:text-white',
                'group flex items-center px-2 py-2 text-sm leading-6 font-medium rounded-md'
              ]"
            >
              <component
                :is="item.icon"
                :class="[
                  $route.path === item.href ? 'text-white' : 'text-indigo-300 group-hover:text-white',
                  'mr-4 flex-shrink-0 h-6 w-6'
                ]"
                aria-hidden="true"
              />
              {{ item.name }}
            </router-link>
          </div>
        </nav>
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  HomeIcon,
  UsersIcon,
  UserGroupIcon,
  ClockIcon,
  DocumentTextIcon,
  ChatAlt2Icon,
  ChartBarIcon
} from '@heroicons/vue/outline'

const navigation = [
  { name: 'Dashboard', href: '/', icon: HomeIcon },
  { name: 'Users', href: '/users', icon: UsersIcon },
  { name: 'Contacts', href: '/contacts', icon: UserGroupIcon },
  { name: 'Attendance', href: '/attendance', icon: ClockIcon },
  { name: 'Invoices', href: '/invoices', icon: DocumentTextIcon },
  { name: 'Chat', href: '/chat', icon: ChatAlt2Icon },
  { name: 'Reports', href: '/reports', icon: ChartBarIcon },
]
</script>