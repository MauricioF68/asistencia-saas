<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable implements FilamentUser, HasTenants
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_super_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_super_admin' => 'boolean',
    ];

    // RELACIÓN: Un usuario puede pertenecer a muchos colegios
    public function schools(): BelongsToMany
    {
        return $this->belongsToMany(School::class);
    }

    // LOGICA FILAMENT: ¿Puede este usuario entrar al panel?
    public function canAccessPanel(Panel $panel): bool
    {
        // Si es Super Admin, entra a todo.
        if ($this->is_super_admin) {
            return true;
        }

        // Si es un usuario normal (profesor), validaremos más adelante
        // que solo entre al panel del colegio ('app'), no al admin general.
        // Por ahora retornamos true para facilitar pruebas, luego restringiremos.
        return true; 
    }

    // LOGICA TENANCY: ¿A qué colegios tiene acceso este usuario?
    public function getTenants(Panel $panel): Collection
    {
        return $this->schools;
    }

    // LOGICA TENANCY: ¿Puede acceder a ESTE colegio específico?
    public function canAccessTenant(Model $tenant): bool
    {
        return $this->schools()->whereKey($tenant)->exists();
    }
}