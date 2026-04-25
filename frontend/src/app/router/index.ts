import { createRouter, createWebHistory } from "vue-router";
import HomePage from "@/app/pages/HomePage.vue";
import CalendarPage from "@/app/pages/CalendarPage.vue";

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
