<template>
  <div>
    <div v-if="errorMessage" class="f1-board">
      <div class="f1-board__head">
        <span class="f1-board__tag">F1</span>
        <span class="f1-board__title">{{ $t("championshipResult.title") }}</span>
      </div>
      <div class="f1-board__note">{{ $t('general.errorOnComponentLoad') }} – {{ errorMessage }}</div>
    </div>
    <div v-else-if="loading" class="f1-board">
      <div class="f1-board__head">
        <span class="f1-board__tag">F1</span>
        <span class="f1-board__title">{{ $t("championshipResult.title") }}</span>
      </div>
      <div class="f1-board__body text-center"><pulse-loader color="#e10600"></pulse-loader></div>
    </div>
    <template v-else>
      <div class="f1-board">
        <div class="f1-board__head">
          <span class="f1-board__tag">F1</span>
          <span class="f1-board__title">Pilóta világbajnokság</span>
        </div>
        <div class="f1-board__colhead">
          <span>{{ $t('championshipResult.driver') }}</span>
          <span class="f1-nums">
            <span class="f1-num">{{ $t('championshipResult.wins') }}</span>
            <span class="f1-num">{{ $t('championshipResult.point') }}</span>
          </span>
        </div>
        <div v-for="(driver, index) in data.driverStandings" :key="'d' + index" class="f1-row">
          <div class="f1-pos" :class="{'f1-pos--leader': Number(driver.position || index + 1) === 1}">{{ driver.position || index + 1 }}</div>
          <div class="f1-stripe" :style="{background: driver.teamColor}"></div>
          <div class="f1-name">{{ driver.Driver.familyName }}<small>{{ driver.Driver.givenName }}</small></div>
          <div class="f1-nums">
            <span class="f1-num f1-num--dim">{{ driver.wins }}</span>
            <span class="f1-num f1-num--main">{{ driver.points }}</span>
          </div>
        </div>
      </div>
      <div class="f1-board">
        <div class="f1-board__head">
          <span class="f1-board__tag">F1</span>
          <span class="f1-board__title">Konstruktőri VB</span>
        </div>
        <div class="f1-board__colhead">
          <span>{{ $t('championshipResult.construct') }}</span>
          <span class="f1-nums"><span class="f1-num">{{ $t('championshipResult.point') }}</span></span>
        </div>
        <div v-for="(construct, index) in data.constructStandings" :key="'c' + index" class="f1-row">
          <div class="f1-pos" :class="{'f1-pos--leader': Number(construct.position || index + 1) === 1}">{{ construct.position || index + 1 }}</div>
          <div class="f1-stripe" :style="{background: construct.teamColor}"></div>
          <div class="f1-name">{{ construct.Constructor.name }}</div>
          <div class="f1-nums"><span class="f1-num f1-num--main">{{ construct.points }}</span></div>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import axios from "axios";
import PulseLoader from 'vue-spinner/src/PulseLoader.vue';
import visibleInterval from '../classes/refresh/visibleInterval';

const REFRESH_INTERVAL_MS = 60 * 1000;

export default {
  name: "ChampionshipResult",
  components: {PulseLoader},
  data() {
    return {
      errorMessage: '',
      loading: true,
      data: {
        event: {
          name: null
        }
      }
    }
  },
  mounted() {
    if (!('IntersectionObserver' in window)) {
      this.loadResult()
      return;
    }
    const observer = new IntersectionObserver((entries) => {
      if (entries[0].intersectionRatio <= 0) return;
      observer.unobserve(this.$el);
      this.loadResult()
    });
    observer.observe(this.$el);
  },
  beforeUnmount() {
    if (this.stopRefresh) {
      this.stopRefresh();
    }
  },
  methods: {
    loadResult() {
      axios
          .get('/module/championship_result')
          .then(response => {
            this.data = response.data;
            this.errorMessage = '';
            this.loading = false;
            // csak sikeres első betöltés után frissítünk: üres szerver-cache-nél minden kérés
            // RapidAPI-hívást indítana, azt nem akarjuk percenként, kliensenként
            if (!this.stopRefresh) {
              this.stopRefresh = visibleInterval(() => this.loadResult(), REFRESH_INTERVAL_MS);
            }
          })
          .catch(error => {
            this.loading = false;
            // háttérfrissítés hibájánál marad a korábbi tabella
            if (!this.stopRefresh) {
              this.errorMessage = error.message;
            }
          })
    }
  }
}
</script>
