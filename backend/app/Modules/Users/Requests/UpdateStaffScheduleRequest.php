<?php

namespace App\Modules\Users\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateStaffScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'days' => ['required', 'array', 'size:7'],
            'days.*.day_of_week' => ['required', 'integer', 'min:0', 'max:6'],
            'days.*.is_enabled' => ['required', 'boolean'],
            'days.*.start_time' => ['nullable', 'date_format:H:i'],
            'days.*.end_time' => ['nullable', 'date_format:H:i'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $days = $this->input('days', []);
            $dayValues = array_map(
                static fn(array $day): int => (int) ($day['day_of_week'] ?? -1),
                is_array($days) ? $days : [],
            );

            sort($dayValues);

            if ($dayValues !== [0, 1, 2, 3, 4, 5, 6]) {
                $validator->errors()->add('days', 'Each day of the week must be provided exactly once.');
            }

            foreach ($days as $index => $day) {
                $enabled = (bool) ($day['is_enabled'] ?? false);
                $startTime = $day['start_time'] ?? null;
                $endTime = $day['end_time'] ?? null;

                if (!$enabled) {
                    continue;
                }

                if (!$startTime || !$endTime) {
                    $validator->errors()->add("days.{$index}.start_time", 'Enabled days require start and end times.');
                    continue;
                }

                if ($startTime >= $endTime) {
                    $validator->errors()->add("days.{$index}.end_time", 'End time must be later than start time.');
                }
            }
        });
    }

    /**
     * @return array<int, array{day_of_week:int,is_enabled:bool,start_time:?string,end_time:?string}>
     */
    public function days(): array
    {
        return collect($this->array('days'))
            ->map(fn(array $day): array => [
                'day_of_week' => (int) $day['day_of_week'],
                'is_enabled' => (bool) $day['is_enabled'],
                'start_time' => $day['start_time'] !== null ? $day['start_time'].':00' : null,
                'end_time' => $day['end_time'] !== null ? $day['end_time'].':00' : null,
            ])
            ->sortBy('day_of_week')
            ->values()
            ->all();
    }
}
