<template>
  <div>
    <Transition name="fade" mode="out-in">
      <div v-if="errorMessage">
        <div class="panel panel-default">
          <div class="panel-heading text-center">
            <strong>{{ $t("actual.title").toUpperCase() }}</strong>
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
  name: "Actual",
  components: {PulseLoader},
  data() {
    return {
      loading: true,
      errorMessage: null,
      renderedModule: null,
      colorTheme: colorTheme
    }
  },
  mounted() {
    document.title = 'F1Tipp - Aktuális'
    gtag('event', 'page_view')
    axios
        .get('/module/actual')
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
</script>
