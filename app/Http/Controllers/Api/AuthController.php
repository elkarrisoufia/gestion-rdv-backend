<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Employe;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller {
    public function login(Request $request) {
        $request->validate(['email'=>'required|email','password'=>'required|string']);
        $user = User::where('email',$request->email)->first();
        if (!$user || !Hash::check($request->password,$user->password)) {
            return response()->json(['message'=>'Email ou mot de passe incorrect.'],401);
        }
        if (!$user->is_active) return response()->json(['message'=>'Compte désactivé.'],403);
        $token = $user->createToken('auth-token')->plainTextToken;
        $data = $user->only('id','nom','prenom','email','role','telephone');
        if ($user->role === 'client') {
            $client = Client::where('user_id',$user->id)->first();
            $data['client_id'] = $client?->id;
        }
        return response()->json(['token'=>$token,'user'=>$data]);
    }

    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message'=>'Déconnecté.']);
    }

    public function me(Request $request) {
        $user = $request->user();
        $data = $user->only('id','nom','prenom','email','role','telephone');
        if ($user->role === 'client') {
            $client = Client::where('user_id',$user->id)->first();
            $data['client_id'] = $client?->id;
        }
        return response()->json($data);
    }

    public function registerClient(Request $request) {
        $request->validate([
            'nom'=>'required|string','prenom'=>'required|string',
            'email'=>'required|email|unique:users,email',
            'password'=>'required|string|min:6|confirmed',
            'telephone'=>'nullable|string','cin'=>'required|string|unique:clients,cin',
            'adresse'=>'nullable|string',
        ],['email.unique'=>'Cet email est déjà utilisé.','cin.unique'=>'Ce CIN est déjà enregistré.']);

        $user = User::create([
            'nom'=>$request->nom,'prenom'=>$request->prenom,'email'=>$request->email,
            'password'=>Hash::make($request->password),'role'=>'client',
            'telephone'=>$request->telephone,'is_active'=>true,
        ]);

        $employe = Employe::first();
        $client = Client::create([
            'user_id'=>$user->id,'cin'=>$request->cin,'adresse'=>$request->adresse??'',
            'type_compte'=>'courant','is_vip'=>false,'employe_id'=>$employe?->id,
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;
        $data = $user->only('id','nom','prenom','email','role','telephone');
        $data['client_id'] = $client->id;
        return response()->json(['token'=>$token,'user'=>$data,'message'=>'Compte créé avec succès !'],201);
    }
}
