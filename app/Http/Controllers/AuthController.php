<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }

        // Creamos el token de Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'mensaje' => 'Bienvenido/a, ' . $user->name,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'usuario' => [
                'id' => $user->id,
                'nombre' => $user->name,
                'especialidad' => $user->specialty,
                'mn' => $user->mn_number,
                'mp' => $user->mp_number,
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['mensaje' => 'Sesión cerrada correctamente']);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validados = $request->validate([
            'name'      => 'required|string|max:255',
            'specialty' => 'required|string',
            'mn_number' => 'required|string',
            'mp_number' => 'required|string',
        ]);

        $user->update($validados);

        return response()->json([
            'mensaje' => 'Perfil actualizado con éxito',
            'usuario' => $user
        ]);
    }
}
