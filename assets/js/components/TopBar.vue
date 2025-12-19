<template>
  <header class="top-bar">
    <div class="top-bar-content">
      <!-- Logo Area -->
      <router-link to="/" class="brand">
        <span class="f1-logo-text">F1</span>
        <span class="tipp-logo-text">TIPP</span>
      </router-link>

      <!-- Desktop Navigation -->
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
          <a href="/logout" class="btn-logout" title="LOGOUT">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
              <polyline points="16 17 21 12 16 7"/>
              <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
          </a>
        </template>
        <template v-else>
          <router-link to="/login" class="btn btn-primary btn-sm login-btn">
            LOGIN
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
        { path: '/betting', label: 'Tipp' },
        { path: '/actual', label: 'Race' },
        { path: '/standings', label: 'Rank' },
        { path: '/calendar', label: 'Cal' },
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
  border-bottom: 2px solid var(--accent-red);
  z-index: 1000;
  backdrop-filter: blur(10px);
}

.top-bar-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 100%;
  padding: 0 var(--space-md);
  max-width: var(--content-max-width);
  margin: 0 auto;
}

@media (min-width: 768px) {
  .top-bar-content {
    max-width: 800px;
    padding: 0 var(--space-lg);
  }
}

.brand {
  display: flex;
  align-items: baseline;
  text-decoration: none;
  transform: skew(-10deg);
}

.f1-logo-text {
  font-family: var(--font-family);
  font-weight: 900;
  font-size: 24px;
  color: var(--accent-red);
  letter-spacing: -2px;
  margin-right: 2px;
  font-style: italic;
}

.tipp-logo-text {
  font-family: var(--font-family);
  font-weight: 900;
  font-size: 24px;
  color: var(--text-primary);
  font-style: italic;
  letter-spacing: -1px;
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
  font-weight: var(--font-weight-bold);
  text-transform: uppercase;
  font-style: italic;
  letter-spacing: 1px;
  padding: var(--space-sm) 0;
  position: relative;
}

.nav-link.active {
  color: #fff;
}

.nav-link.active::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  width: 100%;
  height: 2px;
  background: var(--accent-red);
  transform: skew(-15deg);
}

/* User Section */
.user-section {
  display: flex;
  align-items: center;
  gap: var(--space-md);
}

.username {
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-bold);
  color: var(--text-primary);
  text-transform: uppercase;
  font-style: italic;
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
  border-radius: var(--radius-sm);
  transition: all var(--transition-fast);
}

.btn-logout:hover {
  color: var(--accent-red);
  background: rgba(255, 255, 255, 0.05);
}

.login-btn {
  font-size: 12px;
  padding: 4px 12px;
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
