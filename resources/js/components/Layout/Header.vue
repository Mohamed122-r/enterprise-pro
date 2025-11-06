<template>
  <div class="flex-shrink-0 flex border-b border-gray-200 bg-white">
    <div class="flex-1 px-4 flex justify-between">
      <div class="flex-1 flex">
        <div class="flex-1 flex lg:ml-0">
          <div class="flex items-center lg:hidden">
            <button
              type="button"
              class="px-4 border-r border-gray-200 text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 lg:hidden"
              @click="$emit('toggle-sidebar')"
            >
              <span class="sr-only">Open sidebar</span>
              <MenuAlt2Icon class="h-6 w-6" aria-hidden="true" />
            </button>
          </div>
          <div class="flex items-center px-4">
            <h1 class="text-2xl font-bold text-gray-900">{{ currentPageTitle }}</h1>
          </div>
        </div>
      </div>
      <div class="ml-4 flex items-center lg:ml-6">
        <!-- Notifications -->
        <button
          type="button"
          class="bg-white p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          <span class="sr-only">View notifications</span>
          <BellIcon class="h-6 w-6" aria-hidden="true" />
        </button>

        <!-- Profile dropdown -->
        <div class="ml-3 relative">
          <div>
            <button
              type="button"
              class="max-w-xs bg-white flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              @click="profileMenuOpen = !profileMenuOpen"
            >
              <span class="sr-only">Open user menu</span>
              <img
                v-if="authStore.user?.avatar"
                class="h-8 w-8 rounded-full"
                :src="authStore.user.avatar"
                alt=""
              />
              <div v-else class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                <span class="text-sm font-medium text-gray-700">
                  {{ authStore.user?.name?.charAt(0)?.toUpperCase() }}
                </span>
              </div>
              <span class="ml-2 text-sm font-medium text-gray-700 hidden md:block">
                {{ authStore.user?.name }}
              </span>
              <ChevronDownIcon class="ml-1 h-4 w-4 text-gray-400" />
            </button>
          </div>
          <transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
          >
            <div
              v-show="profileMenuOpen"
              class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none"
              role="menu"
            >
              <router-link
                to="/profile"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                role="menuitem"
              >
                Your Profile
              </router-link>
              <a
                href="#"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                role="menuitem"
                @click="handleLogout"
              >
                Sign out
              </a>
            </div>
          </transition>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/store/auth'
import { BellIcon, MenuAlt2Icon, ChevronDownIcon } from '@heroicons/vue/outline'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const profileMenuOpen = ref(false)

const currentPageTitle = computed(() => {
  const routeName = route.name
  const titles = {
    dashboard: 'Dashboard',
    users: 'User Management',
    contacts: 'Contact Management',
    attendance: 'Attendance Tracking',
    invoices: 'Invoice Management',
    chat: 'Team Chat',
    reports: 'Reports & Analytics'
  }
  return titles[routeName] || 'Enterprise Pro'
})

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>