<script setup lang="ts">
import MainNavigation from "@/shared/components/Navigations/MainNavigation.vue";
import NavFooter from "@/shared/components/Navigations/NavFooter.vue";
import AppLoadingOverlay from "@/shared/components/AppLoadingOverlay/AppLoadingOverlay.vue";
import StatusBanner from "@/shared/components/StatusBanner/StatusBanner.vue";
import { useAuthStore } from "@/features/auth/stores/useAuthStore";
import { useRealtimeNotificationStore } from "@/shared/stores/useRealtimeNotificationStore";

const authStore = useAuthStore();
const realtimeNotificationStore = useRealtimeNotificationStore();
</script>

<template>
  <div class="min-h-screen bg-white">
    <AppLoadingOverlay />
    <StatusBanner
      v-if="realtimeNotificationStore.message"
      :message="realtimeNotificationStore.message"
      :tone="realtimeNotificationStore.tone"
      @dismiss="realtimeNotificationStore.dismiss()"
    />
    <MainNavigation />
    <RouterView />
    <NavFooter v-if="!authStore.isAuthenticated" />
  </div>
</template>
