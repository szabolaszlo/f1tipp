<template>
  <div id="f1tipp-app">
    <Navigation />
    <main class="container-fluid">
      <div class="row">
        <div class="col-1 col-lg-1 col-md-1"></div>
        <div class="col-7 col-lg-7 col-md-7">
          <router-view v-slot="{ Component }">
            <keep-alive>
              <component :is="Component" />
            </keep-alive>
          </router-view>
          <div class="visible-lg visible-md visible-sm">
            <News />
          </div>
        </div>
        <div class="col-3 col-lg-3 col-md-3">
          <TrophiesWidget />
          <UserChampionshipWidget />
          <div class="visible-xs">
            <News />
          </div>
          <ChampionshipResult />
        </div>
        <div class="col-1 col-lg-1 col-md-1"></div>
      </div>
      <hr>
      <footer>
        <div class="row">
          <div class="col-lg-12">
            <p class="color-two">{{ $t('app_creator') }}</p>
          </div>
        </div>
      </footer>
    </main>
  </div>
</template>

<script>
import Navigation from './Navigation.vue';
import News from './News.vue';
import ChampionshipResult from './ChampionshipResult.vue';
import TrophiesWidget from './widgets/TrophiesWidget.vue';
import UserChampionshipWidget from './widgets/UserChampionshipWidget.vue';
import { useAuthStore } from '../stores/auth';

export default {
  name: 'App',
  components: {
    Navigation,
    News,
    ChampionshipResult,
    TrophiesWidget,
    UserChampionshipWidget,
  },
  setup() {
    const authStore = useAuthStore();
    authStore.checkAuth();
    return { authStore };
  },
};
</script>

<style>
body {
  padding-top: 70px;
}
</style>
