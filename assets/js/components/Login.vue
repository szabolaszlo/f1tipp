<template>
  <div>
    <Transition mode="out-in" name="fade">
      <div>
        <div v-if="!componentLoading" class="panel panel-default">
          <div class="panel-heading text-center">
            <strong>{{ $t("login.title").toUpperCase() }}</strong>
          </div>
          <div class="panel-body">
            <div class="row">
              <!-- Kisebb kijelzőn (xs) 12 oszlop,
                   sm-n 8 oszlop középre húzva,
                   md-n 4 oszlop középre húzva -->
              <div class="col-xs-12 col-sm-8 col-sm-offset-2 col-md-4 col-md-offset-4">
                <div class="text-center pt">
                  <strong class="text-red" :style="{ minHeight: '20px', display: 'block' }">
                    {{ errorMessage || ' ' }}
                  </strong>
                </div>
                <form v-if="!isLoggedUser" id="signin" class="pa" @submit.prevent="login">
                  <div class="input-group pa">
                    <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                    <!-- V-model a data() -> username mezőre -->
                    <input
                        id="text"
                        v-model="username"
                        class="form-control"
                        name="username"
                        type="text"
                    >
                  </div>

                  <div class="input-group pa">
                    <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                    <input
                        id="password"
                        v-model="password"
                        class="form-control"
                        name="password"
                        type="password"
                    >
                  </div>

                  <input
                      :value="csrfToken"
                      name="_csrf_token"
                      type="hidden"
                  >

                  <input name="_remember_me" type="hidden" value="1">

                  <div class="row">
                    <div class="col-md-6 col-lg-6 col-sm-6 col-xs-6 col-xl-6">
                    <button id="login-submit" class="btn btn-new ma" type="submit">
                        <strong>
                          {{ $t("login.login").toUpperCase() }}
                        </strong>
                      </button>
                    </div>
                    <div class="col-md-6 col-lg-6 col-sm-6 col-xs-6 col-xl-6 pt">
                      <pulse-loader v-if="loginLoading" :color="colorTheme.f1TippColor5"/>
                    </div>
                  </div>
                  <button class="btn-google ma" type="button" @click="redirectToGoogle">
                    <!-- Az ikon. A szélesség/magasság igény szerint módosítható. -->
                    <img
                        alt="Google logo"
                        class="google-icon"
                        src="https://developers.google.com/identity/images/g-logo.png"
                    />
                    <span class="google-text">
                      <strong>{{ $t("login.loginViaGoogle").toUpperCase() }}</strong>
                    </span>
                  </button>
                </form>
                <div v-if="isLoggedUser" class="text-center pa">
                  <strong>{{ $t("login.greeting") }} {{ getUserName() }}</strong>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div v-if="componentLoading" class="text-center">
          <pulse-loader :color="colorTheme.f1TippColor5"></pulse-loader>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script>
import PulseLoader from 'vue-spinner/src/PulseLoader.vue';
import colorTheme from "../classes/colorTheme/ColorTheme";
import axios from "axios";

export default {
  components: {PulseLoader},
  data() {
    return {
      username: '',
      password: '',
      csrfToken: '',
      componentLoading: true,
      loginLoading: false,
      isLoggedUser: false,
      errorMessage: '',
      colorTheme: colorTheme
    }
  },
  activated() {
    this.isLogged();
  },
  mounted() {
    this.isLogged();
  },
  methods: {
    redirectToGoogle() {
      this.loginLoading = true;
      axios
          .get('/get_google_oauth_url')
          .then(response => {
            window.location.href = response.data.google_oauth_link;
          })
          .catch(error => {
            this.loginLoading = false;
            this.errorMessage = error.response?.data?.error || error.message;
          })
          .finally(() => {
            this.loginLoading = false;
          });
    },
    getUserName() {
      return localStorage.getItem('f1tipp_username') || '';
    },
    isLogged() {
      this.componentLoading = true;
      axios
          .get('/is_logged')
          .then(response => {
            this.isLoggedUser = true;
          })
          .catch(error => {
            this.csrfToken = localStorage.getItem('f1tipp_csrf_token') || '';
          })
          .finally(() => {
            this.componentLoading = false;
          });
    },
    login() {
      this.loginLoading = true;

      axios
          .post('/login', {
            username: this.username,
            password: this.password,
            _csrf_token: this.csrfToken,
            _remember_me: 1
          })
          .then(response => {
            // Sikeres login után pl. navigálhatsz, vagy session tárolás
            this.loading = false;
            location.reload();
          })
          .catch(error => {
            // Sikertelen bejelentkezés kezelése
            this.loading = false;
            this.errorMessage = error.response?.data?.error || error.message;
          })
          .finally(() => {
            this.loginLoading = false;
          });
    }
  }
}
</script>
<style scoped>
.pa {
  padding: 8px;
}

.pt {
  padding-top: 15px;
}

.ma {
  margin: 8px;
}

.text-red {
  color: red;
}
</style>
