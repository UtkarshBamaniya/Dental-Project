<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class DentalAppointment extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'patient_id',
        'appointment_no',
        'appointment_date',
        'appointment_time',
        'doctor_id',
        'visit_type',
        'appointment_type',
        'chief_complaint',
        'problem_area',
        'tooth_no',
        'priority',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(DentalPatient::class, 'patient_id');
    }

    public function billing(): HasOne
    {
        return $this->hasOne(DentalAppointmentBilling::class, 'appointment_id');
    }
}
