import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/store/auth'

// مكون NotFound محلي بدون استيراد ملف
const NotFound = {
  template: `
    <div class="min-h-screen flex items-center justify-center bg-gray-50 px-4">
      <div class="text-center max-w-md">
        <div class="text-9xl font-bold text-gray-200 mb-4">404</div>
        <h1 class="text-3xl font-bold text-gray-800 mb-4">Page Not Found</h1>
        <p class="text-gray-600 mb-8 text-lg">
          Sorry, the page you are looking for doesn't exist or has been moved.
        </p>
        <div class="space-y-4">
          <button 
            @click="goHome" 
            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200"
          >
            Go to Dashboard
          </button>
          <button 
            @click="goBack" 
            class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-6 rounded-lg transition duration-200"
          >
            Go Back
          </button>
        </div>
      </div>
    </div>
  `,
  methods: {
    goHome() {
      this.$router.push('/');
    },
    goBack() {
      this.$router.back();
    }
  }
}

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
  // مسار للصفحات غير الموجودة - استخدام المكون المحلي
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: NotFound // استخدام المكون المحلي مباشرة
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
