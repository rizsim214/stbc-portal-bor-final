<script setup lang="ts">
import MainNavigation from "@/shared/components/Navigations/MainNavigation.vue";
import NavFooter from "@/shared/components/Navigations/NavFooter.vue";
import { useAuthStore } from "@/features/auth/stores/useAuthStore";

const authStore = useAuthStore();
</script>

<template>
  <div class="min-h-screen bg-white">
    <MainNavigation />
    <RouterView />
    <NavFooter v-if="!authStore.isAuthenticated" />
  </div>
</template>
