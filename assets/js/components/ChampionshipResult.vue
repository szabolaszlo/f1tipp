<template>
  <div>
    <Transition name="fade" mode="out-in">
      <div class="panel panel-default">
        <div class="panel-heading text-center">
          <strong>{{ data.event.name ? data.event.name + ' - ' : '' }}{{
              $t("championshipResult.title").toUpperCase()
            }}</strong>
        </div>
        <div v-if="errorMessage">
          <div class="text-center" style="padding: 30px;">
            <strong class="text-center color-one">{{ $t('general.errorOnComponentLoad') }}</strong>
            <hr/>
            <strong class="text-center color-two">{{ errorMessage }}</strong>
          </div>
        </div>
        <div v-else-if="!loading">
          <div class="panel-body center-block">
            <div class="table-responsive table-striped center-block">
              <table class="table table-striped">
                <thead>
                <tr>
                  <th></th>
                  <th>{{ $t('championshipResult.driver') }}</th>
                  <th>{{ $t('championshipResult.point') }}</th>
                  <th>{{ $t('championshipResult.wins') }}</th>
                </tr>
                </thead>
                <tbody>
                <template v-for="driver in data.driverStandings">
                  <tr>
                    <td></td>
                    <td><strong>{{ driver.Driver.givenName }} {{ driver.Driver.familyName }}</strong></td>
                    <td><strong class="color-one">{{ driver.points }}</strong></td>
                    <td><strong class="color-two">{{ driver.wins }}</strong></td>
                  </tr>
                </template>
                <tr><td></td></tr>
                <tr><td></td></tr>
                </tbody>
              </table>
              <table class="table table-striped">
                <thead>
                <tr>
                  <th></th>
                  <th>{{ $t('championshipResult.construct') }}</th>
                  <th>{{ $t('championshipResult.point') }}</th>
                  <th>{{ $t('championshipResult.wins') }}</th>
                </tr>
                </thead>
                <tbody>
                <template v-for="construct in this.data.constructStandings">
                  <tr>
                    <td></td>
                    <td><strong>{{ construct.Constructor.name }}</strong></td>
                    <td><strong class="color-one">{{ construct.points }}</strong></td>
                    <td><strong class="color-two">{{ construct.wins }}</strong></td>
                  </tr>
                </template>
                </tbody>
              </table>
            </div>
          </div>
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
  name: "ChampionshipResult",
  components: {PulseLoader},
  data() {
    return {
      errorMessage: '',
      onlyRemaining: true,
      loading: true,
      data: {
        event: {
          name: null
        }
      }
    }
  },
  mounted() {
    if (!('IntersectionObserver' in window)) {
      this.loadResult()
      return;
    }
    const observer = new IntersectionObserver((entries) => {
      if (entries[0].intersectionRatio <= 0) return;
      observer.unobserve(this.$el);
      this.loadResult()
    });
    observer.observe(this.$el);
  },
  methods: {
    loadResult() {
      axios
          .get('/module/championship_result')
          .then(response => {
            this.data = response.data;
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
