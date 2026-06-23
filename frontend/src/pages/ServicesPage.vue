<script setup lang="ts">
import servicesOffered from "@/assets/resources/services offered.jpg";

type ServiceHighlight = {
  name: string;
  description: string;
};

type LabSection = {
  title: string;
  description: string;
  tests: string[];
};

const serviceHighlights: ServiceHighlight[] = [
  {
    name: "Medical Check-up",
    description: "General consultation for symptoms, vitals, and next-step care guidance.",
  },
  {
    name: "Clinical Laboratory Tests",
    description: "Blood, urine, stool, and infection screening for common health concerns.",
  },
  {
    name: "2D-Echo",
    description: "Ultrasound scan of the heart to check structure and pumping function.",
  },
  {
    name: "ECG",
    description: "Records the heart's electrical activity to help detect rhythm issues.",
  },
  {
    name: "Drug Testing",
    description: "Substance screening commonly used for employment or compliance requirements.",
  },
];

const laboratorySections: LabSection[] = [
  {
    title: "Chemistry",
    description: "Tests that help assess blood sugar, kidney function, liver function, and electrolytes.",
    tests: [
      "FBS/RBS",
      "75g OGTT",
      "BUA",
      "Creatinine",
      "BUN",
      "Lipid Profile",
      "SGPT",
      "SGOT",
      "Albumin",
      "Phosphorous",
      "Na+",
      "K+",
      "Ca++",
      "Cl-",
    ],
  },
  {
    title: "Hematology",
    description: "Tests that focus on blood cells, clotting, and blood typing.",
    tests: ["CBC", "Hematocrit", "Hemoglobin", "Platelet Count", "WBC Differential Count", "Blood Typing", "RH Typing"],
  },
  {
    title: "Clinical Microscopy",
    description: "Common urine and stool tests for infection, parasites, and digestive concerns.",
    tests: ["Urinalysis", "UCG", "Fecalysis", "FOBT", "Kato Katz"],
  },
  {
    title: "Serology",
    description: "Screening tests for infections and immune response markers.",
    tests: ["HBsAg", "Anti-HCV", "VDRL / RPR", "Typhidot", "H. pylori test", "Dengue Rapid Test"],
  },
  {
    title: "Special Examinations",
    description: "Additional tests ordered for deeper assessment of specific conditions.",
    tests: ["HbA1c", "TSH", "T3", "T4", "Troponin I"],
  },
];

const bloodChemistryHighlights = [
  "Fasting Blood Sugar",
  "Lipid Profile (Total Cholesterol, HDL, LDL, Triglycerides)",
  "Blood Urea",
  "Creatinine",
  "Blood Uric Acid",
  "SGPT / ALT",
  "SGOT / AST",
  "Albumin",
  "Phosphorus",
  "Electrolytes (Sodium, Potassium, Chloride, Ionized Calcium)",
  "OGTT (Oral Glucose Tolerance Test 75g)",
  "HbA1c",
];

const homeServices = [
  {
    name: "Phlebotomy / Blood Extraction",
    description: "At-home blood collection for requested laboratory tests.",
  },
  {
    name: "ECG",
    description: "At-home heart rhythm recording for basic cardiac screening.",
  },
];

const labTestTooltips: Record<string, string> = {
  "FBS/RBS": "Measures blood sugar levels for diabetes screening and monitoring.",
  "75g OGTT": "Checks how your body processes glucose after a 75g sugar drink.",
  BUA: "Measures uric acid level in blood for gout and kidney risk checks.",
  Creatinine: "Assesses kidney function by measuring waste filtration levels.",
  BUN: "Evaluates kidney function and hydration through blood urea nitrogen.",
  "Lipid Profile": "Measures cholesterol and triglycerides for cardiovascular risk.",
  SGPT: "Liver enzyme test (ALT) used to check liver cell injury.",
  SGOT: "Liver and muscle enzyme test (AST) used in organ function assessment.",
  Albumin: "Blood protein test used to assess liver, kidney, and nutrition status.",
  Phosphorous: "Checks phosphorus level for bone, kidney, and metabolic balance.",
  "Na+": "Measures sodium level for fluid balance and nerve function.",
  "K+": "Measures potassium level for heart, muscle, and nerve function.",
  "Ca++": "Measures calcium level for bone health and muscle/nerve activity.",
  "Cl-": "Measures chloride level to assess hydration and acid-base balance.",
  CBC: "Complete blood count to check red cells, white cells, and platelets.",
  Hematocrit: "Shows percentage of red blood cells in your blood volume.",
  Hemoglobin: "Measures oxygen-carrying protein in red blood cells.",
  "Platelet Count": "Checks clotting cell levels to assess bleeding risk.",
  "WBC Differential Count": "Breaks down white blood cell types for infection clues.",
  "Blood Typing": "Determines ABO blood group for transfusion compatibility.",
  "RH Typing": "Determines Rh factor (positive or negative) in blood.",
  Urinalysis: "General urine test for kidney health, infection, and metabolism.",
  UCG: "Urine pregnancy test that detects hCG hormone.",
  Fecalysis: "Stool test to detect parasites, infection, or digestive issues.",
  FOBT: "Detects hidden blood in stool for gastrointestinal screening.",
  "Kato Katz": "Stool microscopy method used to detect intestinal parasite eggs.",
  HBsAg: "Screens for active Hepatitis B infection.",
  "Anti-HCV": "Screens for Hepatitis C virus antibodies.",
  "VDRL / RPR": "Non-treponemal screening tests for syphilis.",
  Typhidot: "Rapid antibody test used in typhoid fever screening.",
  "H. pylori test": "Detects Helicobacter pylori linked to ulcer disease.",
  "Dengue Rapid Test": "Rapid screening for dengue infection markers.",
  HbA1c: "Average blood glucose level over the past 2 to 3 months.",
  TSH: "Thyroid-stimulating hormone test for thyroid function assessment.",
  T3: "Triiodothyronine hormone level used in thyroid evaluation.",
  T4: "Thyroxine hormone level used in thyroid function assessment.",
  "Troponin I": "Cardiac marker used to detect heart muscle injury.",
};

const bloodChemistryTooltips: Record<string, string> = {
  "Fasting Blood Sugar": "Checks current blood glucose level after fasting.",
  "Lipid Profile (Total Cholesterol, HDL, LDL, Triglycerides)":
    "Evaluates cholesterol and fats linked to heart and vascular risk.",
  "Blood Urea": "Measures urea level to help assess kidney function.",
  Creatinine: "Measures kidney filtration performance.",
  "Blood Uric Acid": "Assesses uric acid level for gout and kidney concerns.",
  "SGPT / ALT": "Liver enzyme test used to detect liver inflammation.",
  "SGOT / AST": "Enzyme test that supports liver and muscle assessment.",
  Albumin: "Measures key blood protein linked to liver and kidney health.",
  Phosphorus: "Checks phosphorus for bone and metabolic balance.",
  "Electrolytes (Sodium, Potassium, Chloride, Ionized Calcium)":
    "Assesses electrolyte balance for hydration, heart, and nerve function.",
  "OGTT (Oral Glucose Tolerance Test 75g)":
    "Monitors blood sugar response after a 75g oral glucose load.",
  HbA1c: "Shows average blood sugar over roughly the last 2 to 3 months.",
};

const getLabTestTooltip = (test: string): string =>
  labTestTooltips[test] ?? "Laboratory diagnostic test for clinical assessment.";

const getBloodChemistryTooltip = (item: string): string =>
  bloodChemistryTooltips[item] ?? "Blood chemistry test for diagnostic assessment.";
</script>

<template>
  <main
    class="min-h-screen bg-[linear-gradient(180deg,color-mix(in_srgb,var(--color-brand-lighter)_18%,white),white_28%,color-mix(in_srgb,var(--color-brand-light)_8%,white))]">
    <div class="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
      <section class="border-b border-brand-light/20 pb-12">
        <div class="grid gap-10 lg:grid-cols-[1.1fr_0.9fr] lg:items-end">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-highlight">
              St. Benedict Medical Care
            </p>
            <h1 class="mt-4 text-4xl font-semibold tracking-tight text-brand-darker sm:text-5xl">
              Services that stay clear, useful, and easy to scan.
            </h1>
            <p class="mt-5 max-w-3xl text-sm leading-7 text-brand-dark/82 sm:text-lg">
              This page gives the clinic’s main services in one place, from consultation and diagnostic work to
              laboratory testing, cardiac screening, and selected home services.
            </p>

            <div class="mt-8 flex flex-wrap gap-x-8 gap-y-3 text-sm text-brand-dark/80">
              <span>Medical check-up</span>
              <span>Laboratory tests</span>
              <span>ECG and 2D-Echo</span>
              <span>Drug testing</span>
              <span>Home services</span>
            </div>
          </div>

          <div class="justify-self-start lg:justify-self-end">
            <img :src="servicesOffered" alt="Services offered poster"
              class="w-full max-w-md rounded-[1.5rem] border border-brand-light/20 bg-white object-contain shadow-[0_18px_50px_-30px_rgba(14,14,82,0.35)]"
              loading="lazy" />
          </div>
        </div>
      </section>

      <section class="grid gap-12 py-12 lg:grid-cols-[0.95fr_1.05fr]">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.16em] text-brand-highlight">General Services</p>
          <h2 class="mt-3 text-2xl font-semibold text-brand-darker sm:text-3xl">
            The services patients ask for most often.
          </h2>
          <p class="mt-4 max-w-xl text-sm leading-7 text-brand-dark/82 sm:text-base">
            Start here if you want a quick sense of what the clinic handles before looking through the full test
            categories below.
          </p>
        </div>

        <div class="divide-y divide-brand-light/20 border-t border-brand-light/20">
          <article v-for="service in serviceHighlights" :key="service.name" class="py-5 first:pt-4">
            <h3 class="text-lg font-semibold text-brand-darker">{{ service.name }}</h3>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-brand-dark/80">{{ service.description }}</p>
          </article>
        </div>
      </section>

      <section class="border-t border-brand-light/20 py-12">
        <div class="max-w-3xl">
          <p class="text-xs font-semibold uppercase tracking-[0.16em] text-brand-highlight">Laboratory Tests</p>
          <h2 class="mt-3 text-2xl font-semibold text-brand-darker sm:text-3xl">
            Test categories grouped for faster reference.
          </h2>
          <p class="mt-4 text-sm leading-7 text-brand-dark/82 sm:text-base">
            Hover over a test name on desktop to see a short explanation. The grouping is meant to help patients scan
            the list without reading every item in full detail.
          </p>
        </div>

        <div class="mt-10 space-y-10">
          <article v-for="section in laboratorySections" :key="section.title"
            class="grid gap-5 border-b border-brand-light/15 pb-8 last:border-b-0 last:pb-0 lg:grid-cols-[0.36fr_0.64fr]">
            <div>
              <h3 class="text-xl font-semibold text-brand-darker">{{ section.title }}</h3>
              <p class="mt-3 max-w-sm text-sm leading-6 text-brand-dark/78">{{ section.description }}</p>
            </div>

            <ul class="grid gap-x-4 gap-y-2 sm:grid-cols-2 xl:grid-cols-3">
              <li v-for="test in section.tests" :key="test"
                class="group relative border-b border-brand-light/10 py-1 text-sm text-brand-darker">
                <span class="transition group-hover:text-brand-highlight">{{ test }}</span>
                <span
                  class="pointer-events-none absolute left-0 top-full z-10 mt-2 hidden w-72 rounded-md bg-brand-darker px-3 py-2 text-xs text-white shadow-lg sm:group-hover:block">
                  {{ getLabTestTooltip(test) }}
                </span>
              </li>
            </ul>
          </article>
        </div>
      </section>

      <section class="grid gap-12 border-t border-brand-light/20 py-12 lg:grid-cols-[1fr_0.9fr]">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.16em] text-brand-highlight">Blood Chemistry</p>
          <h2 class="mt-3 text-2xl font-semibold text-brand-darker sm:text-3xl">
            Common chemistry-related requests.
          </h2>
          <ul class="mt-6 grid gap-x-6 gap-y-2 sm:grid-cols-2">
            <li v-for="item in bloodChemistryHighlights" :key="item"
              class="group relative border-b border-brand-light/10 py-2 text-sm text-brand-darker">
              <span class="transition group-hover:text-brand-highlight">{{ item }}</span>
              <span
                class="pointer-events-none absolute left-0 top-full z-10 mt-2 hidden w-72 rounded-md bg-brand-darker px-3 py-2 text-xs text-white shadow-lg sm:group-hover:block">
                {{ getBloodChemistryTooltip(item) }}
              </span>
            </li>
          </ul>
        </div>

        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.16em] text-brand-highlight">Home Services</p>
          <h2 class="mt-3 text-2xl font-semibold text-brand-darker sm:text-3xl">
            Support outside the clinic when needed.
          </h2>
          <div class="mt-6 space-y-5">
            <article v-for="service in homeServices" :key="service.name" class="border-b border-brand-light/15 pb-5 last:border-b-0 last:pb-0">
              <h3 class="text-lg font-semibold text-brand-darker">{{ service.name }}</h3>
              <p class="mt-2 text-sm leading-6 text-brand-dark/80">{{ service.description }}</p>
            </article>
          </div>
        </div>
      </section>

    </div>
  </main>
</template>
