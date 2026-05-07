<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE contracts MODIFY COLUMN current_status ENUM('asignado', 'limpio', 'lleno', 'venta', 'anulado') DEFAULT 'asignado'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE contracts MODIFY COLUMN current_status ENUM('limpio', 'lleno', 'venta', 'anulado') DEFAULT 'limpio'");
    }
};
