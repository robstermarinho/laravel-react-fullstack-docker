import { useMutation, useQueryClient } from "@tanstack/react-query";
import { useAuthStore, type User } from "../stores/authStore";
import api from "../services/api";

interface LoginCredentials {
  email: string;
  password: string;
}

interface RegisterCredentials {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

interface AuthResponse {
  user: User;
  token: string;
}

export const useAuth = () => {
  const queryClient = useQueryClient();
  const { user, token, isAuthenticated, setCredentials, clearCredentials } =
    useAuthStore();

  const loginMutation = useMutation({
    mutationKey: ["login"],
    mutationFn: async (
      credentials: LoginCredentials
    ): Promise<AuthResponse> => {
      const response = await api.post("/api/login", credentials);
      return response.data;
    },
    onSuccess: (data) => {
      console.log("loginMutation onSuccess");
      console.log(data);
      setCredentials(data.user, data.token);
    },
  });

  const registerMutation = useMutation({
    mutationKey: ["register"],
    mutationFn: async (
      credentials: RegisterCredentials
    ): Promise<AuthResponse> => {
      const response = await api.post("/api/register", credentials);
      return response.data;
    },
    onSuccess: (data) => {
      console.log("registerMutation onSuccess");
      console.log(data);
      setCredentials(data.user, data.token);
    },
  });

  const logoutMutation = useMutation({
    mutationKey: ["logout"],
    mutationFn: async () => {
      if (token) {
        await api.post("/api/logout");
      }
    },
    onSuccess: () => {
      clearCredentials();

      // Clear tanstack query cache
      queryClient.clear();
    },
    onError: (error) => {
      console.log(error);
      clearCredentials();
      queryClient.clear();
    },
  });

  const login = async (email: string, password: string) => {
    return loginMutation.mutateAsync({ email, password });
  };

  const register = async (
    name: string,
    email: string,
    password: string,
    passwordConfirmation: string
  ) => {
    return registerMutation.mutateAsync({
      name,
      email,
      password,
      password_confirmation: passwordConfirmation,
    });
  };

  const logout = () => {
    logoutMutation.mutate();
  };

  return {
    user,
    token,
    isAuthenticated,
    login,
    register,
    logout,
    loading:
      loginMutation.isPending ||
      registerMutation.isPending ||
      logoutMutation.isPending,
    loginError: loginMutation.error,
    registerError: registerMutation.error,
    logoutError: logoutMutation.error,
  };
};
