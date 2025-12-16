<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear el Super Admin (TÚ)
        $user = User::create([
            'name' => 'Administrador SaaS',
            'email' => 'admin@admin.com',
            'password' => Hash::make('admin'), // Contraseña genérica para desarrollo
            'is_super_admin' => true,
        ]);

        // 2. Crear un Colegio de prueba
        $school = School::create([
            'name' => 'I.E. Demo Hostinger',
            'slug' => 'ie-demo-hostinger',
            'is_active' => true,
            'modules' => [
                'psychology' => true, 
                'whatsapp' => true
            ],
        ]);

        // 3. Vincularte al colegio (como Director/Admin del colegio)
        // Esto verifica que la relación User-School funciona
        $user->schools()->attach($school->id, ['role' => 'admin']);
        
        $this->command->info('¡Usuario Admin y Colegio Demo creados correctamente!');
    }
}