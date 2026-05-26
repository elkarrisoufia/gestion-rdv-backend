<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Email;
use App\Models\Employe;
use App\Models\RendezVous;
use Carbon\Carbon;

class StatistiqueController extends Controller {

    public function index() {
        $m = now()->month;
        $a = now()->year;
        $total     = RendezVous::whereMonth('date_rdv',$m)->whereYear('date_rdv',$a)->count();
        $confirmes = RendezVous::whereMonth('date_rdv',$m)->whereYear('date_rdv',$a)->where('statut','confirme')->count();

        return response()->json([
            'total_rdv_mois'    => $total,
            'rdv_confirmes'     => $confirmes,
            'emails_envoyes'    => Email::whereMonth('created_at',$m)->whereYear('created_at',$a)->where('statut','envoye')->count(),
            'nouveaux_clients'  => Client::whereMonth('created_at',$m)->whereYear('created_at',$a)->count(),
            'taux_confirmation' => $total > 0 ? round(($confirmes/$total)*100) : 0,
        ]);
    }

    public function rdv() {
        $m = now()->month;
        $a = now()->year;
        $rdvParJour = RendezVous::selectRaw('DAY(date_rdv) as jour, COUNT(*) as rdv')
            ->whereMonth('date_rdv',$m)->whereYear('date_rdv',$a)
            ->groupByRaw('DAY(date_rdv)')->orderByRaw('DAY(date_rdv)')
            ->get()->map(fn($r) => ['jour'=>(string)$r->jour,'rdv'=>(int)$r->rdv]);

        $motifs = RendezVous::selectRaw('motif, COUNT(*) as count')
            ->groupBy('motif')->orderByDesc('count')
            ->get()->map(fn($r) => ['motif'=>$r->motif,'count'=>(int)$r->count]);

        return response()->json(['rdv_par_jour'=>$rdvParJour,'motifs_rdv'=>$motifs]);
    }

    public function employes() {
        $m = now()->month;
        $a = now()->year;
        $top = Employe::with('user')
            ->withCount(['rendezVous as rdv_count' => fn($q) => $q->whereMonth('date_rdv',$m)->whereYear('date_rdv',$a)])
            ->withCount(['emails as email_count'   => fn($q) => $q->whereMonth('created_at',$m)->whereYear('created_at',$a)])
            ->orderByDesc('rdv_count')->get()
            ->map(fn($e) => ['nom'=>$e->user->prenom.' '.$e->user->nom,'rdv'=>(int)$e->rdv_count,'emails'=>(int)$e->email_count]);

        $types = Email::selectRaw('type, COUNT(*) as count')
            ->groupBy('type')->orderByDesc('count')
            ->get()->map(fn($r) => ['type'=>$r->type,'count'=>(int)$r->count]);

        return response()->json(['top_employes'=>$top,'emails_par_type'=>$types]);
    }
}
