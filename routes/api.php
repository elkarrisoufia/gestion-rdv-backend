<?php
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\EmailController;
use App\Http\Controllers\Api\EmployeController;
use App\Http\Controllers\Api\RendezVousController;
use App\Http\Controllers\Api\StatistiqueController;
use Illuminate\Support\Facades\Route;

// ===== PUBLIQUES =====
Route::post('/auth/login', [AuthController::class,'login']);
Route::post('/auth/register-client', [AuthController::class,'registerClient']);

// ===== AUTHENTIFIÉES =====
Route::middleware(['auth:sanctum','active'])->group(function () {
    Route::post('/auth/logout',[AuthController::class,'logout']);
    Route::get('/auth/me',[AuthController::class,'me']);

    // ✅ EMPLOYES accessible par TOUS les rôles connectés (pour le form RDV)
    Route::get('/employes',[EmployeController::class,'index']);

    // ===== CLIENT =====
    Route::middleware('role:client')->group(function () {
        Route::get('/client/rdv',[RendezVousController::class,'mesRdv']);
        Route::post('/client/rdv',[RendezVousController::class,'clientStore']);
        Route::put('/client/rdv/{id}/annuler',[RendezVousController::class,'annuler']);
    });

    // ===== EMPLOYÉ + MANAGER =====
    Route::middleware('role:employe,manager')->group(function () {
        Route::get('/rdv/today',[RendezVousController::class,'today']);
        Route::get('/rdv/creneaux',[RendezVousController::class,'creneaux']);
        Route::get('/rdv',[RendezVousController::class,'index']);
        Route::post('/rdv',[RendezVousController::class,'store']);
        Route::put('/rdv/{id}/confirmer',[RendezVousController::class,'confirmer']);
        Route::put('/rdv/{id}/annuler',[RendezVousController::class,'annuler']);
        Route::put('/rdv/{id}',[RendezVousController::class,'update']);
        Route::delete('/rdv/{id}',[RendezVousController::class,'destroy']);

        Route::get('/clients',[ClientController::class,'index']);
        Route::get('/clients/{id}',[ClientController::class,'show']);
        Route::post('/clients',[ClientController::class,'store']);
        Route::put('/clients/{id}',[ClientController::class,'update']);
        Route::delete('/clients/{id}',[ClientController::class,'destroy']);

        Route::get('/emails',[EmailController::class,'index']);
        Route::post('/emails',[EmailController::class,'store']);
        Route::post('/emails/{id}/envoyer',[EmailController::class,'envoyer']);
        Route::delete('/emails/{id}',[EmailController::class,'destroy']);

        Route::post('/chatbot/generer',[EmailController::class,'genererIA']);
    });

    // ===== MANAGER =====
    Route::middleware('role:manager')->group(function () {
        Route::get('/statistiques',[StatistiqueController::class,'index']);
        Route::get('/statistiques/rdv',[StatistiqueController::class,'rdv']);
        Route::get('/statistiques/employes',[StatistiqueController::class,'employes']);
        Route::post('/employes',[EmployeController::class,'store']);
        Route::put('/employes/{id}',[EmployeController::class,'update']);
        Route::delete('/employes/{id}',[EmployeController::class,'destroy']);
    });
});
