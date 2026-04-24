import { createRouter, createWebHistory } from "vue-router";
import HomePage from "@/app/pages/HomePage.vue";

const routes = [
  {
    path: "/",
    name: "home",
    component: HomePage,
  },
];

export const router = createRouter({
  history: createWebHistory(),
  routes,
});
