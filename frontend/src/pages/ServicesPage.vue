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

const clinicInfo = {
  address: "Sabate Bldg, Real St., Brgy. Songco, Borongan City, Eastern Samar",
  page: "ST.BENEDICTMEDICALPH",
  homeServiceContact: "(+63) 917-116-4346",
  clinicContact: "(+63) 953-241-5158",
};

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
  <main class="mx-auto w-full max-w-6xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
    <section
      class="overflow-hidden rounded-2xl border border-brand-light/30 bg-linear-to-r from-white via-brand-lighter/15 to-white shadow-sm">
      <div class="grid gap-6 lg:grid-cols-[1.15fr_0.85fr]">
        <div class="p-5 sm:p-8">
          <p
            class="text-xs font-semibold uppercase tracking-[0.14em] text-brand-highlight sm:text-sm sm:tracking-[0.18em]">
            St. Benedict Medical Care
          </p>
          <h1 class="mt-3 text-2xl font-semibold tracking-tight text-brand-darker sm:text-4xl">
            Our Services
          </h1>
          <p class="mt-4 max-w-3xl text-sm leading-7 text-brand-dark sm:text-lg">
            Review the clinic's principal services, arrange laboratory tests through a more efficient process, and
            access laboratory results online without an unnecessary return visit.
          </p>

          <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-brand-light/25 bg-white/80 p-4">
              <p class="text-xs font-semibold uppercase tracking-[0.12em] text-brand-dark/70">Start here</p>
              <p class="mt-1 text-sm font-semibold text-brand-darker">Initial consultation</p>
            </div>
            <div class="rounded-xl border border-brand-light/25 bg-white/80 p-4">
              <p class="text-xs font-semibold uppercase tracking-[0.12em] text-brand-dark/70">Most used</p>
              <p class="mt-1 text-sm font-semibold text-brand-darker">Laboratory tests and blood work</p>
            </div>
            <div class="rounded-xl border border-brand-light/25 bg-white/80 p-4">
              <p class="text-xs font-semibold uppercase tracking-[0.12em] text-brand-dark/70">Need help</p>
              <p class="mt-1 text-sm font-semibold text-brand-darker">Home services and contact details</p>
            </div>
            <div class="rounded-xl border border-brand-light/25 bg-white/80 p-4">
              <p class="text-xs font-semibold uppercase tracking-[0.12em] text-brand-dark/70">Online results</p>
              <p class="mt-1 text-sm font-semibold text-brand-darker">Online laboratory result release</p>
            </div>
          </div>
        </div>

        <div class="bg-linear-to-b from-brand-lighter/20 to-white p-5 sm:p-8">
          <img :src="servicesOffered" alt="Services offered poster"
            class="w-full rounded-xl border border-brand-light/20 object-contain shadow-md" loading="lazy" />
        </div>
      </div>
    </section>

    <section
      class="mt-8 rounded-2xl border border-brand-light/25 bg-white p-5 shadow-[0_8px_24px_-16px_rgba(21,5,120,0.45)] sm:p-6">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <h2 class="text-xl font-semibold text-brand-darker sm:text-2xl">General Services</h2>
          <p class="mt-1 text-sm text-brand-dark/90 sm:text-base">
            A concise overview of the services most frequently requested by patients.
          </p>
        </div>
      </div>

      <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
        <article v-for="service in serviceHighlights" :key="service.name"
          class="rounded-xl border border-brand-light/25 bg-brand-lighter/10 p-4 transition hover:-translate-y-0.5 hover:bg-brand-lighter/20">
          <h3 class="text-base font-semibold text-brand-darker">{{ service.name }}</h3>
          <p class="mt-2 text-sm leading-6 text-brand-dark/90">{{ service.description }}</p>
        </article>
      </div>
    </section>

    <section
      class="mt-8 rounded-2xl border border-brand-light/25 bg-white p-5 shadow-[0_8px_24px_-16px_rgba(21,5,120,0.45)] sm:p-6">
        <h2 class="text-xl font-semibold text-brand-darker sm:text-2xl">Laboratory Tests</h2>
        <p class="mt-2 text-sm text-brand-dark/90 sm:text-base">
          Tests are grouped by category for easier reference.
        </p>

      <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <article v-for="section in laboratorySections" :key="section.title"
          class="rounded-xl border border-brand-light/25 bg-brand-lighter/10 p-4">
          <h3 class="text-lg font-semibold text-brand-darker">{{ section.title }}</h3>
          <p class="mt-2 text-sm leading-6 text-brand-dark/80">{{ section.description }}</p>
          <ul class="mt-4 space-y-1 text-sm text-brand-dark sm:text-base">
            <li v-for="test in section.tests" :key="test"
              class="group relative rounded-md px-2 py-1 transition hover:bg-brand-lighter/20">
              {{ test }}
              <span
                class="pointer-events-none absolute left-2 top-full z-10 mt-2 hidden w-72 rounded-md bg-brand-darker px-3 py-2 text-xs text-white shadow-lg sm:group-hover:block">
                {{ getLabTestTooltip(test) }}
              </span>
            </li>
          </ul>
        </article>
      </div>
    </section>

    <section class="mt-8 grid gap-6 md:grid-cols-2">
      <article
        class="rounded-2xl border border-brand-light/25 bg-white p-5 shadow-[0_8px_24px_-16px_rgba(21,5,120,0.45)] sm:p-6">
        <h2 class="text-xl font-semibold text-brand-darker sm:text-2xl">Blood Chemistry Highlights</h2>
        <p class="mt-2 text-sm text-brand-dark/90 sm:text-base">
          Common chemistry tests used to assess blood sugar, kidney function, liver function, and electrolyte balance.
        </p>
        <ul class="mt-4 space-y-2 text-sm text-brand-dark sm:text-base">
          <li v-for="item in bloodChemistryHighlights" :key="item"
            class="group relative rounded-md px-2 py-1 transition hover:bg-brand-lighter/20">
            {{ item }}
            <span
              class="pointer-events-none absolute left-2 top-full z-10 mt-2 hidden w-72 rounded-md bg-brand-darker px-3 py-2 text-xs text-white shadow-lg sm:group-hover:block">
              {{ getBloodChemistryTooltip(item) }}
            </span>
          </li>
        </ul>
      </article>

      <article
        class="rounded-2xl border border-brand-light/25 bg-white p-5 shadow-[0_8px_24px_-16px_rgba(21,5,120,0.45)] sm:p-6">
        <h2 class="text-xl font-semibold text-brand-darker sm:text-2xl">Home Services</h2>
        <p class="mt-2 text-sm text-brand-dark/90 sm:text-base">
          Services that may be arranged for patients who require care at home.
        </p>
        <div class="mt-4 space-y-3">
          <article v-for="service in homeServices" :key="service.name"
            class="rounded-xl border border-brand-light/25 bg-brand-lighter/10 p-4">
            <h3 class="text-base font-semibold text-brand-darker">{{ service.name }}</h3>
            <p class="mt-2 text-sm leading-6 text-brand-dark/90">{{ service.description }}</p>
          </article>
        </div>

        <div class="mt-6 rounded-xl bg-brand-lighter/15 p-4 text-sm text-brand-dark sm:text-base">
          <p>
            <span class="font-semibold text-brand-darker">Home Service Contact:</span>
            {{ clinicInfo.homeServiceContact }}
          </p>
          <p class="mt-1">
            <span class="font-semibold text-brand-darker">Clinic Contact:</span>
            {{ clinicInfo.clinicContact }}
          </p>
          <p class="mt-1">
            <span class="font-semibold text-brand-darker">Facebook:</span>
            {{ clinicInfo.page }}
          </p>
          <p class="mt-1">
            <span class="font-semibold text-brand-darker">Address:</span>
            {{ clinicInfo.address }}
          </p>
        </div>
      </article>
    </section>
  </main>
</template>
