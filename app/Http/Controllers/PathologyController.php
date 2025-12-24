<?php

namespace App\Http\Controllers;

use App\Models\Pathology;
use Illuminate\Http\Request;

class PathologyController extends Controller
{
    public function index()
    {
        return response()->json(Pathology::all());
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:pathologies,name']);
        $patologia = Pathology::create($request->all());
        return response()->json(['mensaje' => 'Patología creada', 'data' => $patologia], 201);
    }

    public function show(Pathology $pathology)
    {
        return response()->json($pathology);
    }

    public function update(Request $request, Pathology $pathology)
    {
        $request->validate(['name' => 'required|unique:pathologies,name,' . $pathology->id]);
        $pathology->update($request->all());
        return response()->json(['mensaje' => 'Patología actualizada', 'data' => $pathology]);
    }

    public function destroy(Pathology $pathology)
    {
        $pathology->delete();
        return response()->json(['mensaje' => 'Patología eliminada']);
    }
}