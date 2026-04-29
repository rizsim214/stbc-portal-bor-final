import { createRouter, createWebHistory } from "vue-router";
import HomePage from "@/pages/HomePage.vue";
import AppointmentPage from "@/pages/AppointmentPage.vue";
import ServicesPage from "@/pages/ServicesPage.vue";
import AboutPage from "@/pages/AboutPage.vue";

const routes = [
  {
    path: "/",
    name: "home",
    component: HomePage,
  },
  {
    path: "/about",
    name: "about",
    component: AboutPage,
  },
  {
    path: "/services",
    name: "services",
    component: ServicesPage,
  },
  {
    path: "/appointments",
    name: "appointments",
    component: AppointmentPage,
  },
];

export const router = createRouter({
  history: createWebHistory(),
  routes,
});
