import { create } from "zustand";
import { persist, createJSONStorage, devtools } from "zustand/middleware";

export interface User {
  id: number;
  name: string;
  email: string;
}

interface AuthState {
  user: User | null;
  token: string | null;
  isAuthenticated: boolean;
  setCredentials: (user: User, token: string) => void;
  clearCredentials: () => void;
  numberOfLoginAttempts: number;
  isRedirecting: boolean;
}

export const useAuthStore = create<AuthState>()(
  devtools(
    persist(
      (set) => ({
        user: null,
        token: null,
        isAuthenticated: false,
        numberOfLoginAttempts: 0,
        isRedirecting: false,
        setCredentials: (user: User, token: string) => {
          set(
            (state) => ({
              user,
              token,
              isAuthenticated: true,
              numberOfLoginAttempts: state.numberOfLoginAttempts + 1,
            }),
            false,
            "auth/setCredentials"
          );
        },
        clearCredentials: () =>
          set(
            () => ({
              user: null,
              token: null,
              isAuthenticated: false,
            }),
            false,
            "auth/clearCredentials"
          ),
        setIsRedirecting: (isRedirecting: boolean) => set({ isRedirecting }),
      }),
      {
        // Name of the storage
        name: "auth-storage",
        // Persist the state to localStorage
        storage: createJSONStorage(() => localStorage),

        // Partialize the state to only persist the user and token
        partialize: (state) => ({
          user: state.user,
          token: state.token,
          isAuthenticated: state.isAuthenticated,
        }),
        // sem partialize => tudo que for serializável é persistido
      }
    )
  )
);
