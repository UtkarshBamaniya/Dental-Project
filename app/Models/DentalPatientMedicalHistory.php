<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DentalPatientMedicalHistory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'patient_id',
        'blood_group',
        'diabetes',
        'blood_pressure',
        'heart_disease',
        'allergy',
        'allergy_details',
        'current_medicine',
        'previous_dental_treatment',
        'pregnancy_status',
        'other_medical_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'diabetes' => 'boolean',
            'blood_pressure' => 'boolean',
            'heart_disease' => 'boolean',
            'allergy' => 'boolean',
            'pregnancy_status' => 'boolean',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(DentalPatient::class, 'patient_id');
    }
}
