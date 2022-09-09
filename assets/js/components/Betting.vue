<template>
  <div>
    <Transition name="fade" mode="out-in" @after-enter="afterFormLoaded">
      <div v-if="errorMessage">
        <div class="panel panel-default">
          <div class="panel-heading text-center">
            <strong>{{ $t("betting.title").toUpperCase() }}</strong>
          </div>
          <div class="text-center" style="padding: 30px;">
            <strong class="text-center color-two">{{ errorMessage }}</strong>
          </div>
        </div>
      </div>
      <div v-else-if="!loading">
        <div class="form form-inline">
          <div class="row text-center">
            <div v-for="event in weekendEvents" class="col-sm form-control-static" style="padding: 15px;">
              <a class="btn btn-new" role="button"
                 @click="getForm(event.id); eventName = event.name; eventType = event.type;">
                <span :class="'glyphicon glyphicon-' + getGlyphiconName(event.type)" aria-hidden="false"></span>
                {{ $t('general.' + event.type).toUpperCase() }}
              </a>
            </div>
            <div>
              <Transition name="fade" mode="out-in" @after-enter="afterFormLoaded">
                <div v-if="formErrorMessage">
                  <div class="text-center" style="padding: 30px;">
                    <strong class="text-center color-two">{{ formErrorMessage }}</strong>
                  </div>
                </div>
                <div v-else-if="!formLoading">
                  <div class="panel panel-default">
                    <div class="panel-heading text-center">
                      <strong>{{ $t("betting.title").toUpperCase() }} - {{ eventName }} -
                        {{ $t('general.' + eventType).toUpperCase() }}</strong>
                    </div>
                    <div v-if="processingForm" class="text-center">
                      <pulse-loader color="#B8211DE5"></pulse-loader>
                    </div>
                    <div id="betting-form-closure" v-html="form"></div>
                  </div>
                </div>
                <div v-else class="text-center">
                  <pulse-loader color="#B8211DE5"></pulse-loader>
                </div>
              </Transition>
            </div>
          </div>
        </div>
      </div>
      <div v-else class="text-center">
        <pulse-loader color="#B8211DE5"></pulse-loader>
      </div>
    </Transition>
  </div>
</template>

<script>
import axios from "axios"
import PulseLoader from 'vue-spinner/src/PulseLoader.vue'
import "select2/dist/js/select2.full.min"
import JQuery from 'jquery'
import DriverImages from "../classes/driver-images/driver-images"

window.$ = JQuery

export default {
  name: "Betting",
  components: {PulseLoader},
  data() {
    return {
      eventName: null,
      eventType: null,
      loading: true,
      formLoading: true,
      processingForm: false,
      errorMessage: null,
      formErrorMessage: null,
      weekendEvents: null,
      form: null
    }
  },
  activated() {
    document.title = 'F1Tipp - Tippelek'
    gtag('event', 'page_view')
  },
  mounted() {
    axios
        .get('/module/betting/weekend-events')
        .then(response => {
          this.weekendEvents = response.data;
          this.loading = false;
          this.getForm(this.weekendEvents[0].id)
          this.eventName = this.weekendEvents[0].name;
          this.eventType = this.weekendEvents[0].type;
        })
        .catch(error => {
          this.loading = false;
          if (error.response.status === 401) {
            this.errorMessage = this.$t("betting.needToSignIn")
          } else {
            this.errorMessage = error.message;
          }
        })

  },
  methods: {
    afterFormLoaded() {

      var vueInstance = this

      $('#betting_form_submit').prop('disabled', true);

      window.afterFormLoaded = function () {
        $(document).on('select2:open', () => {
          document.querySelector('.select2-search__field').focus();
        });

        $('#betting_form_submit').click(function () {
          $('#betting_form_submit').prop('disabled', true);
          window.scrollTo(0, 0);
          const $form = $('#betting-form');
          vueInstance.processingForm = true;
          $.ajax({
            url: $form.prop('action'),
            method: $form.prop('method'),
            data: $form.serialize(),
          }).done(function (html) {
            $("#betting-form-closure").html(html);
            vueInstance.processingForm = false;
            afterFormLoaded();
          })
        });

        $('.select2').select2({
          placeholder: "Tippelj, Mákolj! Tippelj, Mákolj!",
          containerCssClass: "custom-select2",
          theme: "bootstrap",
          dropdownAutoWidth: true,
          templateResult: window.formatSelectDropDown
        });

        $('.select2').on('select2:select', function (e) {
          window.selectedValues = []

          $('select.select2').each(function () {
            $(this).parent().css('background-color', 'transparent');
            window.selectedValues.push($(this).val());
          });

          let error = false;
          let empty = false;

          $('select.select2').each(function () {
            let counter = 0

            if ($(this).val() === '') {
              empty = true
            }

            for (let value of window.selectedValues) {
              if (value === $(this).val() && value !== '') {
                counter++
              }
            }

            if (counter > 1) {
              error = true;
              $(this).parent().css('background-color', 'rgba(255, 25, 25, .5)');
            }
          });

          if (error || empty) {
            $('#betting_form_submit').prop('disabled', true);
          } else {
            $('#betting_form_submit').prop('disabled', false);
          }
        });
      }

      window.selectedValues = []

      window.formatSelectDropDown = function (item) {
        if (!item.id || window.selectedValues.includes(item.id)) {
          return null;
        }
        if (DriverImages[item.id.toLowerCase()]) {
          var $state = $(
              '<span><img height="70px" src="' + DriverImages[item.id.toLowerCase()] + '" class="img-flag" /> ' + item.text + '</span>'
          );
        } else {
          var $state = $(
              '<span>' + item.text + '</span>'
          );
        }
        return $state;
      };

      afterFormLoaded();
    },
    getForm(eventId) {
      this.form = null;
      this.formLoading = true;
      axios
          .get('/module/betting/form/' + eventId)
          .then(response => {
            this.form = response.data;
            this.formLoading = false;
          })
          .catch(error => {
            this.formLoading = false;
            this.formErrorMessage = error.message
          })
    },
    getGlyphiconName(type) {
      if (type === 'qualify') {
        return 'time'
      }
      if (type === 'race') {
        return 'flag'
      }
      if (type === 'sprint_qualify') {
        return 'flash'
      }
    }
  }
}
</script>
<style>
@import 'select2/dist/css/select2.min.css';
@import 'select2-bootstrap-theme/dist/select2-bootstrap.min.css';
@import "../../../assets/css/bet.css";
</style>
