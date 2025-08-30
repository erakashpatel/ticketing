<template>
  <div id="app">
    <header v-if="!isLoginPage" class="header">
      <div class="header__logo">Help Desk Portal</div>
      <nav class="header__nav">
        <router-link
          to="/dashboard"
          class="header__nav-link"
          :class="{ 'header__nav-link--active': isActive('/dashboard') }"
        >
          Dashboard
        </router-link>
        <router-link
          to="/tickets"
          class="header__nav-link"
          :class="{ 'header__nav-link--active': isActive('/tickets') }"
        >
          Tickets
        </router-link>
        <button @click="logout" class="header__nav-button">
          Logout
        </button>
      </nav>
    </header>

    <router-view />
  </div>
</template>

<script>
import { computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import '/resources/css/app.css';
export default {
  name: 'App',
  setup() {
    const router = useRouter();
    const route = useRoute();

    const logout = () => {
      localStorage.removeItem('token');
      router.push('/login');
    };

    const isActive = (path) => route.path === path;

    const isLoginPage = computed(() => route.path === '/login');

    return { logout, isActive, isLoginPage };
  },
};
</script>
