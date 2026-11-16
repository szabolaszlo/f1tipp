<template>
  <div>
    <Transition name="fade" mode="out-in">
      <div v-if="errorMessage" class="f1-board">
        <div class="f1-board__head">
          <span class="f1-board__tag">{{ $t("actual.title") }}</span>
          <span class="f1-board__title">{{ $t('general.errorOnComponentLoad') }}</span>
        </div>
        <div class="f1-board__note">{{ errorMessage }}</div>
      </div>
      <div v-else-if="!loading">
        <div v-html="renderedModule"></div>
      </div>
      <div v-else class="text-center">
        <pulse-loader color="#e10600"></pulse-loader>
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
