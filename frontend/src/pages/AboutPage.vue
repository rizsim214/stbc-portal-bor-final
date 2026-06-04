<script setup lang="ts">
import logoImage from "@/assets/resources/stbc-logo.jpg";

const clinicHighlights = [
  "Convenient laboratory booking for a more efficient patient experience",
  "Online release of laboratory results without the need for a return visit",
  "Patient-centered care for Borongan City and nearby communities",
  "Home services for patients who need support outside the clinic",
];
</script>

<template>
  <main class="mx-auto w-full max-w-6xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
    <section class="overflow-hidden rounded-2xl border border-brand-light/30 bg-white shadow-sm">
      <div class="grid gap-0 lg:grid-cols-[1.05fr_0.95fr]">
        <div class="p-5 sm:p-8 lg:p-10">
          <p class="text-xs font-semibold uppercase tracking-[0.16em] text-brand-highlight sm:text-sm">
            About St. Benedict Medical Care
          </p>
          <h1 class="mt-3 text-2xl font-semibold tracking-tight text-brand-darker sm:text-4xl">
            Care that is clear, accessible, and patient-centered
          </h1>
          <p class="mt-4 max-w-2xl text-sm leading-7 text-brand-dark sm:text-base">
            St. Benedict Medical Care serves patients in Borongan City and nearby communities with diagnostic,
            laboratory, and clinical services. Our goal is to provide a more efficient laboratory booking process and
            enable online release of results so patients do not need to return to the clinic to obtain them.
          </p>

          <div class="mt-6 grid gap-3 sm:grid-cols-2">
            <article v-for="item in clinicHighlights" :key="item"
              class="rounded-xl border border-brand-light/25 bg-brand-lighter/10 p-4">
              <p class="text-sm leading-6 text-brand-darker">{{ item }}</p>
            </article>
          </div>
        </div>

        <div class="bg-linear-to-b from-brand-lighter/15 to-white p-5 sm:p-8 lg:p-10">
          <img :src="logoImage" alt="Clinic vision statement poster"
            class="h-full w-full rounded-2xl border border-brand-light/20 object-cover shadow-md" loading="lazy" />
        </div>
      </div>
    </section>

    <section class="mt-8 grid gap-6 md:grid-cols-2">
      <article
        class="rounded-2xl border border-brand-light/25 bg-white p-5 shadow-[0_8px_24px_-16px_rgba(21,5,120,0.45)] sm:p-6">
        <h2 class="text-xl font-semibold text-brand-darker sm:text-2xl">Vision</h2>
        <p class="mt-3 text-sm leading-7 text-brand-dark sm:text-base">
          To be recognized as one of the best clinical and drug testing laboratories in Eastern Samar, providing
          compassionate, accessible, and high-quality service that meets or exceeds the needs and expectations of our
          clients, their families, and others whom we serve.
        </p>
      </article>

      <article
        class="rounded-2xl border border-brand-light/25 bg-white p-5 shadow-[0_8px_24px_-16px_rgba(21,5,120,0.45)] sm:p-6">
        <h2 class="text-xl font-semibold text-brand-darker sm:text-2xl">What this means for patients</h2>
        <ul class="mt-3 list-disc space-y-3 pl-5 text-sm leading-6 text-brand-dark sm:text-base">
          <li>Patients can schedule laboratory services through a more efficient booking process.</li>
          <li>Laboratory results may be released online, reducing the need for an additional clinic visit.</li>
          <li>The clinic continues to support both in-clinic and home service needs when appropriate.</li>
        </ul>
      </article>
    </section>
  </main>
</template>
