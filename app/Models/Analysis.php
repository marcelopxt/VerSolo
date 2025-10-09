<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Analysis extends Model
{
    use HasFactory;

    protected $fillable = ['report', 'status', 'consultation_id', 'protocol'];

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }
}