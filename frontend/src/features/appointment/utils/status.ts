export function formatAppointmentStatus(value: string): string {
  return value
    .replaceAll("_", " ")
    .replace(/\b\w/g, (char) => char.toUpperCase());
}

export function getAppointmentStatusClasses(status: string): string {
  const normalized = status.trim().toLowerCase();

  if (normalized === "completed") {
    return "bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200";
  }

  if (normalized === "cancelled" || normalized === "no_show") {
    return "bg-red-50 text-red-700 ring-1 ring-red-200";
  }

  if (normalized === "scheduled" || normalized === "assigned") {
    return "bg-blue-50 text-blue-700 ring-1 ring-blue-200";
  }

  return "bg-amber-50 text-amber-700 ring-1 ring-amber-200";
}
