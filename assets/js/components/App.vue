<template>
  <div class="app">
    <TopBar />
    
    <main class="main-content">
      <router-view v-slot="{ Component }">
        <transition name="page-slide" mode="out-in">
          <keep-alive>
            <component :is="Component" />
          </keep-alive>
        </transition>
      </router-view>
    </main>
    
    <BottomNav />
  </div>
</template>

<script>
import TopBar from './TopBar.vue';
import BottomNav from './BottomNav.vue';
import { useAuthStore } from '../stores/auth';

export default {
  name: 'App',
  components: {
    TopBar,
    BottomNav,
  },
  setup() {
    const authStore = useAuthStore();
    authStore.checkAuth();
    return { authStore };
  },
};
</script>

<style>
/* Import design system */
@import '../../css/design.css';

/* Import Google Font */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
</style>
