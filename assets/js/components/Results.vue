<template>
  <div>
    <div style="text-align: center; padding: 15px;">
      <button v-for="year in years" :key="year" class="btn btn-new" role="button" style="margin: 5px;" @click="yearChosen(year)">
        {{ year }}
      </button>
    </div>
    <Transition name="fade" mode="out-in">
      <div v-if="errorMessage">
        <div class="panel panel-default">
          <div class="panel-heading text-center">
            <strong>{{ $t("results.title").toUpperCase() }}</strong>
          </div>
          <div class="text-center" style="padding: 30px;">
            <strong class="text-center color-one">{{ $t('general.errorOnComponentLoad') }}</strong>
            <hr/>
            <strong class="text-center color-two">{{ errorMessage }}</strong>
          </div>
        </div>
      </div>
      <div v-else-if="!loading">
        <div v-html="renderedModule"></div>
      </div>
      <div v-else class="text-center">
        <pulse-loader :color="colorTheme.f1TippColor5"></pulse-loader>
      </div>
    </Transition>
  </div>
</template>

<script>
import axios from "axios";
import PulseLoader from 'vue-spinner/src/PulseLoader.vue';
import colorTheme from "../classes/colorTheme/ColorTheme";

export default {
  name: 'Results',
  components: {PulseLoader},
  data() {
    return {
      loading: true,
      errorMessage: null,
      renderedModule: null,
      requestId: 0,
      currentYear: new Date().getFullYear(),
      years: [],
      colorTheme: colorTheme
    }
  },
  activated() {
    document.title = 'F1Tipp - Eredmények'
    gtag('event', 'page_view')
  },
  created() {
    let year = 2014
    do {
      this.years.push(year)
      year++
    } while (year <= this.currentYear)
  },
  mounted() {
    this.yearChosen(this.currentYear)
  },
  methods: {
    yearChosen(year) {
      this.loading = true
      // egy korábbi hiba ne ragadjon be: a következő évválasztás újra próbálkozhasson
      this.errorMessage = null
      // gyors kattintgatásnál csak az utoljára választott év válasza jelenhet meg
      const requestId = ++this.requestId
      let url =
          year === this.currentYear
              ? '/module/results'
              : '/information_content/' + year
      axios
          .get(url)
          .then(response => {
            if (requestId !== this.requestId) {
              return;
            }
            this.renderedModule = response.data;
            this.loading = false;
          })
          .catch(error => {
            if (requestId !== this.requestId) {
              return;
            }
            this.loading = false;
            this.errorMessage = error.message;
          })
    }
  }
}
</script>
