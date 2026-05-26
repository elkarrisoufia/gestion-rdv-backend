<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class User extends Authenticatable {
    use HasApiTokens, Notifiable;
    protected $fillable = ['nom','prenom','email','password','role','telephone','is_active'];
    protected $hidden = ['password','remember_token'];
    protected $casts = ['email_verified_at'=>'datetime','password'=>'hashed','is_active'=>'boolean'];
    public function employe() { return $this->hasOne(Employe::class); }
    public function client() { return $this->hasOne(Client::class); }
}
