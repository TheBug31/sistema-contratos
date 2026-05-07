<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Contract;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('code')->nullable()->unique()->after('number');
        });

        // Generar code para contratos existentes
        Contract::whereNull('code')->each(function ($contract) {
            $contract->update(['code' => 'CL-' . $contract->number]);
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }
};
