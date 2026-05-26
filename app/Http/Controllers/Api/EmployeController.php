<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Employe;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeController extends Controller {
    public function index() { return response()->json(Employe::with('user')->get()); }
    public function store(Request $request) {
        $request->validate(['nom'=>'required|string','prenom'=>'required|string','email'=>'required|email|unique:users,email','telephone'=>'nullable|string','poste'=>'required|string','agence'=>'required|string','role'=>'nullable|in:employe,manager']);
        $user = User::create(['nom'=>$request->nom,'prenom'=>$request->prenom,'email'=>$request->email,'password'=>Hash::make('password123'),'role'=>$request->role??'employe','telephone'=>$request->telephone,'is_active'=>true]);
        $count = Employe::count()+1;
        $employe = Employe::create(['user_id'=>$user->id,'matricule'=>'EMP'.str_pad($count,4,'0',STR_PAD_LEFT),'poste'=>$request->poste,'agence'=>$request->agence??'Agence El Khalil El Jadida']);
        return response()->json($employe->load('user'),201);
    }
    public function update(Request $request,$id) {
        $employe = Employe::with('user')->findOrFail($id);
        $employe->user->update(array_filter($request->only(['nom','prenom','email','telephone'])));
        $employe->update(array_filter($request->only(['poste','agence'])));
        return response()->json($employe->fresh()->load('user'));
    }
    public function destroy($id) {
        $employe = Employe::with('user')->findOrFail($id);
        $employe->user?->delete();
        return response()->json(['message'=>'Employé supprimé.']);
    }
}
