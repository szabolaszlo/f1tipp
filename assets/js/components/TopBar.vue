<template>
  <header class="top-bar">
    <div class="top-bar-content">
      <!-- Logo -->
      <router-link to="/" class="logo">
        <img src="@images/logo.png" alt="F1 Tipp" class="logo-img" />
      </router-link>

      <!-- Desktop Navigation (hidden on mobile) -->
      <nav class="desktop-nav">
        <router-link 
          v-for="item in navItems" 
          :key="item.path"
          :to="item.path" 
          class="nav-link"
          :class="{ active: isActive(item.path) }"
        >
          {{ item.label }}
        </router-link>
      </nav>

      <!-- User Section -->
      <div class="user-section">
        <template v-if="authStore.state.loading">
          <div class="avatar-skeleton"></div>
        </template>
        <template v-else-if="authStore.state.isAuthenticated">
          <span class="username">{{ authStore.state.user }}</span>
          <a href="/logout" class="btn-logout" title="Kijelentkezés">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
              <polyline points="16 17 21 12 16 7"/>
              <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
          </a>
        </template>
        <template v-else>
          <router-link to="/login" class="btn btn-primary btn-sm">
            Belépés
          </router-link>
        </template>
      </div>
    </div>
  </header>
</template>

<script>
import { useAuthStore } from '../stores/auth';

export default {
  name: 'TopBar',
  setup() {
    const authStore = useAuthStore();
    return { authStore };
  },
  data() {
    return {
      navItems: [
        { path: '/betting', label: 'Tippelés' },
        { path: '/actual', label: 'Aktuális' },
        { path: '/standings', label: 'Ranglista' },
        { path: '/calendar', label: 'Naptár' },
      ]
    };
  },
  methods: {
    isActive(path) {
      return this.$route.path === path || this.$route.path.startsWith(path + '/');
    }
  }
};
</script>

<style scoped>
.top-bar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  height: var(--nav-height);
  background: var(--bg-secondary);
  border-bottom: 1px solid var(--border-light);
  z-index: 1000;
}

.top-bar-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 100%;
  padding: 0 var(--space-md);
  max-width: 1200px;
  margin: 0 auto;
}

.logo {
  display: flex;
  align-items: center;
}

.logo-img {
  height: 36px;
  width: auto;
}

/* Desktop Navigation */
.desktop-nav {
  display: none;
  gap: var(--space-lg);
}

@media (min-width: 768px) {
  .desktop-nav {
    display: flex;
  }
}

.nav-link {
  color: var(--text-secondary);
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-medium);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: var(--space-sm) 0;
  border-bottom: 2px solid transparent;
  transition: all var(--transition-fast);
}

.nav-link:hover {
  color: var(--text-primary);
}

.nav-link.active {
  color: var(--accent-red);
  border-bottom-color: var(--accent-red);
}

/* User Section */
.user-section {
  display: flex;
  align-items: center;
  gap: var(--space-md);
}

.username {
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-medium);
  color: var(--text-primary);
  display: none;
}

@media (min-width: 768px) {
  .username {
    display: block;
  }
}

.btn-logout {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  color: var(--text-secondary);
  border-radius: var(--radius-full);
  transition: all var(--transition-fast);
}

.btn-logout:hover {
  background: var(--bg-card);
  color: var(--accent-red);
}

.btn-sm {
  padding: var(--space-xs) var(--space-md);
  font-size: var(--font-size-xs);
}

.avatar-skeleton {
  width: 36px;
  height: 36px;
  border-radius: var(--radius-full);
  background: var(--bg-card);
  animation: pulse 1.5s infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}
</style>
