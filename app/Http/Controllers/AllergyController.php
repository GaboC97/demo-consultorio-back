<?php

namespace App\Http\Controllers;

use App\Models\Allergy;
use Illuminate\Http\Request;

class AllergyController extends Controller
{
    public function index()
    {
        return response()->json(Allergy::all());
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:allergies,name']);
        $alergia = Allergy::create($request->all());
        return response()->json(['mensaje' => 'Alergia creada', 'data' => $alergia], 201);
    }

    public function show(Allergy $allergy)
    {
        return response()->json($allergy);
    }

    public function update(Request $request, Allergy $allergy)
    {
        $request->validate(['name' => 'required|unique:allergies,name,' . $allergy->id]);
        $allergy->update($request->all());
        return response()->json(['mensaje' => 'Alergia actualizada', 'data' => $allergy]);
    }

    public function destroy(Allergy $allergy)
    {
        $allergy->delete();
        return response()->json(['mensaje' => 'Alergia eliminada']);
    }
}
