<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'type',
        'country',
        'state',
        'city',
        'zip_code',
        'address',
        'piano',
        'staircase',
        'access_other',
        'sign_detail',
        'opening_type',
        'street_code',
        'door_code',
        'key_description',
        'sofa_bed',
        'bnb_unit_type',
        'bnb_unit_count',
        'parent_id',
        'id',
        'is_active',
        'location_type',
        
    ];

    public static $Type=[
        'own_property'=> 'Own Property',
        'lease_property'=>'Lease Property',
    ];

    public function thumbnail(){
        return $this->hasOne('App\Models\PropertyImage','property_id','id')->where('type','thumbnail');
    }

    public function propertyImages(){
        return $this->hasMany('App\Models\PropertyImage','property_id','id')->where('type','extra');
    }

    public function totalUnit(){
        return $this->hasMany('App\Models\PropertyUnit','property_id','id')->count();
    }
    public function totalUnits(){
        return $this->hasMany('App\Models\PropertyUnit','property_id','id');
    }
    public function totalRoom(){
        $units= $this->totalUnits;

        $totalUnit=0;
        foreach($units as $unit){
            $totalUnit+=$unit->bedroom;

        }
        return $totalUnit;
    }

    public function maintenanceRequests(){
        return $this->hasMany('App\Models\MaintenanceRequest','property_id','id');
    }

    public function owner(){
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function scopeForUser($query, $user)
    {
        if ($user->type === 'super admin') {
            return $query; // Admin sees all properties
        }
        
        return $query->where('user_id', $user->id);
    }
}
