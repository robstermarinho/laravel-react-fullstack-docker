import { useMutation, useQueryClient } from '@tanstack/react-query';
import { useAppDispatch, useAppSelector } from '../store/hooks';
import { setCredentials, clearCredentials, type User } from '../store/slices/authSlice';
import api from '../services/api';

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
  const dispatch = useAppDispatch();
  const queryClient = useQueryClient();
  const { user, token, isAuthenticated } = useAppSelector((state) => state.auth);

  const loginMutation = useMutation({
    mutationFn: async (credentials: LoginCredentials): Promise<AuthResponse> => {
      const response = await api.post('/api/login', credentials);
      return response.data;
    },
    onSuccess: (data) => {
      dispatch(setCredentials({ user: data.user, token: data.token }));
    },
  });

  const registerMutation = useMutation({
    mutationFn: async (credentials: RegisterCredentials): Promise<AuthResponse> => {
      const response = await api.post('/api/register', credentials);
      return response.data;
    },
    onSuccess: (data) => {
      dispatch(setCredentials({ user: data.user, token: data.token }));
    },
  });

  const logoutMutation = useMutation({
    mutationFn: async () => {
      if (token) {
        await api.post('/api/logout');
      }
    },
    onSuccess: () => {
      dispatch(clearCredentials());
      queryClient.clear();
    },
    onError: () => {
      dispatch(clearCredentials());
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
    loading: loginMutation.isPending || registerMutation.isPending || logoutMutation.isPending,
    loginError: loginMutation.error,
    registerError: registerMutation.error,
    logoutError: logoutMutation.error,
  };
};