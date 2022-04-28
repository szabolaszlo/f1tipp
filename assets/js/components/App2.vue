<template>
  <div>
    <Transition name="fade" mode="out-in">
      <div v-if="!loading">
        <div id="f1tipp-calendar2"><span v-html="renderedTrophiesModule"> </span></div>
      </div>
      <div v-else class="text-center">
        <pulse-loader color="#B8211DE5"></pulse-loader>
      </div>
    </Transition>
  </div>
</template>

<script>
import axios from "axios";
import PulseLoader from 'vue-spinner/src/PulseLoader.vue'

export default {
  name: "Calendar",
  components: {PulseLoader},
  data() {
    return {
      loading: true,
      renderedTrophiesModule: null
    }
  },
  mounted() {
    axios
        .get('/calendar')
        .then(response => {
          this.renderedTrophiesModule = response.data;
          this.loading = false;
        })
        .catch(error => {
          this.errorMessage = error.message;
        })
  }
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: all 0.5s;
  max-height: 500px;
}

.fade-enter-from,
.fade-leave-to {
  transition: all 0.5s;
  opacity: 0;
  max-height: 30px;
}
</style>