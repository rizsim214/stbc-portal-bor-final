<script setup lang="ts">
import servicesOffered from "@/assets/resources/services offered.jpg";

const featuredServices = [
  {
    name: "Medical Check-up",
    tooltip: "General physician assessment for symptoms, vitals, and initial care plan.",
  },
  {
    name: "Clinical Laboratory Tests",
    tooltip: "Core diagnostic lab tests for blood, urine, stool, and infection screening.",
  },
  {
    name: "2D-Echo",
    tooltip: "Ultrasound-based heart scan to evaluate structure and pumping function.",
  },
  {
    name: "ECG",
    tooltip: "Records heart rhythm and electrical activity to detect cardiac abnormalities.",
  },
  {
    name: "Drug Testing",
    tooltip: "Screening test for common substances, often used for employment requirements.",
  },
];

const laboratorySections = [
  {
    title: "Chemistry",
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
    tests: [
      "CBC",
      "Hematocrit",
      "Hemoglobin",
      "Platelet Count",
      "WBC Differential Count",
      "Blood Typing",
      "RH Typing",
    ],
  },
  {
    title: "Clinical Microscopy",
    tests: ["Urinalysis", "UCG", "Fecalysis", "FOBT", "Kato Katz"],
  },
  {
    title: "Serology",
    tests: ["HBsAg", "Anti-HCV", "VDRL / RPR", "Typhidot", "H. pylori test", "Dengue Rapid Test"],
  },
  {
    title: "Special Examination",
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

const homeServices = ["Phlebotomy / Blood Extraction", "ECG"];

const clinicInfo = {
  address: "Sabate Bldg, Real St., Brgy. Songco, Borongan City, Eastern Samar",
  page: "ST.BENEDICTMEDICALPH",
  homeServiceContact: "09171164346",
  clinicContact: "0953-241-5158",
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

const getLabTestTooltip = (test: string): string =>
  labTestTooltips[test] ?? "Laboratory diagnostic test for clinical assessment.";

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

const homeServiceTooltips: Record<string, string> = {
  "Phlebotomy / Blood Extraction":
    "At-home blood sample collection for requested laboratory tests.",
  ECG: "At-home heart rhythm recording for basic cardiac screening.",
};

const getBloodChemistryTooltip = (item: string): string =>
  bloodChemistryTooltips[item] ?? "Blood chemistry test for diagnostic assessment.";

const getHomeServiceTooltip = (service: string): string =>
  homeServiceTooltips[service] ?? "Home-based clinical support service.";
</script>

<template>
  <main class="mx-auto w-full max-w-6xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
    <section
      class="rounded-2xl border border-brand-light/30 bg-linear-to-r from-white via-brand-lighter/15 to-white p-5 shadow-sm sm:p-8">
      <p class="text-xs font-semibold uppercase tracking-[0.14em] text-brand-highlight sm:text-sm sm:tracking-[0.18em]">STBC Medical Care</p>
      <h1 class="mt-3 text-2xl font-semibold tracking-tight text-brand-darker sm:text-4xl">Services</h1>
      <p class="mt-4 max-w-3xl text-sm leading-7 text-brand-dark sm:text-lg">
        Updated service information based on STBC service posters. Browse general services, complete laboratory test
        menu,
        blood chemistry offerings, and available home services.
      </p>
    </section>

    <section class="mt-8 grid gap-6 lg:grid-cols-[1.3fr_1fr]">
      <article
        class="overflow-hidden rounded-2xl border border-brand-light/30 bg-white shadow-[0_12px_28px_-14px_rgba(21,5,120,0.35)]">
        <div class="bg-linear-to-r from-brand-lighter/20 via-white to-brand-lighter/20 p-3 sm:p-4">
          <img :src="servicesOffered" alt="Services offered poster" class="w-full rounded-xl object-contain"
            loading="lazy" />
        </div>
      </article>

      <article
        class="rounded-2xl border border-brand-light/25 bg-white p-5 shadow-[0_8px_24px_-16px_rgba(21,5,120,0.45)] sm:p-6">
        <h2 class="text-xl font-semibold text-brand-darker sm:text-2xl">General Services Offered</h2>
        <ul class="mt-4 grid gap-2 text-brand-dark">
          <li v-for="service in featuredServices" :key="service.name"
            class="group relative rounded-lg bg-brand-lighter/10 px-3 py-2 text-sm sm:text-base">
            {{ service.name }}
            <span
              class="pointer-events-none absolute left-3 top-full z-10 mt-2 hidden w-64 rounded-md bg-brand-darker px-3 py-2 text-xs text-white shadow-lg sm:group-hover:block">
              {{ service.tooltip }}
            </span>
          </li>
        </ul>
      </article>
    </section>

    <section
      class="mt-8 rounded-2xl border border-brand-light/25 bg-white p-5 shadow-[0_8px_24px_-16px_rgba(21,5,120,0.45)] sm:p-6">
      <h2 class="text-2xl font-semibold text-brand-darker">Clinical Laboratory Tests</h2>
      <p class="mt-2 text-sm text-brand-dark/90 sm:text-base">Laboratory menu grouped by category from the clinic
        materials.</p>
      <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <article v-for="section in laboratorySections" :key="section.title"
          class="rounded-xl border border-brand-light/25 bg-brand-lighter/10 p-4">
          <h3 class="text-lg font-semibold text-brand-darker">{{ section.title }}</h3>
          <ul class="mt-3 space-y-1 text-sm text-brand-dark sm:text-base">
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
        <h2 class="text-2xl font-semibold text-brand-darker">Blood Chemistry Services</h2>
        <p class="mt-2 text-sm text-brand-dark/90 sm:text-base">Highlighted blood chemistry tests currently promoted by
          the clinic.</p>
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
        <h2 class="text-2xl font-semibold text-brand-darker">Home Service</h2>
        <p class="mt-2 text-sm text-brand-dark/90 sm:text-base">Available services for at-home support:</p>
        <ul class="mt-4 space-y-2 text-sm text-brand-dark sm:text-base">
          <li v-for="service in homeServices" :key="service"
            class="group relative rounded-md px-2 py-1 transition hover:bg-brand-lighter/20">
            {{ service }}
            <span
              class="pointer-events-none absolute left-2 top-full z-10 mt-2 hidden w-72 rounded-md bg-brand-darker px-3 py-2 text-xs text-white shadow-lg sm:group-hover:block">
              {{ getHomeServiceTooltip(service) }}
            </span>
          </li>
        </ul>

        <div class="mt-6 rounded-xl bg-brand-lighter/15 p-4 text-sm text-brand-dark sm:text-base">
          <p><span class="font-semibold text-brand-darker">Home Service Contact:</span> {{ clinicInfo.homeServiceContact
            }}</p>
          <p class="mt-1"><span class="font-semibold text-brand-darker">Clinic Contact:</span> {{
            clinicInfo.clinicContact }}</p>
          <p class="mt-1"><span class="font-semibold text-brand-darker">Facebook:</span> {{ clinicInfo.page }}</p>
        </div>
      </article>
    </section>
  </main>
</template>
