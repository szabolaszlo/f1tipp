<template>
  <div>
    <Transition name="fade" mode="out-in">
      <div v-if="errorMessage">
        <div class="panel panel-default">
          <div class="panel-heading text-center">
            <strong>{{ $t("calendar.title").toUpperCase() }}</strong>
          </div>
          <div class="text-center" style="padding: 30px;">
            <strong class="text-center color-one">{{ $t('general.errorOnComponentLoad') }}</strong>
            <hr/>
            <strong class="text-center color-two">{{ errorMessage }}</strong>
          </div>
        </div>
      </div>
      <div v-else-if="!loading">
        <div class="panel panel-default">
          <div class="panel-heading text-center">
            <strong>{{ $t("calendar.title").toUpperCase() }}</strong>
          </div>
          <div class="form form-inline">
            <div class="row text-center">
              <div class="col-sm form-control-static" style="padding: 5px;">
                <a class="btn btn-new" role="button" @click="onlyRemaining = !onlyRemaining; filterEvents();">
                  <input type="checkbox" v-model="onlyRemaining">
                  {{ $t('calendar.onlyRemaining') }}
                </a>
              </div>
              <div v-for="eventType in eventTypes" class="col-sm form-control-static" style="padding: 5px;">
                <a class="btn btn-new" role="button" @click="eventType.state = !eventType.state; filterEvents();">
                  <input type="checkbox" v-model="eventType.state">
                  {{ $t('general.' + eventType.translatedName) }}
                </a>
              </div>
            </div>
          </div>
          <table class="table table-responsive table-striped">
            <tbody>

            <tr v-for="event in filteredEvents">
              <td></td>
              <td>
                <strong class="color-two">{{ event.name }}</strong>
              </td>
              <td><strong class="color-one">{{ $t('general.' + event.type) }}</strong></td>
              <td><strong class="color-one">{{
                  event.dateTime.replace('T', '  -  ').substring(0, 20)
                }}</strong></td>
              <td></td>
            </tr>

            </tbody>
          </table>
        </div>
      </div>
      <div v-else class="text-center">
        <pulse-loader :color="colorTheme.f1TippColor5"></pulse-loader>
      </div>
    </Transition>
  </div>
</template>

<script>
import axios from "axios";
import PulseLoader from 'vue-spinner/src/PulseLoader.vue';
import EventType from "../classes/calendar/CalendarEventType";
import colorTheme from "../classes/colorTheme/ColorTheme";

export default {
  name: "Calendar",
  components: {PulseLoader},
  data() {
    return {
      eventTypes: [
        new EventType('qualify', 'qualify', true),
        new EventType('race', 'race', true),
        new EventType('sprint_qualify', 'sprintQualify', true)
      ],
      buttons: [
        'qualify',
        'race',
        'sprint_qualify',
        'onlyRemaining'
      ],
      errorMessage: '',
      onlyRemaining: true,
      loading: true,
      events: [],
      filteredEvents: [],
      colorTheme: colorTheme
    }
  },
  activated() {
    document.title = 'F1Tipp - Naptár'
    gtag('event', 'page_view')
  },
  created() {
    axios
        .get('/api/events')
        .then(response => {
          this.events = response.data;
          this.loading = false;
        })
        .catch(error => {
          this.loading = false;
          this.errorMessage = error.message;
        })
  },
  watch: {
    events: function () {
      this.filteredEvents = this.events;
      this.filterEvents();
    }
  },
  methods: {
    filterEvents: function () {
      let events = this.events;
      let eventTypes = this.eventTypes;
      let onlyRemaining = this.onlyRemaining;
      let filteredEvents = [];
      events.forEach(function (event) {
        let eventDateTime = new Date(event.dateTime);
        if ((onlyRemaining && eventDateTime > new Date()) || !onlyRemaining) {
          eventTypes.forEach(function (eventType) {
            if (event.type === eventType.id && eventType.state) {
              filteredEvents.push(event)
            }
          })
        }
      });
      this.filteredEvents = filteredEvents;
    }
  }
}
</script>
