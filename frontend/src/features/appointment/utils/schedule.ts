import { CalendarDate, getLocalTimeZone } from "@internationalized/date";

function padTime(value: number): string {
  return String(value).padStart(2, "0");
}

export function dateToCalendarDate(value: Date): CalendarDate {
  return new CalendarDate(
    value.getFullYear(),
    value.getMonth() + 1,
    value.getDate(),
  );
}

export function toDateInput(value: CalendarDate): string {
  return `${value.year}-${padTime(value.month)}-${padTime(value.day)}`;
}

export function toSqlDateTime(value: CalendarDate, time: string): string {
  const [hoursText, minutesText] = time.split(":");
  const date = value.toDate(getLocalTimeZone());

  date.setHours(Number(hoursText), Number(minutesText), 0, 0);

  return `${date.getFullYear()}-${padTime(date.getMonth() + 1)}-${padTime(date.getDate())} ${padTime(date.getHours())}:${padTime(date.getMinutes())}:00`;
}

export function addMinutesToSqlDateTime(
  dateTime: string,
  minutesToAdd: number,
): string {
  const date = new Date(dateTime.replace(" ", "T"));

  date.setMinutes(date.getMinutes() + minutesToAdd);

  return `${date.getFullYear()}-${padTime(date.getMonth() + 1)}-${padTime(date.getDate())} ${padTime(date.getHours())}:${padTime(date.getMinutes())}:00`;
}

export function formatTimeValue(value: string): string {
  const [hoursText, minutes] = value.split(":");
  const hours = Number(hoursText);
  const suffix = hours >= 12 ? "PM" : "AM";
  const hour12 = hours % 12 || 12;
  return `${hour12}:${minutes} ${suffix}`;
}

export function formatSelectedDate(value?: CalendarDate): string {
  if (!value) return "No date selected";
  return value.toDate(getLocalTimeZone()).toLocaleDateString("en-US", {
    weekday: "short",
    month: "long",
    day: "numeric",
    year: "numeric",
  });
}

export function formatScheduleRange(start?: string, end?: string): string {
  if (!start || !end) return "-";

  const startDate = new Date(start);
  const endDate = new Date(end);
  const startLabel = startDate.toLocaleString("en-US", {
    month: "long",
    day: "numeric",
    year: "numeric",
    hour: "numeric",
    minute: "2-digit",
  });
  const endLabel = endDate.toLocaleTimeString("en-US", {
    hour: "numeric",
    minute: "2-digit",
  });

  return `${startLabel} - ${endLabel}`;
}

export function extractTimeValue(dateTime: string): string {
  const date = new Date(dateTime.replace(" ", "T"));
  return `${padTime(date.getHours())}:${padTime(date.getMinutes())}`;
}
