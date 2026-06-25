<script setup lang="ts">
import { computed, ref } from "vue";
import SideNavigation from "@/shared/components/Navigations/SideNavigation.vue";

const isDesktopSideNavCollapsed = ref(false);

const layoutStyle = computed(() => ({
  "--dashboard-sidebar-width": isDesktopSideNavCollapsed.value ? "88px" : "300px",
}));
</script>

<template>
  <div
    class="grid min-h-screen md:grid-cols-[var(--dashboard-sidebar-width)_1fr]"
    :style="layoutStyle"
  >
    <SideNavigation v-model:collapsed="isDesktopSideNavCollapsed" />

    <main class="bg-brand-lighter/10 p-4 pt-16 sm:p-6 sm:pt-20 lg:p-8 lg:pt-8 md:pt-6">
      <RouterView />
    </main>
  </div>
</template>
