<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Services\UsersService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'status',
        'password',
        'password_reset',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected static function boot()
    {
        parent::boot();

        // self::creating(function ($model) {
        //     $avatar = (new UsersService)->createAvatar($model->username);
        //     $model['avatar'] = $avatar;
        // });

        static::deleting(function ($user) {
            $user->revokePermissionTo($user->getAllPermissions());
        });
    }

    public function logs() 
    {
        return $this->hasMany(Activity::class, 'causer_id');
    }


    public function isAdmin() 
    {
        return $this->role == 'admin';
    }
    
    public function isSuperAdmin() 
    {
        return $this->role == 'super_admin';
    }

    public function superUser() 
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    public function getStatusBadgeAttribute()
    {
    	$badges = [
    		'1' => 'primary',
    		'0' => 'danger',
    	];

    	return $badges[$this->status];
    }
    
    public function getStatusNameAttribute()
    {
    	$name = [
    		'1' => 'Active',
    		'0' => 'Inactive',
    	];

    	return $name[$this->status];
    }
    
    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->whereAny([
                'name',
                'username',
                'email',
            ], 'LIKE', "%$search%");
        }
        return $query;
    }

    public function getRoleNameAttribute()
    {
        return $this->getRoleNames()[0];
    }

    public function getIsUserAttribute() 
    {
        return $this->role_name == 'user';
    }
    
    public function getIsAccountsAttribute() 
    {
        return $this->role_name == 'accounts';
    }

    public function getIsAdminAttribute() 
    {
        return $this->role_name == 'admin';
    }

    public function getSuperAdminAttribute() 
    {
        return $this->role_name == 'super admin';
    }

    public function getSuperUserAttribute() 
    {
        return in_array($this->role_name, ['super admin', 'admin']);
    }

    public function getUserAvatarAttribute() 
    {
        return asset("storage/uploads/avatar/$this->avatar");
    }
    
    public function loginSecurity()
    {
        return $this->hasOne(LoginSecurity::class);
    }
    
    public function getSecurityStatusBadgeAttribute()
    {
    	$badges = [
    		'1' => 'primary',
    		'' => 'danger',
    		'0' => 'warning',
    	];

        return $badges[$this->getGoogleSecurityStatus()];
    }

    public function getSecurityStatusAttribute()
    {
    	$name = [
    		'1' => 'Active',
            ''  => 'Not Configured',
            '0'  => 'Inactive'
    	];

    	return $name[$this->getGoogleSecurityStatus()];
    }
    
    private function getGoogleSecurityStatus() 
    {
        $status = LoginSecurity::whereUserId($this->id)->first();
        if(isset($status)) {
            return $status->google2fa_enable;
        }
            return null;
    }
}
