<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class RendezVous extends Model {
    protected $table = 'rendez_vouses';
    protected $fillable = ['client_id','employe_id','date_rdv','heure_rdv','motif','statut'];
    public function client() { return $this->belongsTo(Client::class)->with('user'); }
    public function employe() { return $this->belongsTo(Employe::class)->with('user'); }
}
