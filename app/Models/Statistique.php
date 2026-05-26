<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Statistique extends Model {
    protected $fillable = ['total_rdv','rdv_confirmes','emails_envoyes','date_statistique'];
}
