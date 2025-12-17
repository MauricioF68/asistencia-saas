<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Gestión SaaS'; // Lo agrupamos con Colegios
    protected static ?string $navigationLabel = 'Usuarios y Directores';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos de Acceso')
                    ->description('Credenciales para ingresar al sistema.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre Completo')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true), // No permitir correos repetidos

                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->label('Contraseña')
                            ->required(fn ($livewire) => $livewire instanceof Pages\CreateUser) // Obligatorio solo al crear
                            ->dehydrated(fn ($state) => filled($state)) // Solo se guarda si escribieron algo
                            ->confirmed(), // Pide confirmar contraseña

                        Forms\Components\TextInput::make('password_confirmation')
                            ->password()
                            ->label('Confirmar Contraseña')
                            ->required(fn ($livewire) => $livewire instanceof Pages\CreateUser)
                            ->dehydrated(false), // Esto no se guarda en BD
                    ])->columns(2),

                Forms\Components\Section::make('Permisos y Asignaciones')
                    ->schema([
                        // EL PODER TOTAL
                        Forms\Components\Toggle::make('is_super_admin')
                            ->label('¿Es Super Administrador?')
                            ->helperText('Cuidado: Si activas esto, tendrá acceso a TODO el panel global.')
                            ->default(false),

                        // ASIGNACIÓN DE COLEGIOS
                        Forms\Components\CheckboxList::make('schools')
                            ->label('Asignar Colegios (Acceso Director)')
                            ->relationship('schools', 'name') // Usa la relación que creamos en el Modelo
                            ->columns(2)
                            ->helperText('El usuario solo podrá gestionar los colegios marcados aquí.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),

                // Muestra cuántos colegios tiene asignados
                Tables\Columns\TextColumn::make('schools_count')
                    ->counts('schools')
                    ->label('Colegios')
                    ->badge(),

                Tables\Columns\IconColumn::make('is_super_admin')
                    ->label('Admin')
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}