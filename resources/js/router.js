import { createRouter, createWebHistory } from 'vue-router';
import Login from './Pages/Login.vue';
import DashboardPage from './Pages/DashboardPage.vue';
import TicketsPage from './Pages/TicketsPage.vue';

const routes = [
  { path: '/login', name: 'Login', component: Login },
  { path: '/dashboard', name: 'Dashboard', component:  DashboardPage },
  { path: '/tickets', name: 'Tickets', component: TicketsPage },
  { path: '/:pathMatch(.*)*', redirect: '/login' }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token');
  
  if (to.path !== '/login' && !token) {
    next('/login');
  } else if (to.path === '/login' && token) {
    next('/dashboard');
  } else {
    next();
  }
});

export default router;
