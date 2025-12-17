<template>
  <div class="reload-able-module">
    <div v-if="loading" class="text-center" style="padding: 20px;">
      <pulse-loader :color="colorTheme.f1TippColor5"></pulse-loader>
    </div>
    <div v-else-if="error" class="panel panel-default">
      <div class="panel-heading text-center">
        <strong>{{ $t('trophies.title').toUpperCase() }}</strong>
      </div>
      <div class="text-center" style="padding: 20px;">
        <span class="color-two">{{ error }}</span>
      </div>
    </div>
    <div v-else class="panel panel-default">
      <div class="panel-heading text-center">
        <strong>{{ $t('trophies.title').toUpperCase() }}</strong>
        <span v-if="event" class="color-two"> - {{ event.name }}</span>
      </div>
      <div class="panel-body">
        <table class="table table-striped">
          <tbody>
            <tr v-for="type in ['gold', 'silver', 'bronze']" :key="type">
              <td style="width: 30%;">
                <img 
                  :src="getTrophyImage(type)" 
                  :alt="type" 
                  style="height: 40px;"
                >
              </td>
              <td>
                <template v-if="podiumTrophies[type]">
                  <span 
                    v-for="trophy in podiumTrophies[type]" 
                    :key="trophy.id"
                    class="color-one"
                    style="margin-right: 8px;"
                  >
                    {{ trophy.user.name }}
                  </span>
                </template>
                <span v-else class="color-two">-</span>
              </td>
            </tr>
          </tbody>
        </table>
        <div class="text-center">
          <router-link to="/trophies" class="btn btn-new">
            {{ $t('general.details') }}
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import PulseLoader from 'vue-spinner/src/PulseLoader.vue';
import colorTheme from '../../classes/colorTheme/ColorTheme';

export default {
  name: 'TrophiesWidget',
  components: { PulseLoader },
  data() {
    return {
      loading: true,
      error: null,
      podiumTrophies: {},
      event: null,
      colorTheme,
    };
  },
  mounted() {
    this.fetchTrophies();
  },
  methods: {
    async fetchTrophies() {
      try {
        const response = await axios.get('/api/trophies');
        this.podiumTrophies = response.data.podiumTrophies;
        this.event = response.data.event;
      } catch (err) {
        this.error = err.message;
      } finally {
        this.loading = false;
      }
    },
    getTrophyImage(type) {
      const images = {
        gold: require('@images/trophy/gold.png'),
        silver: require('@images/trophy/silver.png'),
        bronze: require('@images/trophy/bronze.png'),
      };
      return images[type];
    },
  },
};
</script>
