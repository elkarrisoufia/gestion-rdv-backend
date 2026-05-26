<?php
use Illuminate\Support\Facades\Route;
Route::get('/', fn() => response()->json(['app'=>'Banque Populaire API','status'=>'ok']));
