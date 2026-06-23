<script setup lang="ts">
const clinicInfo = {
  name: "St. Benedict Laboratory, Drug Testing & Medical Clinic",
  address: "Sabate Bldg, Real St., Brgy. Songco, Borongan City, Eastern Samar",
  clinicContact: "(+63) 953-241-5158",
  homeServiceContact: "(+63) 917-116-4346",
  facebookPage: "ST.BENEDICTMEDICALPH",
};
const currentYear = new Date().getFullYear();
</script>

<template>
  <footer class="bg-linear-to-r from-brand-darker to-brand-dark px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto grid w-full max-w-6xl gap-6 text-white/90 md:grid-cols-[1.1fr_0.9fr] md:items-end">
      <div>
        <p class="text-sm font-semibold tracking-wide text-white">{{ clinicInfo.name }}</p>
        <p class="mt-1 text-xs sm:text-sm text-white/80">{{ clinicInfo.address }}</p>
      </div>

      <div class="grid gap-2 text-xs sm:grid-cols-2 sm:text-sm md:justify-self-end">
        <p><span class="font-semibold text-white">Clinic:</span> {{ clinicInfo.clinicContact }}</p>
        <p><span class="font-semibold text-white">Home Service:</span> {{ clinicInfo.homeServiceContact }}</p>
        <p><span class="font-semibold text-white">Facebook:</span> {{ clinicInfo.facebookPage }}</p>
      </div>
    </div>

    <div class="mx-auto mt-4 w-full max-w-6xl border-t border-white/10 pt-4 text-center text-[11px] text-white/70 sm:text-xs">
      <p>
        &copy; {{ currentYear }} {{ clinicInfo.name }}. All rights reserved.
      </p>
    </div>
  </footer>
</template>
