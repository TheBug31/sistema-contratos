<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('number')->unique();
            $table->foreignId('advisor_id')->constrained('users');
            $table->enum('current_status', ['limpio', 'lleno', 'venta', 'anulado'])
                ->default('limpio');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            $table->index('advisor_id');
            $table->index('current_status');
            $table->index(['advisor_id', 'current_status']);
            $table->index('number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
