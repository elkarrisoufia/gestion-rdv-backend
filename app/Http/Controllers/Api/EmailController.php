<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Email;
use App\Models\Employe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmailController extends Controller {
    public function index() {
        return response()->json(Email::with(['client.user','employe.user'])->orderBy('created_at','desc')->get());
    }

    public function store(Request $request) {
        $request->validate(['client_id'=>'required|exists:clients,id','sujet'=>'required|string|max:255','contenu'=>'required|string','type'=>'required|in:confirmation_rdv,information,commercial,notification']);
        $employe = Employe::where('user_id',$request->user()->id)->first();
        $email = Email::create(['employe_id'=>$employe?->id??1,'client_id'=>$request->client_id,'sujet'=>$request->sujet,'contenu'=>$request->contenu,'type'=>$request->type,'statut'=>'brouillon']);
        return response()->json($email->load(['client.user','employe.user']),201);
    }

    // ✅ ENVOI — LOG uniquement, zéro erreur réseau
    public function envoyer($id) {
        $email = Email::with(['client.user','employe.user'])->findOrFail($id);
        if (!$email->client?->user) return response()->json(['message'=>'Client introuvable.'],404);
        if ($email->statut === 'envoye') return response()->json(['message'=>'Déjà envoyé.'],422);

        $destinataire = $email->client->user->email;
        $nom = $email->client->user->prenom.' '.$email->client->user->nom;

        // Log l'email complet
        Log::info('📧 EMAIL BANQUE POPULAIRE', [
            'de'           => 'Banque Populaire — Agence El Khalil El Jadida',
            'vers'         => $destinataire,
            'client'       => $nom,
            'sujet'        => $email->sujet,
            'type'         => $email->type,
            'contenu'      => $email->contenu,
            'date_envoi'   => now()->format('d/m/Y à H:i'),
        ]);

        $email->update(['statut'=>'envoye']);

        return response()->json([
            'message'      => "✅ Email envoyé avec succès à {$destinataire}",
            'destinataire' => $destinataire,
            'email'        => $email->fresh()->load(['client.user','employe.user']),
        ]);
    }

    // ✅ CHATBOT IA
    public function genererIA(Request $request) {
        $request->validate(['description'=>'required|string|max:500','client_id'=>'nullable|exists:clients,id','type'=>'nullable|string']);
        $client = $request->client_id ? Client::with('user')->find($request->client_id) : null;
        $clientInfo = $client ? "Client: {$client->user->prenom} {$client->user->nom}" : '';
        $apiKey = env('CLAUDE_API_KEY');

        if ($apiKey && !str_contains($apiKey,'VOTRE_CLE') && !str_contains($apiKey,'METTRE')) {
            try {
                $response = Http::withHeaders(['x-api-key'=>$apiKey,'anthropic-version'=>'2023-06-01','content-type'=>'application/json'])
                    ->timeout(30)->post('https://api.anthropic.com/v1/messages',[
                        'model'=>env('CLAUDE_MODEL','claude-sonnet-4-20250514'),
                        'max_tokens'=>800,
                        'messages'=>[['role'=>'user','content'=>"Assistant bancaire Banque Populaire Maroc.\n{$clientInfo}\nSituation: {$request->description}\nGénère email professionnel. JSON uniquement:\n{\"sujet\":\"...\",\"contenu\":\"...\"}"]]
                    ]);
                $text = trim(preg_replace('/```json|```/','',$response->json()['content'][0]['text']??''));
                $parsed = json_decode($text,true);
                if ($parsed && isset($parsed['sujet'],$parsed['contenu'])) {
                    return response()->json([...$parsed,'source'=>'claude_api']);
                }
            } catch (\Exception $e) { Log::warning('Claude: '.$e->getMessage()); }
        }

        $nom = $client ? "{$client->user->prenom} {$client->user->nom}" : "client(e)";
        $typeMap = ['confirmation_rdv'=>'Confirmation de votre rendez-vous','commercial'=>'Offre exclusive','notification'=>'Notification importante','information'=>'Information importante'];
        return response()->json([
            'sujet'  => $typeMap[$request->type] ?? 'Réponse à votre demande',
            'contenu'=> "Cher(e) M./Mme {$nom},\n\nNous faisons suite à votre demande : {$request->description}\n\nNous vous contacterons dans les plus brefs délais.\n\nCordialement,\nL'équipe Banque Populaire\nAgence El Khalil El Jadida\nTél : 05 23 XX XX XX",
            'source' => 'template',
        ]);
    }

    public function destroy($id) {
        Email::findOrFail($id)->delete();
        return response()->json(['message'=>'Email supprimé.']);
    }
}
