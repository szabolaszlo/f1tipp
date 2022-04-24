<template>
  <div>
    <Transition name="slide">
      <span v-if="renderedModule">
        <div v-html="renderedModule">
        </div>
      </span>
    </Transition>
  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      renderedModule: null,
    }
  },
  mounted() {
    axios
        .get('/module/user_championship')
        .then(response => {
          this.renderedModule = response.data;
        })
        .catch(error => {
          this.loading = false;
          this.errorMessage = error.message;
        })
  }
}
</script>