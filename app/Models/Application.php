<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'job_opening_id',
        'phone',
        'portfolio',
        'cover_letter',
        'resume',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jobOpening()
    {
        return $this->belongsTo(JobOpening::class, 'job_opening_id');
    }
}