<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'user_id',
        'appointment_type_id',
        'start_time',
        'end_time',
        'status',
        'notes'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function type()
    {
        return $this->belongsTo(AppointmentType::class, 'appointment_type_id');
    }

    public function resources()
    {
        return $this->belongsToMany(Resource::class, 'appointment_resources');
    }

    public function labResult()
    {
        return $this->hasOne(LabResult::class);
    }
}
