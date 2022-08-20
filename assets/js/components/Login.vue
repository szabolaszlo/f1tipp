<template>
  <div v-html="renderedLoginModule"></div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      renderedLoginModule: null
    }
  },
  mounted() {
    axios
        .get('/login')
        .then(response => {
          this.renderedLoginModule = response.data;
        })
        .catch(error => {
          this.loading = false;
          this.errorMessage = error.message;
        })
  }
}
</script>
