<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Client extends Model {
    protected $fillable = ['user_id','cin','adresse','date_naissance','type_compte','is_vip','employe_id'];
    protected $casts = ['is_vip'=>'boolean'];
    public function user() { return $this->belongsTo(User::class); }
    public function employe() { return $this->belongsTo(Employe::class); }
    public function rendezVous() { return $this->hasMany(RendezVous::class); }
}
