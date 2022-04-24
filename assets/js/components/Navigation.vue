<template>
  <div id="f1tipp-navigation">
    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container-fluid">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse"
                  data-target="#f1tipp-navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="/"><img src="build/images/logo.png" height="51" alt=""></a>
        </div>
        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="f1tipp-navbar-collapse">
          <ul class="nav navbar-nav navbar-right">
            <li>
            Itt lesz a login
            </li>
          </ul>
          <ul class="nav navbar-nav navbar-left">
            <li>
            <router-link to="/app-one">App1</router-link>
            </li>
            <li>
              <router-link to="/app-two">App2</router-link>
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
</script>