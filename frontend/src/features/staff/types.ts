export interface StaffScheduleDay {
  day_of_week: number;
  is_enabled: boolean;
  start_time: string | null;
  end_time: string | null;
}

export interface StaffScheduleResponseData {
  resource_id: number | null;
  days: StaffScheduleDay[];
  user?: {
    id: number;
    name: string;
    email: string;
    sub_role?: string | null;
  };
}
