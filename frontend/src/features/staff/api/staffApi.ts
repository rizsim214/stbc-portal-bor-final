import { http } from "@/shared/api/http";
import type { StaffScheduleDay, StaffScheduleResponseData } from "../types";

type StaffAvailabilityResponse = {
  data: {
    is_available: boolean;
    resource_id: number | null;
  };
  message?: string;
};

type StaffScheduleResponse = {
  data: StaffScheduleResponseData;
  message?: string;
};

export const staffApi = {
  getMyAvailability() {
    return http.get<StaffAvailabilityResponse>("/staff/availability", {
      headers: {
        "X-Skip-Global-Loading": "true",
      },
    });
  },

  updateMyAvailability(isAvailable: boolean) {
    return http.patch<StaffAvailabilityResponse>("/staff/availability", {
      is_available: isAvailable,
    });
  },

  getMySchedule() {
    return http.get<StaffScheduleResponse>("/staff/schedule", {
      headers: {
        "X-Skip-Global-Loading": "true",
      },
    });
  },

  updateMySchedule(days: StaffScheduleDay[]) {
    return http.patch<StaffScheduleResponse>("/staff/schedule", {
      days,
    });
  },
};
