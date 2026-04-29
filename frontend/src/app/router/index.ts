import { createRouter, createWebHistory } from "vue-router";
import HomePage from "@/pages/HomePage.vue";
import CalendarPage from "@/pages/CalendarPage.vue";

const routes = [
  {
    path: "/",
    name: "home",
    component: HomePage,
  },
  {
    path: "/calendar",
    name: "calendar",
    component: CalendarPage,
  },
];

export const router = createRouter({
  history: createWebHistory(),
  routes,
});
