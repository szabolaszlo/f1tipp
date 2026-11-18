<template>
  <header id="f1tipp-navigation" class="f1-header">
    <div class="f1-header__bar">
      <a class="f1-logo" href="/#/actual">F1<span>TIPP</span></a>
      <nav class="f1-nav f1-nav--desktop" aria-label="Főmenü">
        <router-link v-for="item in menu" :key="item.to" :to="item.to" @click="routerLinkClicked()">
          {{ $t(item.label) }}
        </router-link>
      </nav>
      <div class="f1-user">
        <router-link v-if="!username" to="/login" class="f1-user__login" @click="routerLinkClicked()">
          <span v-if="componentLoading"><pulse-loader color="#e10600" size="6px"/></span>
          <span v-else>{{ $t('login.title') }}</span>
        </router-link>
        <template v-else>
          <a v-if="isAdmin" href="/admin" class="f1-user__admin" :title="$t('login.admin')">
            <i class="glyphicon glyphicon-cog"></i>
            <span class="f1-user__admin-label">{{ $t('login.admin') }}</span>
          </a>
          <span class="f1-user__avatar" aria-hidden="true">{{ username.charAt(0).toUpperCase() }}</span>
          <span class="f1-user__name" :title="username">{{ username }}</span>
          <a href="/logout" class="f1-user__logout" :title="$t('login.logout')">
            <i class="glyphicon glyphicon-log-out"></i>
          </a>
        </template>
      </div>
    </div>
    <nav class="f1-nav f1-nav--mobile" aria-label="Főmenü">
      <router-link v-for="item in menu" :key="item.to" :to="item.to" @click="routerLinkClicked()">
        {{ $t(item.label) }}
      </router-link>
    </nav>
  </header>
</template>

<script>
import Login from "./Login";
import axios from "axios";
import PulseLoader from 'vue-spinner/src/PulseLoader.vue';

export default {
  components: {
    Login: Login,
    PulseLoader: PulseLoader
  },
  data() {
    return {
      username: '',
      isAdmin: false,
      componentLoading: false,
      menu: [
        {to: '/betting', label: 'betting.title'},
        {to: '/actual', label: 'actual.title'},
        {to: '/results', label: 'results.title'},
        {to: '/calendar', label: 'calendar.title'},
        {to: '/rules', label: 'rules.title'},
        {to: '/statistics', label: 'statistics.title'}
      ]
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
            this.isAdmin = response.data.isAdmin === true;
            this.isLoggedUser = true;
          })
          .catch(error => {
            this.isAdmin = false;
            this.csrfToken = localStorage.getItem('f1tipp_csrf_token') || '';
          })
          .finally(() => {
            this.componentLoading = false;
          });
    },
    routerLinkClicked: function () {
      window.scrollTo(0, 0);
    }
  }
}
</script>

<style scoped>
.f1-header {
  position: sticky;
  top: 0;
  z-index: 1000;
  background: var(--f1-bg);
  border-bottom: 1px solid var(--f1-line);
}

.f1-header__bar {
  max-width: 1280px;
  margin: 0 auto;
  height: 56px;
  padding: 0 16px;
  display: flex;
  align-items: center;
  gap: 28px;
}

.f1-logo {
  flex: none;
  font-weight: 900;
  font-style: italic;
  font-size: 24px;
  letter-spacing: -.5px;
  color: #fff;
  text-decoration: none;
}

.f1-logo span {
  color: var(--f1-red);
}

.f1-logo:hover, .f1-logo:focus {
  color: #fff;
  text-decoration: none;
}

.f1-nav {
  display: flex;
  gap: 22px;
  font-size: 14px;
  font-weight: 700;
  text-transform: uppercase;
  white-space: nowrap;
}

.f1-nav a {
  padding: 17px 0 15px;
  border-bottom: 3px solid transparent;
  color: var(--f1-text);
  opacity: .6;
  text-decoration: none;
}

.f1-nav a:hover {
  opacity: .9;
}

/* egérkattintásra ne legyen böngésző-fókuszkeret (kitakarta a betűket), billentyűzetnél halvány aláhúzás jelzi */
.f1-header a:focus {
  outline: none;
}

.f1-nav a:focus-visible {
  opacity: 1;
  border-bottom-color: var(--f1-muted);
}

.f1-user__login:focus-visible,
.f1-user__admin:focus-visible,
.f1-user__logout:focus-visible {
  border-color: var(--f1-muted);
}

.f1-nav a.router-link-active {
  opacity: 1;
  border-bottom-color: var(--f1-select);
}

.f1-nav--mobile {
  display: none;
}

.f1-user {
  margin-left: auto;
  display: flex;
  align-items: center;
  gap: 8px;
  min-width: 0;
  font-size: 14px;
  font-weight: 700;
}

.f1-user__avatar {
  flex: none;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: #2c2c3a;
  display: grid;
  place-items: center;
  font-size: 12px;
}

.f1-user__name {
  max-width: 160px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.f1-user__login,
.f1-user__admin,
.f1-user__logout {
  color: var(--f1-text);
  text-decoration: none;
  padding: 6px 10px;
  border: 1px solid var(--f1-line);
  border-radius: 6px;
}

.f1-user__admin {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  margin-right: 4px;
}

.f1-user__login:hover,
.f1-user__admin:hover,
.f1-user__logout:hover {
  border-color: var(--f1-red);
  color: #fff;
  text-decoration: none;
}

@media (max-width: 900px) {
  .f1-header__bar {
    height: 52px;
    padding: 0 12px;
  }

  .f1-nav--desktop {
    display: none;
  }

  .f1-nav--mobile {
    display: flex;
    gap: 18px;
    overflow-x: auto;
    padding: 0 14px;
    font-size: 13px;
    background: var(--f1-panel);
    scrollbar-width: none;
  }

  .f1-nav--mobile::-webkit-scrollbar {
    display: none;
  }

  .f1-nav--mobile a {
    padding: 10px 0 8px;
  }

  .f1-user__name {
    max-width: 90px;
  }

  /* keskeny fejlécben csak a fogaskerék ikon marad */
  .f1-user__admin-label {
    display: none;
  }
}
</style>
