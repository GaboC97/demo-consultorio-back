<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
public function index(Request $request)
{
    $query = User::query();

    // Filtrar médicos
    if ($request->get('role') === 'medico') {
        $query->whereNotNull('specialty');
    }

    // Búsqueda
    if ($request->filled('q')) {
        $q = $request->q;

        $query->where(function ($sub) use ($q) {
            $sub->where('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
                ->orWhere('specialty', 'like', "%{$q}%")
                ->orWhere('mn_number', 'like', "%{$q}%")
                ->orWhere('mp_number', 'like', "%{$q}%");
        });
    }

    // (Opcional) excluirme a mí mismo
    if ($request->user()) {
        $query->where('id', '!=', $request->user()->id);
    }

    return $query->orderBy('name')->get([
        'id',
        'name as nombre',
        'email',
        'mn_number as mn',
        'mp_number as mp',
        'specialty'
    ]);
}

}
