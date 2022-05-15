<template>
  <div>
    <Transition name="fade" mode="out-in">
      <div v-if="errorMessage">
        <div class="panel panel-default">
          <div class="panel-heading text-center">
            <strong>NAPTÁR</strong>
          </div>
          <strong class="text-center color-one">Hiba történt a Naptár betöltésekor, próbáld később...</strong>
        </div>
      </div>
      <div v-else-if="!loading">
        <div class="panel panel-default">
          <div class="panel-heading text-center">
            <strong>NAPTÁR</strong>
          </div>
          <div style="text-align: center; margin-top: 15px;">
            Itt a gombok
            <div class="btn btn-new" role="button" @click="onlyRemaining = !onlyRemaining; filterEvents();">
              <input type="checkbox" id="only-remaining" v-model="onlyRemaining">
              Csak Hátralévők
            </div>
          </div>
          <table class="table table-responsive table-striped">
            <thead>
            <tr>
              <th><strong class="color-two">1</strong></th>
              <th><strong class="color-two">2</strong></th>
              <th><strong class="color-two">3</strong></th>
            </tr>
            </thead>
            <tbody>

            <tr v-for="event in filteredEvents">
              <td>
                <strong class="color-two">{{ event.name }}</strong>
              </td>
              <td><strong class="color-one">{{ event.type }}</strong></td>
              <td><strong class="color-one">{{ event.dateTime.toDateString("hu-HU") }}</strong></td>
            </tr>

            </tbody>
          </table>
        </div>
      </div>
      <div v-else class="text-center">
        <pulse-loader color="#B8211DE5"></pulse-loader>
      </div>
    </Transition>
  </div>
</template>

<script>
import axios from "axios";
import PulseLoader from 'vue-spinner/src/PulseLoader.vue';
import EventType from "../classes/calendar/CalendarEventType";

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
      errorMessage: '',
      onlyRemaining: true,
      loading: true,
      events: [],
      filteredEvents: []
    }
  },
  created() {
    axios
        .get('/api/events')
        .then(response => {
          this.events = response.data['hydra:member'];
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
        event.dateTime = new Date(event.dateTime);
        if ((onlyRemaining && event.dateTime > new Date()) || !onlyRemaining) {
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