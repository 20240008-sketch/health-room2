import "./bootstrap";

import { createApp } from "vue";
import { createPinia } from "pinia";
import axios from "axios";

// Base component
import App from "./App.vue";

// Router
import router from "./router/index.js";

// Axios configuration
// Use current origin for API requests (works in dev and production)
axios.defaults.baseURL = window.location.origin;
axios.defaults.headers.common["Accept"] = "application/json";
axios.defaults.headers.common["Content-Type"] = "application/json";
axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

// Global Axios instance
window.axios = axios;

// Create Pinia store
const pinia = createPinia();

// Create Vue app
const app = createApp(App);

// Use plugins
app.use(pinia);
app.use(router);

// Mount app
app.mount("#app");
