<template>
  <div>
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
  name: "Results",
  components: {PulseLoader},
  data() {
    return {
      loading: true,
      errorMessage: null,
      renderedModule: null,
    }
  },
  mounted() {
    this.$el.addEventListener('ActualYearClicked', this.reload)
    axios
        .get('/module/results')
        .then(response => {
          this.renderedModule = response.data;
          this.loading = false;
        })
        .catch(error => {
          this.loading = false;
          this.errorMessage = error.message;
        })
  },
  methods: {
    reload() {
      this.$router.go(0)
    }
  }
}
</script>
