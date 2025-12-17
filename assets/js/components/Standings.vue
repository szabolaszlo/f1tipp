<template>
  <div class="page standings-page">
    <div class="card mb-lg">
      <div class="card-header">
        <h2 class="card-title">🏆 Ranglista</h2>
      </div>
      
      <div v-if="loading" class="loader">
        <div class="spinner"></div>
      </div>
      
      <div v-else-if="error" class="card-body text-center">
        <p class="text-error">{{ error }}</p>
      </div>
      
      <div v-else class="standings-list">
        <div 
          v-for="(user, index) in users" 
          :key="user.id" 
          class="standing-item"
        >
          <div class="rank" :class="getRankClass(index)">
            {{ index + 1 }}
          </div>
          <div class="user-info">
            <span class="user-name">{{ user.name }}</span>
          </div>
          <div class="points-section">
            <span class="points">{{ user.pointSummary }}</span>
            <span 
              class="diff" 
              :class="getDiffClass(user.pointDifference)"
            >
              {{ formatDiff(user.pointDifference) }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Records Card -->
    <div v-if="!loading && records" class="card">
      <div class="card-header">
        <h2 class="card-title">📊 Rekordok</h2>
      </div>
      <div class="card-body">
        <div class="records-grid">
          <div class="record-item">
            <span class="record-label">Legjobb kvalifikációs tipp</span>
            <span class="record-value text-gold">{{ records.qualifyBets }}</span>
          </div>
          <div class="record-item">
            <span class="record-label">Legjobb versenytipp</span>
            <span class="record-value text-gold">{{ records.raceBets }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'Standings',
  data() {
    return {
      loading: true,
      error: null,
      users: [],
      records: null,
      maths: null,
    };
  },
  mounted() {
    document.title = 'F1 Tipp - Ranglista';
    this.fetchData();
  },
  activated() {
    document.title = 'F1 Tipp - Ranglista';
    gtag('event', 'page_view');
  },
  methods: {
    async fetchData() {
      try {
        const response = await axios.get('/api/user-championship');
        this.users = response.data.users;
        this.records = response.data.records;
        this.maths = response.data.maths;
      } catch (err) {
        this.error = err.message;
      } finally {
        this.loading = false;
      }
    },
    getRankClass(index) {
      if (index === 0) return 'rank-1';
      if (index === 1) return 'rank-2';
      if (index === 2) return 'rank-3';
      return '';
    },
    getDiffClass(diff) {
      if (diff > 0) return 'text-success';
      if (diff < 0) return 'text-error';
      return 'text-muted';
    },
    formatDiff(diff) {
      if (diff === null || diff === undefined) return '-';
      if (diff > 0) return `+${diff}`;
      return String(diff);
    },
  },
};
</script>

<style scoped>
.standings-list {
  padding: 0;
}

.standing-item {
  display: flex;
  align-items: center;
  gap: var(--space-md);
  padding: var(--space-md) var(--space-lg);
  border-bottom: 1px solid var(--border-light);
  transition: background var(--transition-fast);
}

.standing-item:last-child {
  border-bottom: none;
}

.standing-item:hover {
  background: var(--bg-card-hover);
}

.user-info {
  flex: 1;
  min-width: 0;
}

.user-name {
  font-weight: var(--font-weight-semibold);
  color: var(--text-primary);
}

.points-section {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 2px;
}

.points {
  font-size: var(--font-size-lg);
  font-weight: var(--font-weight-bold);
  color: var(--text-primary);
}

.diff {
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-medium);
}

.records-grid {
  display: grid;
  gap: var(--space-md);
}

.record-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: var(--space-sm) 0;
  border-bottom: 1px solid var(--border-light);
}

.record-item:last-child {
  border-bottom: none;
}

.record-label {
  color: var(--text-secondary);
  font-size: var(--font-size-sm);
}

.record-value {
  font-weight: var(--font-weight-semibold);
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid var(--border-color);
  border-top-color: var(--accent-red);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
