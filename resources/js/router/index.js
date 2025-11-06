import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/store/auth'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: () => import('@/components/Auth/Login.vue'),
    meta: { guest: true }
  },
  {
    path: '/',
    component: () => import('@/components/Layout/AppLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'dashboard',
        component: () => import('@/components/Dashboard/Dashboard.vue')
      },
      {
        path: '/users',
        name: 'users',
        component: () => import('@/components/Users/UserList.vue')
      },
      {
        path: '/contacts',
        name: 'contacts',
        component: () => import('@/components/CRM/ContactList.vue')
      },
      {
        path: '/attendance',
        name: 'attendance',
        component: () => import('@/components/Attendance/AttendanceList.vue')
      },
      {
        path: '/invoices',
        name: 'invoices',
        component: () => import('@/components/Accounting/InvoiceList.vue')
      },
      {
        path: '/chat',
        name: 'chat',
        component: () => import('@/components/Chat/ChatContainer.vue')
      }
    ]
  },
  // مسار للصفحات غير الموجودة
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('@/components/Common/NotFound.vue')
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  // تهيئة المتجر إذا لزم الأمر
  if (!authStore.user && authStore.token) {
    authStore.initialize()
  }

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  } else if (to.meta.guest && authStore.isAuthenticated) {
    next('/')
  } else {
    next()
  }
})

export default router
