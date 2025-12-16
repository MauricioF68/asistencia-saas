<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchoolResource\Pages;
use App\Models\School;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class SchoolResource extends Resource
{
    protected static ?string $model = School::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationLabel = 'Colegios';
    protected static ?string $modelLabel = 'Colegio';
    
    // Grupo de navegación (útil cuando tengamos más opciones)
    protected static ?string $navigationGroup = 'Gestión SaaS';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identidad del Colegio')
                    ->description('Datos principales y logo de la institución.')
                    ->schema([
                        // Nombre con generación automática de Slug
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre del Colegio')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true) // Escucha cuando el usuario deja de escribir
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                // Rellena el slug automáticamente
                                $set('slug', Str::slug($state));
                            }),

                        Forms\Components\TextInput::make('slug')
                            ->label('URL Amigable (Slug)')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true) // Evita duplicados
                            ->readOnly(), // Mejor que sea solo lectura para evitar errores

                        Forms\Components\FileUpload::make('logo')
                            ->label('Logo Institucional')
                            ->image()
                            ->directory('school-logos') // Carpeta donde se guardan
                            ->visibility('public'),
                    ])->columns(2),

                Forms\Components\Section::make('Configuración de Servicio')
                    ->description('Control de acceso y módulos contratados.')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Servicio Activo')
                            ->default(true)
                            ->helperText('Si se apaga, nadie de este colegio podrá entrar.'),

                        // Aquí manejamos el JSON de módulos como checkboxes
                        Forms\Components\CheckboxList::make('modules')
                            ->label('Módulos Habilitados')
                            ->options([
                                'asistencia' => 'Control de Asistencia (Core)',
                                'psychology' => 'Módulo de Psicología/Riesgo',
                                'whatsapp' => 'Notificaciones WhatsApp',
                                'reports' => 'Reportes Avanzados',
                            ])
                            ->columns(2) // Se ven en 2 columnas
                            ->gridDirection('row'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->circular(),
                
                Tables\Columns\TextColumn::make('name')
                    ->label('Institución')
                    ->description(fn (School $record) => $record->slug) // Muestra el slug debajo
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Estado')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha Registro')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filtro rápido para ver solo activos/inactivos
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Filtrar por Estado'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Aquí pondremos relaciones futuras
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchools::route('/'),
            'create' => Pages\CreateSchool::route('/create'),
            'edit' => Pages\EditSchool::route('/{record}/edit'),
        ];
    }
}