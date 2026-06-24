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

  if (normalized === "releasing_lab_result") {
    return "bg-violet-50 text-violet-700 ring-1 ring-violet-200";
  }

  if (normalized === "awaiting_result") {
    return "bg-orange-50 text-orange-700 ring-1 ring-orange-200";
  }

  if (normalized === "checkup_ongoing") {
    return "bg-cyan-50 text-cyan-700 ring-1 ring-cyan-200";
  }

  if (normalized === "cancelled" || normalized === "no_show") {
    return "bg-red-50 text-red-700 ring-1 ring-red-200";
  }

  if (normalized === "scheduled" || normalized === "assigned") {
    return "bg-blue-50 text-blue-700 ring-1 ring-blue-200";
  }

  return "bg-amber-50 text-amber-700 ring-1 ring-amber-200";
}

export function getAppointmentStatusActionLabel(status: string): string {
  const normalized = status.trim().toLowerCase();

  switch (normalized) {
    case "assigned":
      return "Start Checkup / Laboratory";
    case "checkup_ongoing":
      return "Mark Awaiting Result";
    case "awaiting_result":
      return "Mark Releasing Lab Result";
    case "releasing_lab_result":
      return "Mark Completed";
    default:
      return "Update Status";
  }
}
