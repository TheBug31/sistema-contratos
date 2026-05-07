<?php

namespace Database\Seeders;

use App\Models\Operation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear operaciones si no existen
        $operation1 = Operation::firstOrCreate(['name' => 'Operación Norte']);
        $operation2 = Operation::firstOrCreate(['name' => 'Operación Sur']);

        // Crear usuarios con roles
        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'operation_id' => $operation1->id,
            'active' => true,
        ]);
        $admin->assignRole('Administrador');

        $secretario = User::create([
            'name' => 'Secretario',
            'email' => 'secretario@example.com',
            'password' => Hash::make('password'),
            'operation_id' => $operation1->id,
            'active' => true,
        ]);
        $secretario->assignRole('Secretario');

        $gerente = User::create([
            'name' => 'Gerente',
            'email' => 'gerente@example.com',
            'password' => Hash::make('password'),
            'operation_id' => $operation2->id,
            'active' => true,
        ]);
        $gerente->assignRole('Gerente');

        $asesor1 = User::create([
            'name' => 'Asesor Uno',
            'email' => 'asesor1@example.com',
            'password' => Hash::make('password'),
            'operation_id' => $operation1->id,
            'active' => true,
        ]);
        $asesor1->assignRole('Asesor');

        $asesor2 = User::create([
            'name' => 'Asesor Dos',
            'email' => 'asesor2@example.com',
            'password' => Hash::make('password'),
            'operation_id' => $operation2->id,
            'active' => true,
        ]);
        $asesor2->assignRole('Asesor');
    }
}
