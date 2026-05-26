<script setup lang="ts">
import MainNavigation from "@/shared/components/Navigations/MainNavigation.vue";
import NavFooter from "@/shared/components/Navigations/NavFooter.vue";
import AppLoadingOverlay from "@/shared/components/feedback/AppLoadingOverlay.vue";
import { useAuthStore } from "@/features/auth/stores/useAuthStore";

const authStore = useAuthStore();
</script>

<template>
  <div class="min-h-screen bg-white">
    <AppLoadingOverlay />
    <MainNavigation />
    <RouterView />
    <NavFooter v-if="!authStore.isAuthenticated" />
  </div>
</template>
