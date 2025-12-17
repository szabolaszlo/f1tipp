<template>
  <div class="page calendar-page">
    <!-- Filters Card -->
    <div class="card mb-lg">
      <div class="card-header">
        <h2 class="card-title">🗓️ Naptár</h2>
      </div>
      <div class="filters">
        <button 
          class="filter-btn"
          :class="{ active: onlyRemaining }"
          @click="onlyRemaining = !onlyRemaining; filterEvents();"
        >
          <span class="filter-icon">📅</span>
          Csak jövőbeli
        </button>
        <button 
          v-for="eventType in eventTypes" 
          :key="eventType.id"
          class="filter-btn"
          :class="{ active: eventType.state }"
          @click="eventType.state = !eventType.state; filterEvents();"
        >
          <span class="filter-icon">{{ getEventIcon(eventType.id) }}</span>
          {{ getEventLabel(eventType.id) }}
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loader">
      <div class="spinner"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="errorMessage" class="card">
      <div class="card-body text-center">
        <p class="text-error">{{ errorMessage }}</p>
      </div>
    </div>

    <!-- Events List -->
    <div v-else class="events-list">
      <div 
        v-for="event in filteredEvents" 
        :key="event.id" 
        class="event-card"
        :class="getEventClass(event.type)"
      >
        <div class="event-type-badge" :class="'badge-' + event.type">
          {{ getEventIcon(event.type) }}
        </div>
        <div class="event-info">
          <span class="event-name">{{ event.name }}</span>
          <span class="event-type-label">{{ getEventLabel(event.type) }}</span>
        </div>
        <div class="event-date">
          <span class="date">{{ formatDate(event.dateTime) }}</span>
          <span class="time">{{ formatTime(event.dateTime) }}</span>
        </div>
      </div>

      <div v-if="filteredEvents.length === 0" class="card">
        <div class="card-body text-center text-secondary">
          Nincs megjeleníthető esemény.
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'Calendar',
  data() {
    return {
      eventTypes: [
        { id: 'qualify', state: true },
        { id: 'race', state: true },
        { id: 'sprint_qualify', state: true },
      ],
      errorMessage: '',
      onlyRemaining: true,
      loading: true,
      events: [],
      filteredEvents: [],
    };
  },
  mounted() {
    document.title = 'F1 Tipp - Naptár';
    this.fetchEvents();
  },
  activated() {
    document.title = 'F1 Tipp - Naptár';
    gtag('event', 'page_view');
  },
  watch: {
    events() {
      this.filterEvents();
    },
  },
  methods: {
    async fetchEvents() {
      try {
        const response = await axios.get('/api/events');
        this.events = response.data;
      } catch (error) {
        this.errorMessage = error.message;
      } finally {
        this.loading = false;
      }
    },
    filterEvents() {
      const now = new Date();
      this.filteredEvents = this.events.filter((event) => {
        const eventDate = new Date(event.dateTime);
        const dateCheck = !this.onlyRemaining || eventDate > now;
        const typeCheck = this.eventTypes.find(
          (t) => t.id === event.type && t.state
        );
        return dateCheck && typeCheck;
      });
    },
    getEventIcon(type) {
      const icons = {
        qualify: '🏁',
        race: '🏎️',
        sprint_qualify: '⚡',
      };
      return icons[type] || '📅';
    },
    getEventLabel(type) {
      const labels = {
        qualify: 'Időmérő',
        race: 'Futam',
        sprint_qualify: 'Sprint',
      };
      return labels[type] || type;
    },
    getEventClass(type) {
      return `event-${type}`;
    },
    formatDate(dateTime) {
      const date = new Date(dateTime);
      return date.toLocaleDateString('hu-HU', {
        month: 'short',
        day: 'numeric',
        weekday: 'short',
      });
    },
    formatTime(dateTime) {
      const date = new Date(dateTime);
      return date.toLocaleTimeString('hu-HU', {
        hour: '2-digit',
        minute: '2-digit',
      });
    },
  },
};
</script>

<style scoped>
.filters {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-sm);
  padding: var(--space-md);
  border-top: 1px solid var(--border-light);
}

.filter-btn {
  display: flex;
  align-items: center;
  gap: var(--space-xs);
  padding: var(--space-sm) var(--space-md);
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-full);
  color: var(--text-secondary);
  font-size: var(--font-size-sm);
  cursor: pointer;
  transition: all var(--transition-fast);
}

.filter-btn:hover {
  background: var(--bg-card-hover);
  color: var(--text-primary);
}

.filter-btn.active {
  background: var(--accent-red);
  border-color: var(--accent-red);
  color: var(--text-primary);
}

.filter-icon {
  font-size: 1rem;
}

.events-list {
  display: flex;
  flex-direction: column;
  gap: var(--space-sm);
}

.event-card {
  display: flex;
  align-items: center;
  gap: var(--space-md);
  padding: var(--space-md) var(--space-lg);
  background: var(--bg-card);
  border-radius: var(--radius-lg);
  border-left: 4px solid var(--border-color);
  transition: all var(--transition-fast);
}

.event-card:hover {
  background: var(--bg-card-hover);
  transform: translateX(4px);
}

.event-card.event-race {
  border-left-color: var(--accent-red);
}

.event-card.event-qualify {
  border-left-color: var(--accent-gold);
}

.event-card.event-sprint_qualify {
  border-left-color: #8b5cf6;
}

.event-type-badge {
  font-size: 1.5rem;
  width: 40px;
  text-align: center;
}

.event-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 0;
}

.event-name {
  font-weight: var(--font-weight-semibold);
  color: var(--text-primary);
}

.event-type-label {
  font-size: var(--font-size-xs);
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.event-date {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 2px;
}

.date {
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-medium);
  color: var(--text-primary);
}

.time {
  font-size: var(--font-size-xs);
  color: var(--text-secondary);
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
