<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;
use Lab404\Impersonate\Models\Impersonate;


class User extends Authenticatable implements MustVerifyEmail
{
    use HasRoles;
    use Notifiable;
    use Impersonate;


    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'type',
        'phone_number',
        'profile',
        'lang',
        'subscription',
        'subscription_expire_date',
        'parent_id',
        'is_active',
    ];


    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function canImpersonate()
    {
        // Example: Only admins can impersonate others
        return $this->type == 'super admin';
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail);
    }

    public function totalUser()
    {
        return User::whereNotIn('type', ['tenant', 'maintainer'])->where('parent_id', $this->id)->count();
    }
    public function totalTenant()
    {
        return User::where('type', 'tenant')->where('parent_id', $this->id)->count();
    }

    public function getNameAttribute()
    {
        return ucfirst($this->first_name) . ' ' . ucfirst($this->last_name);
    }


    public function totalContact()
    {
        return Contact::where('parent_id', '=', parentId())->count();
    }

    public function roleWiseUserCount($role)
    {
        return User::where('type', $role)->where('parent_id', parentId())->count();
    }
    public static function getDevice($user)
    {
        $mobileType = '/(?:phone|windows\s+phone|ipod|blackberry|(?:android|bb\d+|meego|silk|googlebot) .+? mobile|palm|windows\s+ce|opera mini|avantgo|mobilesafari|docomo)/i';
        $tabletType = '/(?:ipad|playbook|(?:android|bb\d+|meego|silk)(?! .+? mobile))/i';
        if (preg_match_all($mobileType, $user)) {
            return 'mobile';
        } else {
            if (preg_match_all($tabletType, $user)) {
                return 'tablet';
            } else {
                return 'desktop';
            }
        }
    }

    public function totalProperty()
    {
        return Property::where('parent_id', parentId())->count();
    }
    public function totalUnit()
    {
        return PropertyUnit::where('parent_id', parentId())->count();
    }

    public function subscriptions()
    {
        return $this->hasOne('App\Models\Subscription', 'id', 'subscription');
    }

    public function tenants()
    {
        return $this->hasOne('App\Models\Tenant', 'user_id', 'id');
    }

    public function maintainer()
    {
        return $this->hasOne('App\Models\Maintainer', 'user_id', 'id');
    }

    public static $systemModules = [
        'user',
        'property',
        'unit',
        'tenant',
        'invoice',
        'maintainer',
        'maintenance request',
        'expense',
        'types',
        'contact',
        'note',
        'logged history',
        'pricing transation',
        'settings',
    ];

    public static function parentData()
    {
        return User::find(parentId());
    }
}
