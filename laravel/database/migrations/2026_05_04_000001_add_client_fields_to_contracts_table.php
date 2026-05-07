<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('client_name')->nullable()->after('number');
            $table->string('client_document')->nullable()->after('client_name');
            $table->string('client_phone')->nullable()->after('client_document');
            $table->decimal('amount', 10, 2)->nullable()->after('client_phone');
            $table->timestamp('signed_at')->nullable()->after('amount');
            $table->timestamp('expires_at')->nullable()->after('signed_at');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['client_name', 'client_document', 'client_phone', 'amount', 'signed_at', 'expires_at']);
        });
    }
};
