<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Email extends Model {
    protected $fillable = ['employe_id','client_id','sujet','contenu','type','statut'];
    public function employe() { return $this->belongsTo(Employe::class)->with('user'); }
    public function client() { return $this->belongsTo(Client::class)->with('user'); }
}
