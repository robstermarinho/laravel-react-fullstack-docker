import { useState, useEffect } from "react";
import { useMutation, useQuery } from "@tanstack/react-query";
import { toast } from "react-toastify";
import api from "../services/api";

interface CacheStats {
  cache_exists: boolean;
  message: string;
  progress_percentage?: number;
}

interface CacheResponse {
  message: string;
  stats?: CacheStats;
  result?: unknown;
  success?: boolean;
  timestamp: string;
}

const CacheStats = () => {
  const [shouldRefetch, setShouldRefetch] = useState<boolean>(false);

  // Fetch cache stats
  const {
    data: cacheData,
    isLoading,
    isRefetching,
    error,
    refetch: refetchStats,
  } = useQuery<CacheResponse>({
    queryKey: ["cache-stats"],
    queryFn: async () => {
      const response = await api.get("/api/cache-stats");
      return response.data;
    },
    refetchInterval: shouldRefetch ? 2000 : false,
    staleTime: 0,
    refetchOnMount: true,
    refetchOnWindowFocus: true,
    refetchOnReconnect: true,
  });

  // Update refetch state when cache exists status changes
  useEffect(() => {
    if (cacheData?.stats?.cache_exists !== undefined) {
      setShouldRefetch(cacheData.stats.cache_exists);
    }
  }, [cacheData?.stats?.cache_exists]);

  // Add cache entry mutation
  const addCacheMutation = useMutation({
    mutationFn: async () => {
      const response = await api.post("/api/test-cache");
      return response.data;
    },
    onSuccess: (data) => {
      const message = data.result.message;
      if (data.result.status === "cache_miss") {
        toast.success(message);
        refetchStats();
      } else if (data.result.status === "cache_hit") {
        toast.warning(message);
      } else {
        toast.error(message);
        refetchStats();
      }
    },
    onError: (error: Error) => {
      toast.error(`Failed to add cache: ${error.message}`);
    },
  });

  // Clear cache mutation
  const clearCacheMutation = useMutation({
    mutationFn: async () => {
      const response = await api.delete("/api/clear-cache");
      return response.data;
    },
    onSuccess: (data) => {
      toast.success(data.message);
      refetchStats();
    },
    onError: (error: Error) => {
      toast.error(`Failed to clear cache: ${error.message}`);
    },
  });

  const handleReloadStats = () => {
    refetchStats();
  };

  const handleAddCache = () => {
    addCacheMutation.mutate();
  };

  const handleClearCache = () => {
    clearCacheMutation.mutate();
  };

  const stats = cacheData?.stats;
  const cacheExists = stats?.cache_exists || false;

  return (
    <div className="bg-white overflow-hidden shadow rounded-lg">
      <div className="px-4 py-5 sm:p-6">
        <div className="flex items-center justify-between mb-4">
          <h3 className="text-lg leading-6 font-medium text-gray-900">
            Cache Status
          </h3>
          <div className="flex items-center">
            {cacheExists ? (
              <div className="flex items-center text-green-600">
                <svg
                  className="h-5 w-5 mr-2"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={2}
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
                <span className="text-sm font-medium">Active</span>
              </div>
            ) : (
              <div className="flex items-center text-red-600">
                <svg
                  className="h-5 w-5 mr-2"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={2}
                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
                <span className="text-sm font-medium">Nothing in cache</span>
              </div>
            )}
          </div>
        </div>

        {/* Stats Display */}
        <div className="border border-gray-200 rounded-lg p-4 mb-4">
          {isLoading ? (
            <div className="flex items-center justify-center py-4">
              <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-600"></div>
              <span className="ml-2 text-sm text-gray-600">
                Loading cache stats...
              </span>
            </div>
          ) : error ? (
            <div className="text-red-600 text-sm">
              Error loading cache stats: {error.message}
            </div>
          ) : (
            <div className="space-y-3">
              {stats?.progress_percentage !== undefined && (
                <div>
                  <div className="flex justify-between items-center mb-1">
                    <span className="text-sm font-medium text-gray-500">
                      Progress:
                    </span>
                    <span className="text-sm text-gray-900">
                      {stats.progress_percentage.toFixed(2)}%
                    </span>
                  </div>
                  <div className="w-full bg-gray-200 rounded-full h-2">
                    <div
                      className="bg-blue-600 h-2 rounded-full transition-all duration-300"
                      style={{
                        width: `${Math.min(
                          100,
                          Math.max(0, stats.progress_percentage)
                        )}%`,
                      }}
                    ></div>
                  </div>
                </div>
              )}

              {cacheData?.timestamp && (
                <div className="flex justify-between items-center">
                  <span className="text-xs font-medium text-gray-400">
                    Last Check:
                  </span>
                  <span className="text-xs text-gray-400">
                    {new Date(cacheData.timestamp).toLocaleString()}
                  </span>
                </div>
              )}
            </div>
          )}
        </div>

        {/* Action Buttons */}
        <div className="flex space-x-3">
          <button
            onClick={handleAddCache}
            disabled={addCacheMutation.isPending}
            className="flex-1 inline-flex items-center justify-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed transition duration-150 ease-in-out"
          >
            <svg
              className="-ml-1 mr-2 h-4 w-4"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M12 6v6m0 0v6m0-6h6m-6 0H6"
              />
            </svg>
            {addCacheMutation.isPending ? "Adding..." : "Add Cache"}
          </button>

          <button
            onClick={handleReloadStats}
            disabled={isLoading || isRefetching}
            className="flex-1 inline-flex items-center justify-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition duration-150 ease-in-out"
          >
            {isLoading || isRefetching ? (
              <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-gray-600 mr-2"></div>
            ) : (
              <svg
                className="-ml-1 mr-2 h-4 w-4"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                />
              </svg>
            )}
            {isLoading || isRefetching ? "Loading..." : "Reload"}
          </button>

          <button
            onClick={handleClearCache}
            disabled={clearCacheMutation.isPending}
            className="flex-1 inline-flex items-center justify-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed transition duration-150 ease-in-out"
          >
            <svg
              className="-ml-1 mr-2 h-4 w-4"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
              />
            </svg>
            {clearCacheMutation.isPending ? "Clearing..." : "Clear Cache"}
          </button>
        </div>
      </div>
    </div>
  );
};

export default CacheStats;
