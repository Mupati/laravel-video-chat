<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'about',
        'last_login_at',
        'last_login_ip',
        'avatar_path'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'last_login_ip',
        'avatar_path'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime'
    ];

    protected $appends = [
        'avatar_url',
    ];


    /*public function getAvatarUrlAttribute()
    {

        $exists = Storage::disk('s3')->exists($this->avatar_path);

        if ($exists) {
            return  Storage::disk('s3')->url($this->avatar_path);
        }
        return null;
    }
    */
  
  public function getAvatarUrlAttribute()
  {
      // Use the md5 hash of the user's email address to generate a Gravatar URL
      $emailHash = md5(strtolower(trim($this->attributes['email'])));

      // Gravatar URL format
      $gravatarUrl = "https://www.gravatar.com/avatar/{$emailHash}?s=200";

      return $gravatarUrl;
  }

  protected static function boot()
    
  {
    parent::boot();

    // Add an event listener to the 'created' event
    static::created(function ($user) {
      // Set the avatar url when the user is created
      $user->avatar_path = $user->getAvatarUrlAttribute();
      $user->save();
    });
  }


}
