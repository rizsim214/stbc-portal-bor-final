import { computed } from "vue";
import { useMutation, useQuery, useQueryClient } from "@tanstack/vue-query";
import { staffApi } from "../api/staffApi";

function getErrorMessage(error: unknown, fallback: string): string {
  if (error instanceof Error && error.message) {
    return error.message;
  }

  return fallback;
}

export function useStaffAvailability(enabled: boolean) {
  const queryClient = useQueryClient();

  const availabilityQuery = useQuery({
    queryKey: ["staff", "availability"],
    enabled,
    queryFn: async () => {
      const { data } = await staffApi.getMyAvailability();
      return data.data;
    },
  });

  const updateAvailabilityMutation = useMutation({
    mutationFn: async (nextAvailability: boolean) => {
      const { data } = await staffApi.updateMyAvailability(nextAvailability);
      return data.data;
    },
    onSuccess: (payload) => {
      queryClient.setQueryData(["staff", "availability"], payload);
    },
  });

  return {
    availability: computed(() => availabilityQuery.data.value ?? null),
    isLoadingAvailability: computed(
      () => availabilityQuery.isPending.value || availabilityQuery.isFetching.value,
    ),
    availabilityError: computed(() => {
      if (!availabilityQuery.isError.value) {
        return "";
      }

      return getErrorMessage(
        availabilityQuery.error.value,
        "Failed to load staff availability.",
      );
    }),
    isUpdatingAvailability: computed(() => updateAvailabilityMutation.isPending.value),
    updateAvailabilityError: computed(() => {
      if (!updateAvailabilityMutation.isError.value) {
        return "";
      }

      return getErrorMessage(
        updateAvailabilityMutation.error.value,
        "Failed to update staff availability.",
      );
    }),
    setAvailability: async (nextAvailability: boolean) => {
      await updateAvailabilityMutation.mutateAsync(nextAvailability);
    },
  };
}
