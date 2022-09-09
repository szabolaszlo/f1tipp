<template>
  <div id="main-component">
    <router-view v-slot="{ Component }">
      <Transition name="fade" mode="out-in">
        <keep-alive exclude="Actual">
          <component :is="Component"/>
        </keep-alive>
      </Transition>
    </router-view>
  </div>
</template>

<script>
export default {
  name: "Main"
}
</script>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: all 0.3s;
  max-height: 500px;
}

.fade-enter-from,
.fade-leave-to {
  transition: all 0.3s;
  opacity: 0;
  max-height: 30px;
}
</style>
