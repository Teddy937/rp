import { useToast } from "vue-toastification";
import { getDeviceInfo } from "@/helpers/helper";

const toast = useToast();
import axios from "axios";
import router from "@/router";
const BASE_URL = window.location.origin;

let BACKEND_URL = "";
let toastShown = false;

const session = { user: null, permissions: [], roles: [], modules: [] };

export const setSession = (data = {}) => {
  session.user = data.user ?? null;
  session.permissions = Array.isArray(data.permissions) ? data.permissions : [];
  session.roles = Array.isArray(data.roles) ? data.roles : [];
  session.modules = Array.isArray(data.modules) ? data.modules : [];
};

export const clearSession = () => {
  session.user = null;
  session.permissions = [];
  session.roles = [];
  session.modules = [];
};

export const getSession = () => session;

const http = () => {
  const apiClient = axios.create({
    baseURL: resolveUrl(BASE_URL),
    withCredentials: true,
    validateStatus: (status) => status >= 200 && status < 300,
  });

  // --- REQUEST INTERCEPTOR TO ADD DEVICE INFO ---
  apiClient.interceptors.request.use((config) => {
    config.headers["X-Device-Info"] = JSON.stringify(getDeviceInfo());
    return config;
  });

  apiClient.interceptors.response.use(
    (response) => response,
    (error) => {
      console.log("Error", error);
      if (error.response && error.response.status == 401) {
        clearSession();
        if (!toastShown) {
          toast.error(
            "Your session has expired or ip has changed!. Please log in again.",
          );
          toastShown = true;
          setTimeout(() => {
            toastShown = false;
          }, 5000);
        }
        const currentRoute = router.currentRoute.value;
        router.push({
          name: "auth.sign-in",
          query: { redirectedFrom: currentRoute.fullPath },
        });
      }
      return Promise.reject(error);
    },
  );
  return apiClient;
};

export const resolveUrl = (origin) => {
  switch (origin) {
    case "https://test.favitech.co.ke":
      return "https://backend.test.favitech.co.ke/api/v1/";
      break;
    case "https://portal.favitech.co.ke":
      return "https://backend.favitech.co.ke/api/v1/";
      break;
    case "https://loansystem.demulla.co.ke":
      // return "https://www.backend.portal.demulla.co.ke/api/v1/";
      return "https://backend.portal.demulla.co.ke/api/v1/";
      break;
    case "http://localhost:5173":
      return "http://localhost:8000/api/v1/";
    case "https://sso.demulla.co.ke":
      return "https://backend.portal.demulla.co.ke/api/v1/";
      break;
    case "http://sso.demulla.co.ke":
      return "https://backend.portal.demulla.co.ke/api/v1/";
      break;
    default:
      return "https://backend.favitech.co.ke/api/v1/";
      break;
  }
};
export default http;
