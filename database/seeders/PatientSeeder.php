<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Patient;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        Patient::create([
            'first_name' => 'Juan',
            'last_name' => 'Pueblo',
            'dni' => '20123456',
            'birth_date' => '1990-05-15',
            'gender' => 'M',
            'blood_type' => 'O+',
            'phone' => '1144556677',
            'email' => 'juan@example.com',
            'allergies' => 'Ninguna conocida'
        ]);

        Patient::factory(10)->create();
        // Tomamos todos los pacientes y les asignamos alergias/patologías al azar
        $allPathologies = \App\Models\Pathology::all();
        $allAllergies = \App\Models\Allergy::all();

        Patient::all()->each(function ($patient) use ($allPathologies, $allAllergies) {
            $patient->pathologies()->attach(
                $allPathologies->random(rand(1, 2))->pluck('id')
            );
            $patient->allergies_list()->attach(
                $allAllergies->random(rand(1, 2))->pluck('id')
            );
        });
    }
}
