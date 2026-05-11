<script setup lang="ts">
import MainNavigation from "@/shared/components/Navigations/MainNavigation.vue";
import NavFooter from "@/shared/components/Navigations/NavFooter.vue";
</script>

<template>
  <div class="min-h-screen bg-white">
    <MainNavigation />
    <RouterView />
    <NavFooter />
  </div>
</template>
