<template>
  <div>
    <Transition name="fade" mode="out-in">
      <div v-if="!loading">
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
  name: "Actual",
  components: {PulseLoader},
  data() {
    return {
      loading: true,
      renderedModule: null,
    }
  },
  mounted() {
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
