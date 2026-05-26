<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Employe extends Model {
    protected $fillable = ['user_id','matricule','poste','agence'];
    public function user() { return $this->belongsTo(User::class); }
    public function clients() { return $this->hasMany(Client::class); }
    public function rendezVous() { return $this->hasMany(RendezVous::class); }
    public function emails() { return $this->hasMany(Email::class); }
}
