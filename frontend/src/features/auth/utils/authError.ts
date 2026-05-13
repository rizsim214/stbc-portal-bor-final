import axios from "axios";

type ApiErrorPayload = {
  message?: string;
  errors?: Record<string, string[]>;
};

type HandleAuthApiErrorOptions<TField extends string> = {
  error: unknown;
  fallbackMessage: string;
  fieldMap?: Partial<Record<string, TField>>;
  setFieldError?: (field: TField, message: string) => void;
};

export function handleAuthApiError<TField extends string>({
  error,
  fallbackMessage,
  fieldMap,
  setFieldError,
}: HandleAuthApiErrorOptions<TField>): string {
  if (!axios.isAxiosError(error)) {
    return fallbackMessage;
  }

  const payload = error.response?.data as ApiErrorPayload | undefined;

  if (payload?.errors && setFieldError) {
    Object.entries(payload.errors).forEach(([apiField, messages]) => {
      const firstMessage = messages?.[0];
      if (!firstMessage) return;

      const mappedField = fieldMap?.[apiField] ?? (apiField as TField);
      setFieldError(mappedField, firstMessage);
    });
  }

  return payload?.message ?? fallbackMessage;
}
