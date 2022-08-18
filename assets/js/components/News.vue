<template>
  <div>
    <Transition name="fade" mode="out-in">
      <div class="panel panel-default">
        <div class="panel-heading text-center">
          <div class="pull-left">
            <a class="feed_source small" href="https://hu.motorsport.com/f1/"
               target="_blank">{{ $t("news.source") }}</a>
          </div>
          <strong>{{ $t("news.title").toUpperCase() }}</strong>
        </div>
        <div v-if="errorMessage">
          <div class="text-center" style="padding: 30px;">
            <strong class="text-center color-one">{{ $t('general.errorOnComponentLoad') }}</strong>
            <hr/>
            <strong class="text-center color-two">{{ errorMessage }}</strong>
          </div>
        </div>
        <div v-else-if="!loading">
          <table class="table table-striped">
            <tbody>
            <template v-for="feed in feeds">
              <tr>
                <td class="visible-lg visible-md visible-sm"
                    style="vertical-align: middle;width: 20%;text-align: center">
                  <img height="130" :src="feed.image" style="padding: 5px">
                  <div>
                    <span class="color-two small">{{ new Date(feed.publishDate).toLocaleString() }}</span>
                  </div>
                </td>
                <td>
                  <div class="text-center"><strong class="color-one">{{ feed.title }}</strong></div>
                  <div class="visible-xs text-center"><img class="img-responsive" :src="feed.image"
                                                           style="padding: 5px">
                    <span class="color-two small">{{ new Date(feed.publishDate).toLocaleString() }}</span>
                  </div>
                  <div class="text-justify" style="padding: 5px">
                    <strong v-html="feed.description"></strong>
                  </div>
                </td>
              </tr>
            </template>
            </tbody>
          </table>
        </div>
        <div v-else class="text-center">
          <pulse-loader color="#B8211DE5"></pulse-loader>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script>
import axios from "axios";
import PulseLoader from 'vue-spinner/src/PulseLoader.vue';

export default {
  name: "News",
  components: {PulseLoader},
  data() {
    return {
      errorMessage: '',
      onlyRemaining: true,
      loading: true,
      feeds: null
    }
  },
  mounted() {
    if (!('IntersectionObserver' in window)) {
      this.loadFeeds()
      return;
    }
    const observer = new IntersectionObserver((entries) => {
      if (entries[0].intersectionRatio <= 0) return;
      observer.unobserve(this.$el);
      this.loadFeeds()
    });
    observer.observe(this.$el);
  },
  methods: {
    loadFeeds() {
      axios
          .get('/api/feeds')
          .then(response => {
            this.feeds = response.data;
            this.loading = false;
          })
          .catch(error => {
            this.loading = false;
            this.errorMessage = error.message;
          })
    }
  }
}
</script>
