<template>
  <div class="page standings-page">
    <div class="card mb-lg">
      <div class="card-header">
        <h2 class="card-title">F1 CHAMPIONSHIP LEADERBOARD</h2>
      </div>
      
      <div v-if="loading" class="loader">
        <div class="spinner"></div>
      </div>
      
      <div v-else-if="error" class="card-body text-center">
        <p class="text-error">{{ error }}</p>
      </div>
      
      <div v-else class="f1-table-container">
        <!-- Header Row -->
        <div class="f1-table-header">
          <span class="f1-col-pos">POS</span>
          <span class="f1-col-name">DRIVER</span>
          <span class="f1-col-pts">PTS</span>
          <span class="f1-col-diff">DIFF</span>
        </div>

        <div 
          v-for="(user, index) in users" 
          :key="user.id" 
          class="f1-row"
        >
          <!-- Rank (Colored Box) -->
          <div class="f1-rank" :class="getRankClass(index)">
            {{ index + 1 }}
          </div>
          
          <!-- Content -->
          <div class="f1-row-content">
            <!-- Team Color Strip (Random for now or based on user ID hash) -->
            <div class="f1-team-strip" :style="{ background: getUserColor(user.id) }"></div>
            
            <div class="f1-name">
              {{ user.name }}
            </div>
            
            <div class="f1-data-group">
              <span class="f1-data">{{ user.pointSummary }}</span>
              <span 
                class="f1-diff" 
                :class="getDiffClass(user.pointDifference)"
                v-if="user.pointDifference !== 0"
              >
                {{ formatDiff(user.pointDifference) }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Records Card -->
    <div v-if="!loading && records" class="card">
      <div class="card-header">
        <h2 class="card-title">SEASON RECORDS</h2>
      </div>
      <div class="card-body">
        <div class="records-grid">
          <div class="record-item">
            <span class="record-label">BEST QUALIFIER</span>
            <span class="record-value text-gold">{{ records.qualifyBets }}</span>
          </div>
          <div class="record-item">
            <span class="record-label">BEST RACER</span>
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
      // F1 Team colors for decoration
      teamColors: [
        '#3671C6', // Red Bull
        '#F91536', // Ferrari
        '#6CD3BF', // Mercedes
        '#2293D1', // Alpine
        '#F58020', // McLaren
        '#C92D4B', // Alfa Romeo
        '#B6BABD', // Haas
        '#5E8FAA', // Williams
        '#358C75', // Aston Martin
        '#5A9BD3'  // AlphaTauri
      ]
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
      if (index === 0) return 'f1-rank-1';
      if (index === 1) return 'f1-rank-2';
      if (index === 2) return 'f1-rank-3';
      return '';
    },
    getDiffClass(diff) {
      if (diff > 0) return 'text-success';
      if (diff < 0) return 'text-error';
      return 'text-muted';
    },
    formatDiff(diff) {
      if (diff === null || diff === undefined) return '';
      if (diff > 0) return `+${diff}`;
      return String(diff);
    },
    getUserColor(id) {
      // Deterministic color based on ID
      const index = id % this.teamColors.length;
      return this.teamColors[index];
    }
  },
};
</script>

<style scoped>
.f1-table-header {
  display: flex;
  padding: var(--space-xs) var(--space-md);
  margin-bottom: var(--space-xs);
  color: var(--text-muted);
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-bold);
  text-transform: uppercase;
}

.f1-col-pos { width: 40px; text-align: center; margin-right: var(--space-md); }
.f1-col-name { flex: 1; }
.f1-col-pts { width: 50px; text-align: right; margin-right: var(--space-sm); }
.f1-col-diff { width: 40px; text-align: right; }

.f1-data-group {
  display: flex;
  align-items: center;
  gap: var(--space-md);
}

.f1-diff {
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-bold);
  width: 40px; /* Fixed width for alignment */
  text-align: right;
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
  font-style: italic;
  font-weight: bold;
}

.record-value {
  font-weight: var(--font-weight-bold);
  font-size: var(--font-size-lg);
}
</style>
