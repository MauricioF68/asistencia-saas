<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            
            // 1. Pertenencia (Multi-tenancy)
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();

            // 2. Identidad del Alumno
            $table->string('dni', 20); // DNI/Cédula
            $table->string('name');
            $table->string('last_name');
            $table->date('birth_date')->nullable(); // Para reportes de edad

            // 3. Datos Académicos (Vitales para Asistencia)
            $table->string('grade');   // Ej: "5to Secundaria"
            $table->string('section'); // Ej: "A"
            $table->string('shift');   // Ej: "morning" (Mañana) o "afternoon" (Tarde)

            // 4. Contacto y Riesgo (Para WhatsApp)
            $table->string('parent_name');
            $table->string('parent_phone'); // Solo guardaremos números aquí

            // 5. Estado del Alumno
            // Valores: 'active', 'withdrawn' (retirado), 'expelled' (expulsado), 'graduated' (egresado)
            $table->string('status')->default('active'); 

            $table->timestamps();

            // REGLA DE ORO: El DNI debe ser único DENTRO del colegio.
            // (Pero dos colegios distintos pueden tener un alumno con el mismo DNI si se traslada).
            $table->unique(['school_id', 'dni']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};