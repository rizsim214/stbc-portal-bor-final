<script setup lang="ts">
import MainNavigation from "@/shared/components/Navigations/MainNavigation.vue";
</script>

<template>
  <div class="min-h-screen bg-white">
    <MainNavigation />
    <RouterView />
  </div>
</template>
