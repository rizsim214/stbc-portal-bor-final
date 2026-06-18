import { createApp } from "vue";
import "@/shared/styles/style.css";
import App from "@/app/App.vue";
import { pinia } from "@/app/store";
import { router } from "@/app/router";
import { VueQueryPlugin } from "@tanstack/vue-query";
import { queryClient } from "@/app/queryClient";

const app = createApp(App);

app.use(pinia);
app.use(router);
app.use(VueQueryPlugin, { queryClient });

app.mount("#app");
