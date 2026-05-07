<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_status_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users');   // Asesor
            $table->foreignId('resolved_by')->nullable()->constrained('users'); // Gerente/Secretario
            $table->string('requested_status');         // Estado que solicita
            $table->text('reason');                     // Motivo del asesor
            $table->text('rejection_reason')->nullable(); // Motivo si rechazan
            $table->enum('status', ['pendiente', 'aprobado', 'rechazado'])->default('pendiente');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'contract_id']);
            $table->index('requested_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_status_requests');
    }
};
