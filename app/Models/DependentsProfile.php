<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DependentsProfile extends Model
{
    protected $table = 'dependents_profiles';

    protected $fillable = [
        'user_id',
        'idp_user_id',
        'idp_role',
        'id_number',
        'first_name',
        'middle_name',
        'last_name',
        'suffix_name',
        'email',
        'birthday',
        'age',
        'sex',
        'civil_status',
        'street',
        'barangay',
        'municipality',
        'province',
        'home_address',
        'contact_no',
        'emergency_contact_name',
        'emergency_contact_no',
        'submitted_at',
    ];

    protected $casts = [
        'birthday' => 'date',
        'submitted_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
