<template>
  <div class="page actual-page">
    <!-- Header Card -->
    <div class="card mb-lg">
      <div class="card-header">
        <h2 class="card-title">🏁 Aktuális Hétvége</h2>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loader">
      <div class="spinner"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="errorMessage" class="card">
      <div class="card-body text-center">
        <p class="text-error mb-md">{{ $t('general.errorOnComponentLoad') }}</p>
        <p class="text-secondary">{{ errorMessage }}</p>
        <button @click="fetchData" class="btn btn-primary" style="margin-top: var(--space-md);">
          Újrapróbálás
        </button>
      </div>
    </div>

    <!-- Content - Still using v-html as this comes from server -->
    <div v-else class="actual-content">
      <div class="legacy-content" v-html="renderedModule"></div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'Actual',
  data() {
    return {
      loading: true,
      errorMessage: null,
      renderedModule: null,
    };
  },
  mounted() {
    document.title = 'F1 Tipp - Aktuális';
    this.fetchData();
  },
  activated() {
    document.title = 'F1 Tipp - Aktuális';
    gtag('event', 'page_view');
  },
  methods: {
    async fetchData() {
      this.loading = true;
      this.errorMessage = null;
      try {
        const response = await axios.get('/module/actual');
        this.renderedModule = response.data;
      } catch (error) {
        this.errorMessage = error.message;
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

<style scoped>
.actual-content {
  /* Container for legacy HTML content */
}

/* Override legacy styles to match dark theme */
.legacy-content :deep(.panel) {
  background: var(--bg-card);
  border: none;
  border-radius: var(--radius-lg);
  margin-bottom: var(--space-md);
  overflow: hidden;
}

.legacy-content :deep(.panel-heading) {
  background: var(--bg-secondary);
  border-bottom: 1px solid var(--border-light);
  color: var(--text-primary);
  padding: var(--space-md) var(--space-lg);
}

.legacy-content :deep(.panel-body) {
  padding: var(--space-lg);
}

.legacy-content :deep(.table) {
  color: var(--text-primary);
  margin-bottom: 0;
}

.legacy-content :deep(.table th),
.legacy-content :deep(.table td) {
  border-color: var(--border-light);
  padding: var(--space-md);
}

.legacy-content :deep(.table-striped > tbody > tr:nth-of-type(odd)) {
  background: var(--bg-secondary);
}

.legacy-content :deep(.table-striped > tbody > tr:nth-of-type(even)) {
  background: var(--bg-card);
}

.legacy-content :deep(.color-one) {
  color: var(--accent-red) !important;
}

.legacy-content :deep(.color-two) {
  color: var(--text-secondary) !important;
}

.legacy-content :deep(.btn) {
  background: var(--accent-red);
  border: none;
  color: var(--text-primary);
  border-radius: var(--radius-md);
}

.legacy-content :deep(.btn:hover) {
  background: var(--accent-red-hover);
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
  to {
    transform: rotate(360deg);
  }
}
</style>
