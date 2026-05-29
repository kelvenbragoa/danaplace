<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstName',
        'lastName',
        'address',
        'code',
        'bi',
        'mobile',
        'cellphone',
        'email',
        'password',
        'signature',
        'area_id',
        'destination_id',
        'country_id',
        'province_id',
        'city_id',
        'user_status_id',
        'account_status_id',
        'role_id',
        'last_login_at',
        'last_login_ip',
        'status'
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
        'last_login_at' => 'datetime',
    ];

    public function role(){
        return $this->hasOne('App\Models\Role', 'id', 'role_id');
    }

    public function area(){
        return $this->hasOne('App\Models\Area', 'id', 'area_id');
    }

    public function destination(){
        return $this->hasOne('App\Models\Destination', 'id', 'destination_id');
    }

    public function country(){
        return $this->hasOne('App\Models\Country', 'id', 'country_id');
    }

    public function province(){
        return $this->hasOne('App\Models\Province', 'id', 'province_id');
    }

    public function city(){
        return $this->hasOne('App\Models\City', 'id', 'city_id');
    }

    public function user_status(){
        return $this->hasOne('App\Models\UserStatus', 'id', 'user_status_id');
    }

    public function account_status(){
        return $this->hasOne('App\Models\AccountStatus', 'id', 'account_status_id');
    }

    public function taskdone(){
        return $this->hasMany('App\Models\MeetingTask', 'user_id', 'id')->where('status_id',1);
    }

    public function tasknotdone(){
        return $this->hasMany('App\Models\MeetingTask', 'user_id', 'id')->where('status_id',2);
    }
}
