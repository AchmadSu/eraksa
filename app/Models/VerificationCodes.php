<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class VerificationCodes extends Model implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['user_id', 'otp', 'expired_at', 'status'];
    protected $hidden = ['remember_token'];

    public function getJWTIdentifier()
    {
     return $this->getKey();   
    }
    public function getJWTCustomClaims()
    {
        return [];
    }
}