<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    // Buscar usuario por correo
    $user = User::where('email', $request->email)->first();


    // Verificar contraseña
    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    // Crear token
    $token = $user->createToken('API Token')->plainTextToken;

    return response()->json([
        'token' => $token,
        'user' => $user,
    ]);
});

// Endpoint protegido por Sanctum

