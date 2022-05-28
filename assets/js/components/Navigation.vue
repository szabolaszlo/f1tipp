<template>
  <div id="f1tipp-navigation">
    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container-fluid">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
          <button id="hambi-button" type="button" class="navbar-toggle" data-toggle="collapse"
                  data-target="#f1tipp-navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="/"><img src="@images/logo.png" height="50" alt=""></a>
        </div>
        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="f1tipp-navbar-collapse">
          <ul class="nav navbar-nav navbar-right">
            <li>
              <Login></Login>
            </li>
          </ul>
          <ul class="nav navbar-nav navbar-left">
            <li>
              <router-link to="/actual" @click="routerLinkClicked()">{{ $t('actual.title') }}</router-link>
            </li>
            <li>
              <router-link to="/calendar" @click="routerLinkClicked()">{{ $t('calendar.title') }}</router-link>
            </li>
            <li>
              <router-link to="/rules" @click="routerLinkClicked()">{{ $t('rules.title') }}</router-link>
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
