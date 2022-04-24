<template>
    <div id="f1tipp-calendar2"><span v-html="renderedTrophiesModule"> </span></div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      renderedTrophiesModule: null
    }
  },
  mounted() {
    axios
        .get('/module/trophies')
        .then(response => {
          this.renderedTrophiesModule = response.data;
        })
        .catch(error => {
          this.loading = false;
          this.errorMessage = error.message;
        })
  }
}
</script>