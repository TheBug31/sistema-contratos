<?php

namespace Database\Seeders;

use App\Models\Contract;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContractSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener asesores
        $asesores = User::role('Asesor')->get();

        if ($asesores->isEmpty()) {
            return; // No hay asesores, no crear contratos
        }

        // Crear contratos
        $statuses = ['asignado', 'limpio', 'lleno', 'venta', 'anulado'];

        for ($i = 1; $i <= 10; $i++) {
            Contract::create([
                'number' => $i,
                'advisor_id' => $asesores->random()->id,
                'current_status' => $statuses[array_rand($statuses)],
                'delivered_at' => now()->subDays(rand(0, 365)),
            ]);
        }
    }
}
