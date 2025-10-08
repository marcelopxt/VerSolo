<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Hash;

class Employee extends Authenticatable
{
    use HasFactory;

    protected $fillable = ['name', 'password', 'company_id'];

    protected $hidden = ['password'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }
    protected function password(): Attribute
    {
        return Attribute::make(
            set: fn($value) => Hash::make($value),
        );
    }
}
