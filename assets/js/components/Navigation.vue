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
            <router-link to="/login" class="btn btn-new">
              <i class="glyphicon glyphicon-user"></i>
              {{ getUserName() ? getUserName() : $t('login.title') }}
            </router-link>
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
          <ul class="nav navbar-nav navbar-right">
            <li>
              <router-link to="/login" @click="routerLinkClicked()">
                <i class="glyphicon glyphicon-user"></i>
                {{ getUserName() ? getUserName() : $t('login.title') }}
              </router-link>
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

export default {
  components: {Login: Login},
  methods: {
    getUserName() {
      return localStorage.getItem('f1tipp_username') || '';
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
