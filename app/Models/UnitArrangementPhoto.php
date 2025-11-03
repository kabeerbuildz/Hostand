<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitArrangementPhoto extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'property_unit_id',
        'image',
        'description'
    ];

    public function propertyUnit()
    {
        return $this->belongsTo('App\Models\PropertyUnit', 'property_unit_id', 'id');
    }
} 