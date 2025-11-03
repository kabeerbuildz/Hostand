<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'family_member',
        'profile',
        'address',
        'country',
        'state',
        'city',
        'zip_code',
        'property',
        'unit',
        'lease_start_date',
        'lease_end_date',
        'is_active',
    ];

    public function properties()
    {
        return $this->hasOne('App\Models\Property', 'id', 'property');
    }
    public function units()
    {
        return $this->hasOne('App\Models\PropertyUnit', 'id', 'unit');
    }

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function documents()
    {
        return $this->hasMany('App\Models\TenantDocument', 'tenant_id', 'id');
    }

    public function LeaseLeftDay()
    {
        if (!$this->lease_start_date || !$this->lease_end_date) {
            return '<span class="text-danger">' . __('Invalid lease dates') . '</span>';
        }

        $leaseStartDate = \Carbon\Carbon::parse($this->lease_start_date);
        $leaseEndDate = \Carbon\Carbon::parse($this->lease_end_date);
        $currentDate = \Carbon\Carbon::now();

        if ($currentDate->lt($leaseStartDate)) {
            return '<span class="text-warning">' . __('Lease has not started yet') . '</span>';
        }

        if ($currentDate->gt($leaseEndDate)) {
            return '<span class="text-danger">' . __('Lease has ended') . '</span>';
        }

        $daysLeft = $currentDate->diffInDays($leaseEndDate);

        return '<span class="text-success">' . $daysLeft . ' ' . __('Days Left') . '</span>';
    }
}
