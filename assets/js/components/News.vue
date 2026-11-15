<template>
  <div class="f1-board f1-news">
    <div class="f1-board__head">
      <span class="f1-board__tag">{{ $t("news.title") }}</span>
      <span class="f1-board__title">Motorsport.com</span>
      <a class="f1-board__meta" href="https://hu.motorsport.com/f1/" target="_blank" rel="noopener">{{ $t("news.source") }}</a>
    </div>
    <div v-if="errorMessage" class="f1-board__note">{{ $t('general.errorOnComponentLoad') }} – {{ errorMessage }}</div>
    <div v-else-if="loading" class="f1-board__body text-center"><pulse-loader color="#e10600"></pulse-loader></div>
    <article v-else v-for="(feed, index) in feeds" :key="index" class="f1-news__item">
      <img class="f1-news__img" :src="feed.image" alt="" loading="lazy">
      <div class="f1-news__text">
        <span class="f1-muted">{{ new Date(feed.publishDate).toLocaleString() }}</span>
        <h3 class="f1-news__title">{{ feed.title }}</h3>
        <div class="f1-news__desc" v-html="feed.description"></div>
      </div>
    </article>
  </div>
</template>

<script>
import axios from "axios";
import PulseLoader from 'vue-spinner/src/PulseLoader.vue';
import colorTheme from "../classes/colorTheme/ColorTheme";

export default {
  name: "News",
  components: {PulseLoader},
  data() {
    return {
      errorMessage: '',
      onlyRemaining: true,
      loading: true,
      feeds: null,
      colorTheme: colorTheme
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
