<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operations', function (Blueprint $table) {
            $table->decimal('monthly_goal', 12, 2)->nullable()->after('description');
            $table->integer('contracts_goal')->nullable()->after('monthly_goal');
            $table->decimal('sales_goal', 12, 2)->nullable()->after('contracts_goal');
        });
    }

    public function down(): void
    {
        Schema::table('operations', function (Blueprint $table) {
            $table->dropColumn(['monthly_goal', 'contracts_goal', 'sales_goal']);
        });
    }
};
