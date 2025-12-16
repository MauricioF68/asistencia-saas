<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'dni',
        'name',
        'last_name',
        'birth_date',
        'grade_id',
        'section_id',
        'shift',
        'parent_name',
        'parent_phone',
        'status',
    ];

    protected $casts = [
        'birth_date' => 'date', // Para manejarlo como objeto Carbon (fechas)
    ];

    // Relación: Un alumno pertenece a un Colegio
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
    
    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
}