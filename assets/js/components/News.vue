<template>
  <div class="f1-board f1-news">
    <div class="f1-board__head">
      <span class="f1-board__tag">{{ $t("news.title") }}</span>
      <span class="f1-board__title">F1VILÁG.HU</span>
      <a class="f1-board__meta" href="https://f1vilag.hu/" target="_blank" rel="noopener">{{ $t("news.source") }}</a>
    </div>
    <div v-if="errorMessage" class="f1-board__note">{{ $t('general.errorOnComponentLoad') }} – {{ errorMessage }}</div>
    <div v-else-if="loading" class="f1-board__body text-center"><pulse-loader color="#e10600"></pulse-loader></div>
    <article v-else v-for="(feed, index) in feeds" :key="index" class="f1-news__item">
      <a v-if="feed.image" class="f1-news__img-link" :href="feed.link" target="_blank" rel="noopener" tabindex="-1" aria-hidden="true">
        <img class="f1-news__img" :src="feed.image" alt="" loading="lazy">
      </a>
      <div v-else class="f1-news__img" aria-hidden="true"></div>
      <div class="f1-news__text">
        <div class="f1-news__meta">
          <span class="f1-muted">{{ new Date(feed.publishDate).toLocaleString() }}</span>
          <a class="f1-news__more" :href="feed.link" target="_blank" rel="noopener">{{ $t("news.readMore") }} ›</a>
        </div>
        <h3 class="f1-news__title"><a :href="feed.link" target="_blank" rel="noopener">{{ feed.title }}</a></h3>
        <div class="f1-news__desc" v-html="feed.description"></div>
      </div>
    </article>
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
