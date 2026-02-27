import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: () => import('../views/LoginView.vue'),
    meta: { requiresGuest: true },
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('../views/RegisterView.vue'),
    meta: { requiresGuest: true },
  },
  {
    path: '/',
    name: 'dashboard',
    component: () => import('../views/DashboardView.vue'),
    meta: { requiresAuth: true, requiresUser: true },
  },
  {
    path: '/portfolio',
    name: 'portfolio',
    component: () => import('../views/PortfolioView.vue'),
    meta: { requiresAuth: true, requiresUser: true },
  },
  {
    path: '/projection',
    name: 'projection',
    component: () => import('../views/ProjectionView.vue'),
    meta: { requiresAuth: true, requiresUser: true },
  },
  {
    path: '/calendar',
    name: 'calendar',
    component: () => import('../views/CalendarView.vue'),
    meta: { requiresAuth: true, requiresUser: true },
  },
  {
    path: '/import',
    name: 'import',
    component: () => import('../views/ImportView.vue'),
    meta: { requiresAuth: true, requiresUser: true },
  },
  {
    path: '/admin',
    name: 'admin',
    component: () => import('../views/AdminView.vue'),
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to) => {
  const token = localStorage.getItem('token')
  const user = JSON.parse(localStorage.getItem('user') || 'null')
  const isAuthenticated = !!token
  const isAdmin = user?.role === 1

  if (to.meta.requiresAuth && !isAuthenticated) {
    return '/login'
  }

  if (to.meta.requiresGuest && isAuthenticated) {
    return '/'
  }

  if (to.meta.requiresAdmin && !isAdmin) {
    return '/'
  }

  if (to.meta.requiresUser && isAdmin) {
    return '/admin'
  }
})

export default router
