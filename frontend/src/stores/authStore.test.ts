import { act, renderHook } from "@testing-library/react";
import { useAuthStore } from "./authStore";

// Mock localStorage
const localStorageMock = {
  getItem: vi.fn(),
  setItem: vi.fn(),
  removeItem: vi.fn(),
  clear: vi.fn(),
};
Object.defineProperty(window, "localStorage", {
  value: localStorageMock,
});

describe("Auth Store", () => {
  beforeEach(() => {
    // Clear all mocks before each test
    vi.clearAllMocks();

    // Reset the store to initial state
    useAuthStore.setState({
      user: null,
      token: null,
      isAuthenticated: false,
      numberOfLoginAttempts: 0,
      isRedirecting: false,
    });
  });

  describe("Initial State", () => {
    it("should have correct initial state", () => {
      const { result } = renderHook(() => useAuthStore());

      expect(result.current.user).toBeNull();
      expect(result.current.token).toBeNull();
      expect(result.current.isAuthenticated).toBe(false);
      expect(result.current.numberOfLoginAttempts).toBe(0);
      expect(result.current.isRedirecting).toBe(false);
    });
  });

  describe("setCredentials", () => {
    it("should set user and token correctly", () => {
      const { result } = renderHook(() => useAuthStore());

      const mockUser = {
        id: 1,
        name: "John Doe",
        email: "john@example.com",
      };
      const mockToken = "mock-jwt-token";

      act(() => {
        result.current.setCredentials(mockUser, mockToken);
      });

      expect(result.current.user).toEqual(mockUser);
      expect(result.current.token).toBe(mockToken);
      expect(result.current.isAuthenticated).toBe(true);
      expect(result.current.numberOfLoginAttempts).toBe(1);
    });

    it("should increment login attempts on subsequent calls", () => {
      const { result } = renderHook(() => useAuthStore());

      const mockUser = {
        id: 1,
        name: "John Doe",
        email: "john@example.com",
      };
      const mockToken = "mock-jwt-token";

      // First login
      act(() => {
        result.current.setCredentials(mockUser, mockToken);
      });
      expect(result.current.numberOfLoginAttempts).toBe(1);

      // Second login
      act(() => {
        result.current.setCredentials(mockUser, "new-token");
      });
      expect(result.current.numberOfLoginAttempts).toBe(2);
    });
  });

  describe("clearCredentials", () => {
    it("should clear user and token", () => {
      const { result } = renderHook(() => useAuthStore());

      // First set some credentials
      const mockUser = {
        id: 1,
        name: "John Doe",
        email: "john@example.com",
      };
      const mockToken = "mock-jwt-token";

      act(() => {
        result.current.setCredentials(mockUser, mockToken);
      });

      // Verify credentials are set
      expect(result.current.isAuthenticated).toBe(true);
      expect(result.current.user).toEqual(mockUser);
      expect(result.current.token).toBe(mockToken);

      // Clear credentials
      act(() => {
        result.current.clearCredentials();
      });

      // Verify credentials are cleared
      expect(result.current.user).toBeNull();
      expect(result.current.token).toBeNull();
      expect(result.current.isAuthenticated).toBe(false);
      // numberOfLoginAttempts should not be reset
      expect(result.current.numberOfLoginAttempts).toBe(1);
    });
  });

  describe("Persistence", () => {
    it("should persist user, token, and isAuthenticated to localStorage", () => {
      const { result } = renderHook(() => useAuthStore());

      const mockUser = {
        id: 1,
        name: "John Doe",
        email: "john@example.com",
      };
      const mockToken = "mock-jwt-token";

      act(() => {
        result.current.setCredentials(mockUser, mockToken);
      });

      // testing that the state is set correctly
      expect(result.current.user).toEqual(mockUser);
      expect(result.current.token).toBe(mockToken);
      expect(result.current.isAuthenticated).toBe(true);
    });
  });
});
