export interface BackendPatientUserRole {
  id: number;
  name: string;
}

export interface BackendPatientUser {
  id: number;
  name: string;
  email: string;
  account_status?: string | null;
  role?: BackendPatientUserRole | null;
  created_at?: string | null;
  updated_at?: string | null;
}

export interface PatientRow {
  id: number;
  name: string;
  email: string;
  role: string;
  registeredAt: string;
  status: "active" | "inactive";
}

export type PatientListSearchField = "name" | "email" | "role";
export type PatientListStatusFilter = "all" | "active" | "inactive";

export type PatientRecordRow = {
  id: number | string;
  date: string;
  title: string;
  summary: string;
  kind: "appointment" | "lab_result";
  releasedAt: string | null;
};
