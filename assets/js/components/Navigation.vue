<template>
  <div id="f1tipp-navigation">
    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container-fluid">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
          <button id="hambi-button" class="navbar-toggle" data-target="#f1tipp-navbar-collapse" data-toggle="collapse"
                  type="button">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="/#/actual"><img alt="" height="50" src="@images/logo.png"></a>
          <div class="nav navbar-nav navbar-right pull-right visible-xs" style="padding-right: 20px;">
            <router-link v-if="!username" to="/login" class="btn btn-new">
              <i class="glyphicon glyphicon-user"></i>
              <span v-if="componentLoading" ><pulse-loader :color="colorTheme.f1TippColor5" style="display: inline-flex"/></span>
              <span v-if="!componentLoading">{{ $t('login.title') }}</span>
            </router-link>
            <a v-if="username" class="btn btn-new" aria-disabled="true">
              <i class="glyphicon glyphicon-user"></i>
              {{ username.length > 10 ? username.substring(0, 6) + '…' : username }}
            </a>
            <a v-if="username" href="/logout" class="btn btn-new">
              <i class="glyphicon glyphicon-log-out"></i>
            </a>
          </div>
        </div>
        <!-- Collect the nav links, forms, and other content for toggling -->
        <div id="f1tipp-navbar-collapse" class="collapse navbar-collapse">
          <ul class="nav navbar-nav navbar-left">
            <li>
              <router-link to="/betting" @click="routerLinkClicked()">{{ $t('betting.title') }}</router-link>
            </li>
            <li>
              <router-link to="/actual" @click="routerLinkClicked()">{{ $t('actual.title') }}</router-link>
            </li>
            <li>
              <router-link to="/results" @click="routerLinkClicked()">{{ $t('results.title') }}</router-link>
            </li>
            <li>
              <router-link to="/calendar" @click="routerLinkClicked()">{{ $t('calendar.title') }}</router-link>
            </li>
            <li>
              <router-link to="/rules" @click="routerLinkClicked()">{{ $t('rules.title') }}</router-link>
            </li>
            <li>
              <router-link to="/statistics" @click="routerLinkClicked()">{{ $t('statistics.title') }}</router-link>
            </li>
          </ul>
          <!-- Jobb oldali menüpontok -->
          <ul class="nav navbar-nav navbar-right">
            <!-- Ha nincs bejelentkezve a felhasználó, akkor sima "Belépés" link -->
            <li>
              <router-link v-if="!username" to="/login" @click="routerLinkClicked()">
                <i class="glyphicon glyphicon-user"></i>
                <span v-if="componentLoading" ><pulse-loader :color="colorTheme.f1TippColor5" style="display: inline-flex"/></span>
                <span v-if="!componentLoading">{{ $t('login.title') }}</span>
              </router-link>
            </li>
            <li>
              <a v-if="username" class="btn btn-new" aria-disabled="true">
                <i class="glyphicon glyphicon-user"></i>
                {{ username }}
              </a>
            </li>
            <li>
              <a v-if="username" href="/logout" class="btn btn-new">
                <i class="glyphicon glyphicon-log-out"></i>
              </a>
            </li>
          </ul>
        </div>
        <!-- /.navbar-collapse -->
      </div>
      <!-- /.container -->
    </nav>
  </div>
</template>

<script>
import Login from "./Login";
import axios from "axios";
import colorTheme from "../classes/colorTheme/ColorTheme";
import PulseLoader from 'vue-spinner/src/PulseLoader.vue';

export default {
  components: {
    Login: Login,
    PulseLoader: PulseLoader
  },
  data() {
    return {
      username: '',
      componentLoading: false,
      colorTheme: colorTheme
    }
  },
  mounted() {
    this.isLogged();
  },
  activated() {
    this.isLogged();
  },
  methods: {
    isLogged() {
      this.componentLoading = true;
      axios
          .get('/is_logged')
          .then(response => {
            this.username = response.data.userName;
            this.isLoggedUser = true;
          })
          .catch(error => {
            this.csrfToken = localStorage.getItem('f1tipp_csrf_token') || '';
          })
          .finally(() => {
            this.componentLoading = false;
          });
    },
    routerLinkClicked: function () {
      window.scrollTo(0, 0);
      let navBarButton = document.getElementById("hambi-button");
      if (navBarButton.getAttribute('aria-expanded') === 'true') {
        navBarButton.click()
      }
    }
  }
}
</script>

<style scoped>
.disabled {
  pointer-events: none;
  cursor: not-allowed;
}
</style>
