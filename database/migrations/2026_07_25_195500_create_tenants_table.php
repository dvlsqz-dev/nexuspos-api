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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('business_type')->nullable();
            $table->enum('tax_regime', ['pequeno_contribuyente', 'general'])->default('general');
            $table->decimal('default_tax_rate', 5, 2)->default(12.00);
            $table->boolean('prices_include_tax')->default(true);
            $table->string('currency', 3)->default('GTQ');
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
