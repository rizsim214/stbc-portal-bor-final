import { http } from "@/shared/api/http";
import type { UpdatePasswordPayload, UpdatePasswordResponse } from "../types";

export const accountApi = {
  async updatePassword(payload: UpdatePasswordPayload) {
    return http.patch<UpdatePasswordResponse>("/users/me/password", payload);
  },
};
