<template>
  <div ref="board" class="f1-board f1-betting">
    <div class="f1-board__head">
      <span class="f1-board__tag">TIPP</span>
      <span class="f1-board__title">
        {{ $t('betting.title') }}<template v-if="activeEvent"> · {{ activeEvent.name }}</template>
      </span>
    </div>

    <div v-if="errorMessage" class="f1-board__body text-center">
      <strong class="color-two">{{ errorMessage }}</strong>
    </div>
    <div v-else-if="loading" class="f1-board__body text-center">
      <pulse-loader color="#e10600"></pulse-loader>
    </div>
    <template v-else>
      <div class="f1-betting__tabs" role="tablist">
        <button v-for="event in weekendEvents" :key="event.id" type="button" role="tab"
                class="f1-betting__tab" :class="{'is-active': activeEvent && event.id === activeEvent.id}"
                :aria-selected="activeEvent && event.id === activeEvent.id ? 'true' : 'false'"
                @click="selectEvent(event)">
          <span :class="'glyphicon glyphicon-' + getGlyphiconName(event.type)" aria-hidden="true"></span>
          {{ $t('general.' + event.type) }}
        </button>
      </div>

      <!-- tabváltáskor ugyanaz a becsukódó/kinyíló animáció, mint a menüs oldalváltásnál (MainComponent "fade") -->
      <Transition name="fade" mode="out-in">
      <div v-if="formErrorMessage" key="error" class="f1-board__body text-center">
        <strong class="color-two">{{ formErrorMessage }}</strong>
      </div>
      <div v-else-if="!form" key="loading" class="f1-board__body text-center">
        <pulse-loader color="#e10600"></pulse-loader>
      </div>
      <form v-else :key="'form-' + formEventId" class="f1-betting__form" novalidate @submit.prevent="submit">
        <div v-if="form.success" class="f1-betting__alert f1-betting__alert--ok" role="status">
          <div class="f1-betting__success">
            <span>{{ form.success }}</span>
            <button v-if="redirecting" type="button" class="f1-betting__stay" @click="cancelRedirect">
              {{ $t('betting.stay') }}
            </button>
          </div>
          <template v-if="redirecting">
            <div class="f1-betting__redirect">{{ $t('betting.redirecting') }}</div>
            <div class="f1-betting__progress" aria-hidden="true">
              <span :style="{animationDuration: redirectDelay + 'ms'}"></span>
            </div>
          </template>
        </div>
        <div v-for="error in form.errors" :key="error" class="f1-betting__alert f1-betting__alert--error" role="alert">
          {{ error }}
        </div>
        <div v-if="isLocked" class="f1-betting__note">{{ $t('betting.locked') }}</div>

        <div v-for="field in form.fields" :key="field.name" class="f1-betting__row"
             :class="{'f1-betting__row--question': field.type !== 'driver'}">
          <template v-if="field.type === 'driver'">
            <div class="f1-pos" :class="positionClass(field)" :title="field.label">{{ positionText(field) }}</div>
            <DriverSelect v-model="values[field.name]"
                          :drivers="form.drivers"
                          :taken-by="takenFor(field)"
                          :disabled="field.disabled || processing"
                          :placeholder="$t('betting.placeholder')"
                          :search-placeholder="$t('betting.search')"
                          :no-result-text="$t('betting.noResult')"
                          :taken-title="$t('betting.alreadyTipped')"
                          :aria-label="field.label"/>
          </template>
          <template v-else>
            <div class="f1-betting__question">{{ field.label }}</div>
            <div class="f1-betting__choices" role="radiogroup" :aria-label="field.label">
              <button v-for="choice in form.questionChoices" :key="choice.value" type="button" role="radio"
                      class="f1-betting__choice" :class="{'is-active': values[field.name] === choice.value}"
                      :aria-checked="values[field.name] === choice.value ? 'true' : 'false'"
                      :disabled="field.disabled || processing"
                      @click="values[field.name] = choice.value">
                {{ choice.label }}
              </button>
            </div>
          </template>
        </div>

        <div v-if="form.submitLabel" class="f1-betting__footer">
          <span v-if="missingCount" class="f1-betting__hint">{{ $t('betting.missing', {count: missingCount}) }}</span>
          <button type="submit" class="btn f1-betting__submit" :disabled="!canSubmit">
            <pulse-loader v-if="processing" color="#fff" size="6px"></pulse-loader>
            <template v-else>{{ form.submitLabel }}</template>
          </button>
        </div>
      </form>
      </Transition>
    </template>
  </div>
</template>

<script>
import axios from "axios"
import PulseLoader from 'vue-spinner/src/PulseLoader.vue'
import DriverSelect from "./DriverSelect.vue"

export default {
  name: "Betting",
  components: {PulseLoader, DriverSelect},
  data() {
    return {
      loading: true,
      // a formlekérések sorszáma: egy elavult (közben felülírt) válasz ne írja vissza a formot
      formRequestId: 0,
      processing: false,
      errorMessage: null,
      formErrorMessage: null,
      weekendEvents: [],
      activeEvent: null,
      // a szerver által leírt form (BettingFormPresenter)
      form: null,
      // melyik eseményhez tartozik a megjelenített form (tabváltás közben még az előzőé)
      formEventId: null,
      // mezőnév -> kiválasztott érték
      values: {},
      // eseményazonosító -> {form, values}: visszaváltáskor azonnal megjelenik, a be nem küldött választásokkal
      formCache: {},
      // sikeres tipp után ennyi idő múlva irányít át az Aktuális oldalra
      redirectDelay: 3000,
      redirectTimer: null,
      redirecting: false
    }
  },
  computed: {
    editableFields() {
      return this.form ? this.form.fields.filter(field => !field.disabled) : [];
    },
    missingCount() {
      return this.editableFields.filter(field => !this.values[field.name]).length;
    },
    isLocked() {
      return !!this.form && !this.form.submitLabel && this.form.fields.length > 0;
    },
    canSubmit() {
      return !this.processing && this.editableFields.length > 0 && this.missingCount === 0;
    }
  },
  activated() {
    document.title = 'F1Tipp - Tippelek'
    gtag('event', 'page_view')
  },
  deactivated() {
    // keep-alive: ha közben elnavigál, ne ugorjon át később, és visszatéréskor ne a régi üzenet fogadja
    this.cancelRedirect();
    if (this.form) {
      this.form.success = null;
    }
  },
  beforeUnmount() {
    this.cancelRedirect();
  },
  mounted() {
    axios
        .get('/module/betting/weekend-events')
        .then(response => {
          this.weekendEvents = response.data;
          this.loading = false;
          if (this.weekendEvents.length) {
            this.selectEvent(this.weekendEvents[0]);
            // a többi tabot előre betöltjük, hogy váltáskor ne kelljen töltésjelző
            this.weekendEvents.slice(1).forEach(event => this.preloadForm(event));
          }
        })
        .catch(error => {
          this.loading = false;
          if (error.response && error.response.status === 401) {
            this.errorMessage = this.$t("betting.needToSignIn")
          } else {
            this.errorMessage = error.message;
          }
        })
  },
  methods: {
    selectEvent(event) {
      this.cancelRedirect();
      this.rememberForm();
      if (this.form) {
        this.form.success = null;
      }
      this.activeEvent = event;
      this.formErrorMessage = null;

      const cached = this.formCache[event.id];
      if (cached) {
        this.form = cached.form;
        this.formEventId = event.id;
        this.values = {...cached.values};
      } else {
        this.form = null;
      }

      // a gyorsítótárazott formot is frissítjük (zárolás, CSRF token), a felhasználó választásait megtartva
      const requestId = ++this.formRequestId;
      axios
          .get('/module/betting/form/' + event.id)
          .then(response => {
            if (requestId === this.formRequestId) {
              const current = this.formCache[event.id];
              this.applyForm(response.data, this.form && current ? this.changedValues(current.form) : {});
            }
          })
          .catch(error => {
            if (requestId === this.formRequestId && !this.form) {
              this.formErrorMessage = error.message;
            }
          })
    },
    preloadForm(event) {
      axios
          .get('/module/betting/form/' + event.id)
          .then(response => {
            if (!this.formCache[event.id] && this.formEventId !== event.id) {
              const values = {};
              response.data.fields.forEach(field => {
                values[field.name] = field.value || '';
              });
              this.formCache[event.id] = {form: response.data, values};
            }
          })
          .catch(() => {
            // nem baj: tabváltáskor újra lekérjük
          })
    },
    applyForm(form, keep = {}) {
      this.form = form;
      this.formEventId = this.activeEvent ? this.activeEvent.id : null;
      const values = {};
      form.fields.forEach(field => {
        values[field.name] = !field.disabled && field.name in keep ? keep[field.name] : (field.value || '');
      });
      this.values = values;
    },
    rememberForm() {
      if (this.form && this.formEventId !== null) {
        this.formCache[this.formEventId] = {form: this.form, values: {...this.values}};
      }
    },
    // a felhasználó által a szerver állapotához képest módosított értékek
    changedValues(form) {
      const changed = {};
      form.fields.forEach(field => {
        if (this.values[field.name] !== (field.value || '')) {
          changed[field.name] = this.values[field.name];
        }
      });
      return changed;
    },
    submit() {
      if (!this.canSubmit) {
        return;
      }
      const payload = new URLSearchParams();
      this.form.hidden.forEach(field => payload.append(field.name, field.value));
      this.editableFields.forEach(field => payload.append(field.name, this.values[field.name]));

      this.processing = true;
      this.formRequestId++;
      axios
          .post(this.form.action, payload)
          .then(response => {
            this.applyForm(response.data);
            if (response.data.success) {
              this.startRedirect();
            }
          })
          .catch(error => {
            if (error.response && error.response.status === 422 && error.response.data.fields) {
              this.applyForm(error.response.data);
            } else {
              this.formErrorMessage = error.message;
            }
          })
          .finally(() => {
            this.processing = false;
            this.$refs.board.scrollIntoView({behavior: 'smooth', block: 'start'});
          })
    },
    startRedirect() {
      this.cancelRedirect();
      this.redirecting = true;
      this.redirectTimer = setTimeout(() => {
        this.redirectTimer = null;
        this.redirecting = false;
        this.$router.push('/actual');
      }, this.redirectDelay);
    },
    cancelRedirect() {
      clearTimeout(this.redirectTimer);
      this.redirectTimer = null;
      this.redirecting = false;
    },
    // a többi helyezésre már kiválasztott pilóták: kód -> "P3"
    takenFor(current) {
      const taken = {};
      this.form.fields.forEach(field => {
        const value = this.values[field.name];
        if (field !== current && field.type === 'driver' && value) {
          taken[value] = this.positionLabel(field);
        }
      });
      return taken;
    },
    positionNumber(field) {
      const match = /^p(\d+)$/.exec(field.key);
      return match ? parseInt(match[1], 10) : null;
    },
    positionText(field) {
      const number = this.positionNumber(field);
      return number !== null ? number : field.label;
    },
    positionLabel(field) {
      const number = this.positionNumber(field);
      return number !== null ? 'P' + number : field.label;
    },
    positionClass(field) {
      const number = this.positionNumber(field);
      return number !== null && number <= 3 ? 'f1-pos--p' + number : '';
    },
    getGlyphiconName(type) {
      if (type === 'qualify') {
        return 'time'
      }
      if (type === 'race') {
        return 'flag'
      }
      if (type === 'sprint_qualify') {
        return 'flash'
      }
    }
  }
}
</script>
