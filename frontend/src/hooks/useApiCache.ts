import { useState, useEffect } from "react";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { toast } from "react-toastify";
import api from "../services/api";

// Types
export interface CacheStats {
  cache_exists: boolean;
  message: string;
  progress_percentage?: number;
}

export interface CacheResponse {
  message: string;
  stats?: CacheStats;
  result?: {
    message: string;
    status: string;
  };
  success?: boolean;
  timestamp: string;
}

export interface UseCacheStatsOptions {
  refetchInterval?: number | false;
}

// API Cache Stats Query Hook
export const useApiCacheStats = (options: UseCacheStatsOptions = {}) => {
  const [shouldRefetch, setShouldRefetch] = useState<boolean>(false);

  const query = useQuery<CacheResponse>({
    queryKey: ["cache-stats"],
    queryFn: async () => {
      const response = await api.get("/api/cache-stats");
      return response.data;
    },
    refetchInterval: shouldRefetch ? (options.refetchInterval ?? 2000) : false,
    staleTime: 0,
    refetchOnMount: true,
    refetchOnWindowFocus: true,
    refetchOnReconnect: true,
  });

  // Update refetch state when cache exists status changes
  useEffect(() => {
    if (query.data?.stats?.cache_exists !== undefined) {
      setShouldRefetch(query.data.stats.cache_exists);
    }
  }, [query.data?.stats?.cache_exists]);

  return {
    ...query,
    shouldRefetch,
    stats: query.data?.stats,
    cacheExists: query.data?.stats?.cache_exists || false,
  };
};

// Add API Cache Mutation Hook
export const useAddApiCache = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (): Promise<CacheResponse> => {
      const response = await api.post("/api/test-cache");
      return response.data;
    },
    onSuccess: (data) => {
      const message = data.result?.message || "Cache operation completed";
      const status = data.result?.status;

      if (status === "cache_miss") {
        toast.success(message);
      } else if (status === "cache_hit") {
        toast.warning(message);
      } else {
        toast.success(message);
      }

      // Invalidate and refetch cache stats
      queryClient.invalidateQueries({ queryKey: ["cache-stats"] });
    },
    onError: (error: Error) => {
      toast.error(`Failed to add cache: ${error.message}`);
    },
  });
};

// Clear API Cache Mutation Hook
export const useClearApiCache = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (): Promise<CacheResponse> => {
      const response = await api.delete("/api/clear-cache");
      return response.data;
    },
    onSuccess: (data) => {
      toast.success(data.message);
      // Invalidate and refetch cache stats
      queryClient.invalidateQueries({ queryKey: ["cache-stats"] });
    },
    onError: (error: Error) => {
      toast.error(`Failed to clear cache: ${error.message}`);
    },
  });
};

// Combined hook for all API cache operations
export const useApiCache = (options: UseCacheStatsOptions = {}) => {
  const cacheStats = useApiCacheStats(options);
  const addCache = useAddApiCache();
  const clearCache = useClearApiCache();

  return {
    // Stats query
    ...cacheStats,
    
    // Mutations
    addCache,
    clearCache,
    
    // Helper methods
    handleAddCache: () => addCache.mutate(),
    handleClearCache: () => clearCache.mutate(),
    handleRefreshStats: () => cacheStats.refetch(),
  };
};