<template>
  <div class="reload-able-module">
    <div v-if="loading" class="text-center" style="padding: 20px;">
      <pulse-loader :color="colorTheme.f1TippColor5"></pulse-loader>
    </div>
    <div v-else-if="error" class="panel panel-default">
      <div class="panel-heading text-center">
        <strong>{{ $t('results.championship').toUpperCase() }}</strong>
      </div>
      <div class="text-center" style="padding: 20px;">
        <span class="color-two">{{ error }}</span>
      </div>
    </div>
    <div v-else class="panel panel-default">
      <div class="panel-heading text-center">
        <strong>{{ $t('results.championship').toUpperCase() }}</strong>
      </div>
      <div class="panel-body" style="padding: 0;">
        <table class="table table-striped" style="margin-bottom: 0;">
          <thead>
            <tr>
              <th style="width: 10%;">#</th>
              <th>{{ $t('general.name') }}</th>
              <th style="text-align: right;">{{ $t('general.points') }}</th>
              <th style="text-align: right;">+/-</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(user, index) in users" :key="user.id" class="user-activity" :class="'user-' + user.id">
              <td>{{ index + 1 }}</td>
              <td class="color-one">{{ user.name }}</td>
              <td style="text-align: right;" class="color-two">{{ user.pointSummary }}</td>
              <td style="text-align: right;">
                <span :class="getDifferenceClass(user.pointDifference)">
                  {{ formatDifference(user.pointDifference) }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
        <div class="panel-footer">
          <div v-if="maths.remainingWeekends > 0" class="text-center small color-two">
            {{ $t('general.remainingWeekends') }}: {{ maths.remainingWeekends }}
          </div>
          <div class="text-center" style="margin-top: 10px;">
            <router-link to="/results" class="btn btn-new">
              {{ $t('general.details') }}
            </router-link>
          </div>
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
  name: 'UserChampionshipWidget',
  components: { PulseLoader },
  data() {
    return {
      loading: true,
      error: null,
      users: [],
      records: {},
      maths: { remainingWeekends: 0 },
      colorTheme,
    };
  },
  mounted() {
    this.fetchData();
  },
  methods: {
    async fetchData() {
      try {
        const response = await axios.get('/api/user-championship');
        this.users = response.data.users;
        this.records = response.data.records;
        this.maths = response.data.maths;
      } catch (err) {
        this.error = err.message;
      } finally {
        this.loading = false;
      }
    },
    getDifferenceClass(diff) {
      if (diff > 0) return 'text-success';
      if (diff < 0) return 'text-danger';
      return 'color-two';
    },
    formatDifference(diff) {
      if (diff === null || diff === undefined) return '-';
      if (diff > 0) return `+${diff}`;
      return diff.toString();
    },
  },
};
</script>
