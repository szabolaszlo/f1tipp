<template>
  <div>
    <Transition name="fade" mode="out-in">
      <div v-if="errorMessage">
        <div class="panel panel-default">
          <div class="panel-heading text-center">
            <strong>{{ $t("rules.title").toUpperCase() }}</strong>
          </div>
          <div class="text-center" style="padding: 30px;">
            <strong class="text-center color-one">{{ $t('general.errorOnComponentLoad') }}</strong>
            <hr/>
            <strong class="text-center color-two">{{ errorMessage }}</strong>
          </div>
        </div>
      </div>
      <div v-else-if="!loading">
        <div class="panel panel-default">
          <div class="panel-heading text-center">
            <strong>{{ rules.title.toUpperCase() }}</strong>
          </div>
          <div style="padding: 20px;" v-html="rules.content"></div>
        </div>
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
  name: "Rules",
  components: {PulseLoader},
  data() {
    return {
      rules: null,
      errorMessage: '',
      loading: true,
      colorTheme: colorTheme
    }
  },
  activated() {
    document.title = 'F1Tipp - Szabályzat'
    gtag('event', 'page_view')
  },
  created() {
    axios
        .get('/api/information/23')
        .then(response => {
          this.rules = response.data;
          this.loading = false;
        })
        .catch(error => {
          this.loading = false;
          this.errorMessage = error.message;
        })
  }
}
</script>
