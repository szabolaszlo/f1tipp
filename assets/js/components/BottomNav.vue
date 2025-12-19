<template>
  <nav class="bottom-nav">
    <!-- Skewed active indicator background -->
    <div class="nav-track"></div>
    
    <router-link 
      v-for="item in navItems" 
      :key="item.path"
      :to="item.path" 
      class="nav-item"
      :class="{ active: isActive(item.path) }"
    >
      <div class="nav-content">
        <span class="nav-icon" v-html="item.icon"></span>
        <span class="nav-label">{{ item.label }}</span>
      </div>
      <!-- Active indicator line -->
      <div class="active-line"></div>
    </router-link>
  </nav>
</template>

<script>
export default {
  name: 'BottomNav',
  data() {
    return {
      navItems: [
        { 
          path: '/betting', 
          label: 'Tipp', 
          icon: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>'
        },
        { 
          path: '/actual', 
          label: 'Race', 
          icon: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>'
        },
        { 
          path: '/standings', 
          label: 'Rank', 
          icon: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/></svg>'
        },
        { 
          path: '/calendar', 
          label: 'Cal', 
          icon: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>'
        },
        { 
          path: '/profile', 
          label: 'Prof', 
          icon: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>'
        },
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
.bottom-nav {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  height: var(--bottom-nav-height);
  background: var(--bg-secondary);
  border-top: 2px solid var(--accent-red); /* F1 Red Top Border */
  display: flex;
  align-items: center;
  justify-content: space-around;
  padding-bottom: var(--safe-area-bottom);
  z-index: 1000;
  backdrop-filter: blur(10px);
}

.nav-item {
  flex: 1;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: var(--text-secondary);
  text-decoration: none;
  transition: all var(--transition-fast);
  position: relative;
  overflow: hidden;
}

.nav-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  position: relative;
  z-index: 2;
  /* Unskew text if we skew the item */
}

.nav-item.active {
  color: #fff;
  background: linear-gradient(to top, rgba(255, 24, 1, 0.2), transparent);
}

.nav-icon svg {
  width: 20px;
  height: 20px;
}

.nav-label {
  font-size: 10px;
  font-weight: var(--font-weight-bold);
  text-transform: uppercase;
  letter-spacing: 1px;
}

.active-line {
  position: absolute;
  bottom: 0px;
  left: 50%;
  transform: translateX(-50%) skew(-15deg);
  width: 20px; /* Short line */
  height: 4px;
  background: var(--accent-red);
  opacity: 0;
  transition: opacity var(--transition-fast);
}

.nav-item.active .active-line {
  opacity: 1;
}

/* Hide on desktop */
@media (min-width: 768px) {
  .bottom-nav {
    display: none;
  }
}
</style>
