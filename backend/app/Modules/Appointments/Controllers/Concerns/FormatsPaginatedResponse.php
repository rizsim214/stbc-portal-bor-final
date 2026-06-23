<?php

namespace App\Modules\Appointments\Controllers\Concerns;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

trait FormatsPaginatedResponse
{
    /**
     * @return array<string, mixed>
     */
    protected function paginatedResponse(LengthAwarePaginator $paginator): array
    {
        return [
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }
}
