<script setup lang="ts">
import SideNavigation from "@/shared/components/Navigations/SideNavigation.vue";
</script>

<template>
  <div class="grid min-h-screen md:grid-cols-[300px_1fr]">
    <SideNavigation />

    <main class="bg-brand-lighter/10 p-4 pt-16 sm:p-6 sm:pt-20 lg:p-8 lg:pt-8 md:pt-6">
      <RouterView />
    </main>
  </div>
</template>
