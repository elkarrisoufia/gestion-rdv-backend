<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller {
    public function index() { return response()->json(Client::with(['user','employe.user'])->get()); }
    public function show($id) { return response()->json(Client::with(['user','employe.user','rendezVous'])->findOrFail($id)); }
    public function store(Request $request) {
        $request->validate(['nom'=>'required|string','prenom'=>'required|string','email'=>'required|email|unique:users,email','telephone'=>'nullable|string','cin'=>'required|string|unique:clients,cin','adresse'=>'nullable|string','type_compte'=>'required|in:courant,epargne,professionnel','is_vip'=>'boolean','employe_id'=>'required|exists:employes,id']);
        $user = User::create(['nom'=>$request->nom,'prenom'=>$request->prenom,'email'=>$request->email,'password'=>Hash::make('password123'),'role'=>'client','telephone'=>$request->telephone,'is_active'=>true]);
        $client = Client::create(['user_id'=>$user->id,'cin'=>$request->cin,'adresse'=>$request->adresse??'','type_compte'=>$request->type_compte,'is_vip'=>$request->boolean('is_vip',false),'employe_id'=>$request->employe_id]);
        return response()->json($client->load(['user','employe.user']),201);
    }
    public function update(Request $request,$id) {
        $client = Client::with('user')->findOrFail($id);
        $client->user->update(array_filter($request->only(['nom','prenom','email','telephone'])));
        $client->update(array_filter($request->only(['cin','adresse','type_compte','employe_id'])));
        if ($request->has('is_vip')) $client->update(['is_vip'=>$request->boolean('is_vip')]);
        return response()->json($client->fresh()->load(['user','employe.user']));
    }
    public function destroy($id) {
        $client = Client::with('user')->findOrFail($id);
        $client->user?->delete();
        return response()->json(['message'=>'Client supprimé.']);
    }
}
