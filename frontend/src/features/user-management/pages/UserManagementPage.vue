<script setup lang="ts">
import DataTable from '@/shared/components/DataTable/DataTable.vue';
import { Button } from "@/shared/ui/button";
import { Plus } from "lucide-vue-next";
</script>

<template>
  <section class="rounded-xl border border-brand-light/30 bg-white p-5 shadow-[0_12px_28px_-18px_rgba(21,5,120,0.35)]">
    <div class="mb-4 flex items-center justify-between">
      <div>
        <h2 class="text-lg font-semibold text-brand-darker">User Administration</h2>
        <p class="text-xs text-brand-dark/80">Manage staff and admin access.</p>
      </div>
      <Button class="w-auto bg-brand-dark hover:bg-brand-darker">
        <Plus class="mr-1 h-4 w-4" />
        Add User
      </Button>
    </div>
    <!-- Create Search Bar & Filters here  -->
    <DataTable />
  </section>
</template>
