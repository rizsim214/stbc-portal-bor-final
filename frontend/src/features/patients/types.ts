export interface BackendPatientUserRole {
  id: number;
  name: string;
}

export interface BackendPatientUser {
  id: number;
  name: string;
  email: string;
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
}

export type PatientListSearchField = "name" | "email" | "role";
