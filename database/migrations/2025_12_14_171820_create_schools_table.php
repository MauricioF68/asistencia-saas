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
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique(); // Para URL amigables (ej: ie-san-jose)
            $table->string('logo')->nullable(); // Ruta del archivo
            $table->boolean('is_active')->default(true); // Control maestro del SaaS
            $table->json('modules')->nullable(); // Configuración flexible de módulos
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
