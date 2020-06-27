<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Http\Request;
// use Illuminate\Contracts\Auth\CanResetPassword;

// use Illuminate\Auth\Passwords\CanResetPassword; 

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'isAdmin','semester','username','password','isLogin',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
 * Send the password reset notification.
 *
 * @param  string  $token
 * @return void
 */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new Notifications\PasswordResetNotification($token));
    }


    // public function AauthAccessToken()
    // {
    //     return $this->hasMany('\App\OauthAccessToken');
    // }
}


