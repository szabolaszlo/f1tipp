<template>
  <div id="f1tipp-calendar">
    <router-view v-slot="{ Component }">
      <Transition name="fade" mode="out-in">
        <component :is="Component" />
      </Transition>
    </router-view>
  </div>
</template>

<script>
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: all 0.5s;
  max-height: 500px;
}

.fade-enter-from,
.fade-leave-to {
  transition: all 0.5s;
  opacity: 0;
  max-height: 30px;
}
</style>