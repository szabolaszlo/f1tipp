<template>
        <span v-if="renderedModule" v-html="renderedModule"> </span>
</template>

<script>
import axios from "axios";
import {Skeleton} from "vue-loading-skeleton";

export default {
  components: {Skeleton},
  data() {
    return {
      renderedModule: ''
    }
  },
  beforeCreate() {
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