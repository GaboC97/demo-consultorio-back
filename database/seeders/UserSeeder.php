<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Dr. Pedro Pérez',
            'email' => 'doctor@test.com',
            'password' => Hash::make('12345'), // Encriptamos manualmente aquí
            'specialty' => 'Cardiología',
            'mn_number' => '12345',
            'mp_number' => '67890',
        ]);
    }
}
