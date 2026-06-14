<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class DentalPatient extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'patient_code',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'date_of_birth',
        'age',
        'mobile_no',
        'alternate_mobile_no',
        'email',
        'address',
        'city',
        'state',
        'pincode',
        'occupation',
        'referred_by',
        'status',
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
            'date_of_birth' => 'date',
            'age' => 'integer',
        ];
    }

    public function medicalHistory(): HasOne
    {
        return $this->hasOne(DentalPatientMedicalHistory::class, 'patient_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(DentalAppointment::class, 'patient_id');
    }

    public function latestAppointment(): HasOne
    {
        return $this->hasOne(DentalAppointment::class, 'patient_id')
            ->ofMany([
                'appointment_date' => 'max',
                'id' => 'max',
            ]);
    }
}
