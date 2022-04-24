<template>
  <div id="f1tipp-calendar">
    <router-view v-slot="{ Component }">
      <Transition name="fade">
        <component :is="Component" />
      </Transition>
    </router-view>
  </div>
</template>

<script>
</script>