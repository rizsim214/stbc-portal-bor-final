<script setup lang="ts">
import logoImage from "@/assets/resources/stbc-logo.jpg";

const clinicHighlights = [
  "Convenient laboratory booking for a more efficient patient experience",
  "Online release of laboratory results without the need for a return visit",
  "Patient-centered care for Borongan City and nearby communities",
  "Home services for patients who need support outside the clinic",
];

const patientMeaning = [
  "Patients can schedule laboratory services through a more efficient booking process.",
  "Laboratory results may be released online, reducing the need for an additional clinic visit.",
  "The clinic continues to support both in-clinic and home service needs when appropriate.",
];
</script>

<template>
  <main
    class="min-h-screen bg-[linear-gradient(180deg,color-mix(in_srgb,var(--color-brand-lighter)_18%,white),white_28%,color-mix(in_srgb,var(--color-brand-light)_8%,white))]">
    <div class="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
      <section class="border-b border-brand-light/20 pb-12">
        <div class="grid gap-10 lg:grid-cols-[1.1fr_0.9fr] lg:items-end">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-highlight">
              About St. Benedict Medical Care
            </p>
            <h1 class="mt-4 text-4xl font-semibold tracking-tight text-brand-darker sm:text-5xl">
              Care that stays clear, accessible, and patient-centered.
            </h1>
            <p class="mt-5 max-w-3xl text-sm leading-7 text-brand-dark/82 sm:text-lg">
              St. Benedict Medical Care serves Borongan City and nearby communities through diagnostic, laboratory,
              and clinical services. The clinic’s direction is practical: improve booking, make access easier, and
              reduce avoidable return visits through online result release.
            </p>
          </div>

          <div class="justify-self-start lg:justify-self-end">
            <img :src="logoImage" alt="Clinic logo"
              class="w-full max-w-md rounded-[1.5rem] border border-brand-light/20 bg-white object-cover shadow-[0_18px_50px_-30px_rgba(14,14,82,0.35)]"
              loading="lazy" />
          </div>
        </div>
      </section>

      <section class="grid gap-12 py-12 lg:grid-cols-[0.95fr_1.05fr]">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.16em] text-brand-highlight">Clinic Focus</p>
          <h2 class="mt-3 text-2xl font-semibold text-brand-darker sm:text-3xl">
            What the clinic is trying to improve for patients.
          </h2>
        </div>

        <div class="divide-y divide-brand-light/20 border-t border-brand-light/20">
          <div v-for="item in clinicHighlights" :key="item" class="py-5 first:pt-4">
            <p class="max-w-2xl text-sm leading-7 text-brand-darker sm:text-base">
              {{ item }}
            </p>
          </div>
        </div>
      </section>

      <section class="grid gap-12 border-t border-brand-light/20 py-12 lg:grid-cols-2">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.16em] text-brand-highlight">Vision</p>
          <h2 class="mt-3 text-2xl font-semibold text-brand-darker sm:text-3xl">
            A standard of care the clinic aims to grow into.
          </h2>
          <p class="mt-5 text-sm leading-8 text-brand-dark/82 sm:text-base">
            To be recognized as one of the best clinical and drug testing laboratories in Eastern Samar, providing
            compassionate, accessible, and high-quality service that meets or exceeds the needs and expectations of our
            clients, their families, and others whom we serve.
          </p>
        </div>

        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.16em] text-brand-highlight">For Patients</p>
          <h2 class="mt-3 text-2xl font-semibold text-brand-darker sm:text-3xl">
            What that means in practical terms.
          </h2>
          <ul class="mt-5 space-y-4">
            <li v-for="item in patientMeaning" :key="item" class="border-b border-brand-light/15 pb-4 last:border-b-0 last:pb-0">
              <p class="text-sm leading-7 text-brand-dark/82 sm:text-base">{{ item }}</p>
            </li>
          </ul>
        </div>
      </section>
    </div>
  </main>
</template>
