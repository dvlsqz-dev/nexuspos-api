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
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('nit')->nullable()->after('name');
            $table->string('legal_name')->nullable()->after('nit');
            $table->string('address')->nullable()->after('business_type');
            $table->string('phone')->nullable()->after('address');
            $table->enum('status', ['pendiente', 'activo', 'suspendido', 'rechazado'])
                ->default('pendiente')
                ->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['nit', 'legal_name', 'address', 'phone', 'status']);
        });
    }
};
