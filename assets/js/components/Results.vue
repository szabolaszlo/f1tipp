<template>
  <div>
    <div style="text-align: center; padding: 15px;">
      <button v-for="year in years" class="btn btn-new" role="button" style="margin: 5px;" @click="yearChosen(year)">
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
        <pulse-loader color="#B8211DE5"></pulse-loader>
      </div>
    </Transition>
  </div>
</template>

<script>
import axios from "axios";
import PulseLoader from 'vue-spinner/src/PulseLoader.vue';

export default {
  name: 'Results',
  components: {PulseLoader},
  data() {
    return {
      loading: true,
      errorMessage: null,
      renderedModule: null,
      currentYear: new Date().getFullYear(),
      years: [],
    }
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
      let url =
          year === this.currentYear
              ? '/module/results'
              : '/information_content/' + year
      axios
          .get(url)
          .then(response => {
            this.renderedModule = response.data;
            this.loading = false;
          })
          .catch(error => {
            this.loading = false;
            this.errorMessage = error.message;
          })
    }
  }
}
</script>
