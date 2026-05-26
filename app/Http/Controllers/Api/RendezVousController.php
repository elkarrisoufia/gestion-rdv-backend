<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\RendezVous;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RendezVousController extends Controller {

    public function index() {
        return response()->json(
            RendezVous::with(['client.user','employe.user'])
                ->orderBy('date_rdv')->orderBy('heure_rdv')->get()
        );
    }

    // ✅ FIXÉ — RDV du jour avec date exacte serveur
    public function today() {
        $today = Carbon::now()->toDateString();
        return response()->json(
            RendezVous::with(['client.user','employe.user'])
                ->whereDate('date_rdv', $today)
                ->orderBy('heure_rdv')->get()
        );
    }

    public function store(Request $request) {
        $request->validate([
            'client_id'  => 'required|exists:clients,id',
            'employe_id' => 'required|exists:employes,id',
            'date_rdv'   => 'required|date',
            'heure_rdv'  => 'required|string',
            'motif'      => 'required|string|max:255',
            'statut'     => 'nullable|in:en_attente,confirme,annule',
        ]);

        // ✅ Validation heure passée pour aujourd'hui
        $dateRdv  = Carbon::parse($request->date_rdv);
        $heureRdv = Carbon::parse($request->date_rdv . ' ' . $request->heure_rdv);
        $now      = Carbon::now();

        if ($dateRdv->isToday() && $heureRdv->lessThanOrEqualTo($now)) {
            return response()->json([
                'message' => '⚠️ L\'heure ' . $request->heure_rdv . ' est déjà passée. Choisissez un créneau futur.'
            ], 422);
        }

        // Validation doublon
        $doublon = RendezVous::where('employe_id', $request->employe_id)
            ->where('date_rdv', $request->date_rdv)
            ->where('heure_rdv', $request->heure_rdv)
            ->where('statut', '!=', 'annule')->exists();

        if ($doublon) {
            return response()->json([
                'message' => '⚠️ Ce conseiller a déjà un RDV à ' . $request->heure_rdv . '. Choisissez un autre créneau.'
            ], 422);
        }

        $rdv = RendezVous::create([
            'client_id'  => $request->client_id,
            'employe_id' => $request->employe_id,
            'date_rdv'   => $request->date_rdv,
            'heure_rdv'  => $request->heure_rdv,
            'motif'      => $request->motif,
            'statut'     => $request->statut ?? 'en_attente',
        ]);

        return response()->json($rdv->load(['client.user','employe.user']), 201);
    }

    public function update(Request $request, $id) {
        $rdv = RendezVous::findOrFail($id);
        $rdv->update($request->only(['client_id','employe_id','date_rdv','heure_rdv','motif','statut']));
        return response()->json($rdv->load(['client.user','employe.user']));
    }

    public function confirmer($id) {
        $rdv = RendezVous::findOrFail($id);
        $rdv->update(['statut' => 'confirme']);
        return response()->json(['message' => 'RDV confirmé.', 'rdv' => $rdv->load(['client.user','employe.user'])]);
    }

    public function annuler($id) {
        $rdv = RendezVous::findOrFail($id);
        $rdv->update(['statut' => 'annule']);
        return response()->json(['message' => 'RDV annulé.', 'rdv' => $rdv]);
    }

    public function destroy($id) {
        RendezVous::findOrFail($id)->delete();
        return response()->json(['message' => 'RDV supprimé.']);
    }

    // ✅ FIXÉ — Client voit UNIQUEMENT ses propres RDV
    public function mesRdv(Request $request) {
        $client = Client::where('user_id', $request->user()->id)->first();
        if (!$client) return response()->json([]);
        return response()->json(
            RendezVous::with(['employe.user'])
                ->where('client_id', $client->id)
                ->orderBy('date_rdv', 'desc')->get()
        );
    }

    // ✅ FIXÉ — Client crée RDV avec validation heure passée
    public function clientStore(Request $request) {
        $client = Client::where('user_id', $request->user()->id)->first();
        if (!$client) {
            return response()->json(['message' => 'Profil client introuvable. Contactez votre agence.'], 404);
        }

        $request->validate([
            'date_rdv'   => 'required|date|after_or_equal:today',
            'heure_rdv'  => 'required|string',
            'motif'      => 'required|string|max:255',
            'employe_id' => 'required|exists:employes,id',
        ], [
            'date_rdv.after_or_equal' => 'La date doit être aujourd\'hui ou dans le futur.',
            'employe_id.required'     => 'Veuillez sélectionner un conseiller.',
        ]);

        // ✅ Validation heure passée pour aujourd'hui
        $heureRdv = Carbon::parse($request->date_rdv . ' ' . $request->heure_rdv);
        $now = Carbon::now();

        if (Carbon::parse($request->date_rdv)->isToday() && $heureRdv->lessThanOrEqualTo($now)) {
            return response()->json([
                'message' => '⚠️ L\'heure ' . $request->heure_rdv . ' est déjà passée. Choisissez un créneau futur.'
            ], 422);
        }

        // Validation doublon
        $doublon = RendezVous::where('employe_id', $request->employe_id)
            ->where('date_rdv', $request->date_rdv)
            ->where('heure_rdv', $request->heure_rdv)
            ->where('statut', '!=', 'annule')->exists();

        if ($doublon) {
            return response()->json([
                'message' => '⚠️ Ce créneau est déjà réservé. Choisissez une autre heure.'
            ], 422);
        }

        $rdv = RendezVous::create([
            'client_id'  => $client->id,
            'employe_id' => $request->employe_id,
            'date_rdv'   => $request->date_rdv,
            'heure_rdv'  => $request->heure_rdv,
            'motif'      => $request->motif,
            'statut'     => 'en_attente',
        ]);

        return response()->json($rdv->load(['employe.user']), 201);
    }

    // ✅ Créneaux disponibles (filtrés selon heure actuelle pour aujourd'hui)
    public function creneaux(Request $request) {
        $date = $request->query('date', today()->toDateString());
        $emp  = $request->query('employe_id', 1);

        $pris = RendezVous::where('employe_id', $emp)
            ->where('date_rdv', $date)
            ->where('statut', '!=', 'annule')
            ->pluck('heure_rdv')->toArray();

        $tous = ['09:00','09:30','10:00','10:30','11:00','11:30',
                 '14:00','14:30','15:00','15:30','16:00','16:30'];

        $now = Carbon::now();
        $isToday = Carbon::parse($date)->isToday();

        $disponibles = array_filter($tous, function($creneau) use ($pris, $isToday, $date, $now) {
            if (in_array($creneau, $pris)) return false;
            // ✅ Filtrer les créneaux passés si aujourd'hui
            if ($isToday) {
                $heureCreneau = Carbon::parse($date . ' ' . $creneau);
                if ($heureCreneau->lessThanOrEqualTo($now)) return false;
            }
            return true;
        });

        return response()->json(array_values($disponibles));
    }
}
