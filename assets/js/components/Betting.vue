<template>
  <div class="page betting-page">
    <!-- Header Card -->
    <div class="card mb-lg">
      <div class="card-header">
        <h2 class="card-title">🎯 Tippelés</h2>
      </div>
      
      <!-- Event Tabs -->
      <div v-if="!loading && weekendEvents" class="event-tabs">
        <button 
          v-for="event in weekendEvents" 
          :key="event.id"
          class="event-tab"
          :class="{ active: selectedEventId === event.id }"
          @click="selectEvent(event)"
        >
          <span class="event-icon">{{ getEventIcon(event.type) }}</span>
          <span class="event-label">{{ getEventLabel(event.type) }}</span>
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loader">
      <div class="spinner"></div>
    </div>

    <!-- Error State (not logged in) -->
    <div v-else-if="errorMessage" class="card">
      <div class="card-body text-center">
        <div class="login-prompt">
          <span class="login-icon">🔐</span>
          <p class="text-secondary mb-md">{{ errorMessage }}</p>
          <router-link to="/profile" class="btn btn-primary">
            Bejelentkezés
          </router-link>
        </div>
      </div>
    </div>

    <!-- Betting Form -->
    <div v-else class="betting-content">
      <!-- Form Loading -->
      <div v-if="formLoading" class="loader">
        <div class="spinner"></div>
      </div>

      <!-- Form Error -->
      <div v-else-if="formErrorMessage" class="card">
        <div class="card-body text-center">
          <p class="text-error">{{ formErrorMessage }}</p>
        </div>
      </div>

      <!-- Form Content -->
      <div v-else class="card">
        <div class="card-header">
          <h3 class="card-title">
            {{ eventName }} - {{ getEventLabel(eventType) }}
          </h3>
        </div>
        <div class="card-body">
          <div v-if="processingForm" class="loader">
            <div class="spinner"></div>
          </div>
          <div id="betting-form-closure" class="betting-form" v-html="form"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import 'select2/dist/js/select2.full.min';
import JQuery from 'jquery';
import DriverImages from '../classes/driver-images/driver-images';

window.$ = JQuery;

export default {
  name: 'Betting',
  data() {
    return {
      loading: true,
      formLoading: true,
      processingForm: false,
      errorMessage: null,
      formErrorMessage: null,
      weekendEvents: null,
      selectedEventId: null,
      eventName: null,
      eventType: null,
      form: null,
    };
  },
  mounted() {
    document.title = 'F1 Tipp - Tippelés';
    this.fetchWeekendEvents();
  },
  activated() {
    document.title = 'F1 Tipp - Tippelés';
    gtag('event', 'page_view');
  },
  methods: {
    async fetchWeekendEvents() {
      try {
        const response = await axios.get('/module/betting/weekend-events');
        this.weekendEvents = response.data;
        if (this.weekendEvents.length > 0) {
          this.selectEvent(this.weekendEvents[0]);
        }
      } catch (error) {
        if (error.response?.status === 401) {
          this.errorMessage = this.$t('betting.needToSignIn');
        } else {
          this.errorMessage = error.message;
        }
      } finally {
        this.loading = false;
      }
    },
    selectEvent(event) {
      this.selectedEventId = event.id;
      this.eventName = event.name;
      this.eventType = event.type;
      this.getForm(event.id);
    },
    async getForm(eventId) {
      this.form = null;
      this.formLoading = true;
      this.formErrorMessage = null;
      try {
        const response = await axios.get(`/module/betting/form/${eventId}`);
        this.form = response.data;
        this.$nextTick(() => this.initForm());
      } catch (error) {
        this.formErrorMessage = error.message;
      } finally {
        this.formLoading = false;
      }
    },
    initForm() {
      const vueInstance = this;

      $('#betting_form_submit').prop('disabled', true);

      window.selectedValues = [];

      window.formatSelectDropDown = function (item) {
        if (!item.id || window.selectedValues.includes(item.id)) {
          return null;
        }
        if (DriverImages[item.id.toLowerCase()]) {
          return $(
            `<span><img height="50px" src="${DriverImages[item.id.toLowerCase()]}" class="img-flag" /> ${item.text}</span>`
          );
        }
        return $(`<span>${item.text}</span>`);
      };

      $('.select2').select2({
        placeholder: 'Válassz pilótát...',
        containerCssClass: 'custom-select2',
        theme: 'bootstrap',
        dropdownAutoWidth: true,
        templateResult: window.formatSelectDropDown,
      });

      $('.select2').on('select2:select', function () {
        window.selectedValues = [];
        let error = false;
        let empty = false;

        $('select.select2').each(function () {
          $(this).parent().css('background-color', 'transparent');
          window.selectedValues.push($(this).val());
        });

        $('select.select2').each(function () {
          let counter = 0;
          if ($(this).val() === '') {
            empty = true;
          }
          for (let value of window.selectedValues) {
            if (value === $(this).val() && value !== '') {
              counter++;
            }
          }
          if (counter > 1) {
            error = true;
            $(this).parent().css('background-color', 'rgba(255, 25, 25, .3)');
          }
        });

        $('#betting_form_submit').prop('disabled', error || empty);
      });

      $('#betting_form_submit').click(function () {
        $(this).prop('disabled', true);
        window.scrollTo(0, 0);
        vueInstance.processingForm = true;
        const $form = $('#betting-form');
        $.ajax({
          url: $form.prop('action'),
          method: $form.prop('method'),
          data: $form.serialize(),
        }).done(function (html) {
          $('#betting-form-closure').html(html);
          vueInstance.processingForm = false;
          vueInstance.initForm();
        });
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
  },
};
</script>

<style>
@import 'select2/dist/css/select2.min.css';
</style>

<style scoped>
.event-tabs {
  display: flex;
  gap: var(--space-sm);
  padding: var(--space-md);
  border-top: 1px solid var(--border-light);
  overflow-x: auto;
}

.event-tab {
  display: flex;
  align-items: center;
  gap: var(--space-sm);
  padding: var(--space-sm) var(--space-lg);
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-full);
  color: var(--text-secondary);
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-medium);
  cursor: pointer;
  transition: all var(--transition-fast);
  white-space: nowrap;
}

.event-tab:hover {
  background: var(--bg-card-hover);
  color: var(--text-primary);
}

.event-tab.active {
  background: var(--accent-red);
  border-color: var(--accent-red);
  color: var(--text-primary);
}

.event-icon {
  font-size: 1.2rem;
}

.login-prompt {
  padding: var(--space-xl);
}

.login-icon {
  font-size: 3rem;
  display: block;
  margin-bottom: var(--space-md);
}

/* Betting form overrides */
.betting-form :deep(.form-group) {
  margin-bottom: var(--space-md);
}

.betting-form :deep(label) {
  color: var(--text-secondary);
  font-size: var(--font-size-sm);
  margin-bottom: var(--space-xs);
  display: block;
}

.betting-form :deep(.select2-container) {
  width: 100% !important;
}

.betting-form :deep(.select2-selection) {
  background: var(--bg-input) !important;
  border: 1px solid var(--border-color) !important;
  border-radius: var(--radius-md) !important;
  min-height: 44px !important;
  color: var(--text-primary) !important;
}

.betting-form :deep(.select2-selection__rendered) {
  color: var(--text-primary) !important;
  line-height: 42px !important;
  padding-left: var(--space-md) !important;
}

.betting-form :deep(.select2-selection__arrow) {
  height: 42px !important;
}

.betting-form :deep(.select2-dropdown) {
  background: var(--bg-card) !important;
  border-color: var(--border-color) !important;
}

.betting-form :deep(.select2-results__option) {
  color: var(--text-primary) !important;
  padding: var(--space-md) !important;
}

.betting-form :deep(.select2-results__option--highlighted) {
  background: var(--accent-red) !important;
}

.betting-form :deep(.btn-success),
.betting-form :deep(.btn-primary) {
  background: var(--accent-red) !important;
  border: none !important;
  border-radius: var(--radius-md) !important;
  padding: var(--space-md) var(--space-xl) !important;
  font-weight: var(--font-weight-semibold) !important;
  text-transform: uppercase !important;
  letter-spacing: 0.5px !important;
  margin-top: var(--space-lg) !important;
  width: 100% !important;
}

.betting-form :deep(.btn-success:hover),
.betting-form :deep(.btn-primary:hover) {
  background: var(--accent-red-hover) !important;
}

.betting-form :deep(.alert-success) {
  background: var(--success) !important;
  border: none !important;
  border-radius: var(--radius-md) !important;
  color: white !important;
}

.betting-form :deep(.alert-danger) {
  background: var(--error) !important;
  border: none !important;
  border-radius: var(--radius-md) !important;
  color: white !important;
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
