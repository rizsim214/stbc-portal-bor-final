<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabResult extends Model
{
    protected $fillable = [
        'appointment_id',
        'file_path',
        'result_data',
        'released_at',
    ];

    protected function casts(): array
    {
        return [
            'result_data' => 'array',
            'released_at' => 'datetime',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}
