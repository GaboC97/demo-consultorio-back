<?php

namespace Database\Seeders;

use App\Models\Allergy;
use App\Models\Pathology;
use Illuminate\Database\Seeder;

class MedicalCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $alergias = ['Penicilina', 'Polen', 'Aspirina', 'Lácteos', 'Iodo', 'Látex'];
        foreach ($alergias as $a) {
            Allergy::create(['name' => $a]);
        }

        $enfermedades = ['Diabetes Tipo 2', 'Hipertensión Arterial', 'Asma', 'Hipotiroidismo', 'Celidquía'];
        foreach ($enfermedades as $e) {
            Pathology::create(['name' => $e]);
        }
    }
}
