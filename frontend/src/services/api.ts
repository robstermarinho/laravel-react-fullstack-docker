import axios from "axios";
import { useAuthStore } from "../stores/authStore";

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  withCredentials: false,
  headers: {
    Accept: "application/json",
    "Content-Type": "application/json",
  },
});

api.interceptors.request.use(
  (config) => {
    const token = useAuthStore.getState().token;

    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

api.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error.response?.status;

    // Tratar 401/419 (expirado ou não autenticado)
    if ((status === 401 || status === 419) && typeof window !== "undefined") {
      // limpa credenciais locais
      const { clearCredentials, isRedirecting, setIsRedirecting } =
        useAuthStore.getState();
      clearCredentials?.();

      // evita loops
      if (!isRedirecting) {
        setIsRedirecting(true);
        const next = encodeURIComponent(
          window.location.pathname + window.location.search
        );
        window.location.href = `/login?next=${next}`;
      }
    }

    // if (
    //   error.response?.status === 401 &&
    //   window.location.pathname !== "/login"
    // ) {
    //   useAuthStore.getState().clearCredentials();
    //   window.location.href = "/login";
    // }
    return Promise.reject(error);
  }
);

export default api;
