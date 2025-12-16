<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Models\School; // <--- Importante
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique; // <--- Para validación avanzada

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Alumnos (Global)';
    protected static ?string $navigationGroup = 'Gestión Académica';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Filiación Escolar')
                    ->description('Asignación del alumno a una institución.')
                    ->schema([
                        Forms\Components\Select::make('school_id')
                            ->label('Colegio')
                            ->options(School::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->columnSpanFull()
                            ->live(), // IMPORTANTE: Al cambiar esto, se recargan los selectores de abajo
                    ]),

                Forms\Components\Section::make('Datos Personales')
                    ->schema([
                        Forms\Components\TextInput::make('dni')
                            ->label('DNI / Cédula')
                            ->required()
                            ->maxLength(20)
                            ->unique(
                                modifyRuleUsing: function (Unique $rule, Get $get) {
                                    return $rule->where('school_id', $get('school_id'));
                                },
                                ignoreRecord: true
                            ),
                        
                        Forms\Components\TextInput::make('name')
                            ->label('Nombres')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('last_name')
                            ->label('Apellidos')
                            ->required()
                            ->maxLength(255),

                        // --- AQUÍ ESTÁ EL ARREGLO DEL DATEPICKER ---
                        Forms\Components\DatePicker::make('birth_date')
                            ->label('Fecha de Nacimiento')
                            ->native(false) // Usa el calendario JS de Filament
                            ->displayFormat('d/m/Y') // Formato visual
                            ->maxDate(now()) // No puede haber nacido mañana
                            ->closeOnDateSelection() // Se cierra al elegir
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Información Académica')
                    ->schema([
                        // SELECTOR DE GRADO (Filtrado por Colegio)
                        Forms\Components\Select::make('grade_id')
                            ->label('Grado / Aula')
                            ->options(function (Get $get) {
                                $schoolId = $get('school_id');
                                if (!$schoolId) return [];
                                return \App\Models\Grade::where('school_id', $schoolId)->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            // MAGIA: Permite crear un grado nuevo sin salir del formulario
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre del Grado')
                                    ->required(),
                                Forms\Components\Hidden::make('school_id'),
                            ])
                            ->createOptionUsing(function (array $data, Get $get) {
                                $data['school_id'] = $get('school_id');
                                return \App\Models\Grade::create($data)->getKey();
                            }),
                        
                        // SELECTOR DE SECCIÓN (Filtrado por Colegio)
                        Forms\Components\Select::make('section_id')
                            ->label('Sección')
                            ->options(function (Get $get) {
                                $schoolId = $get('school_id');
                                if (!$schoolId) return [];
                                return \App\Models\Section::where('school_id', $schoolId)->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            // MAGIA: Permite crear sección nueva sin salir
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre de Sección')
                                    ->required(),
                            ])
                            ->createOptionUsing(function (array $data, Get $get) {
                                $data['school_id'] = $get('school_id');
                                return \App\Models\Section::create($data)->getKey();
                            }),

                        Forms\Components\Select::make('shift')
                            ->label('Turno')
                            ->options([
                                'morning' => 'Mañana',
                                'afternoon' => 'Tarde',
                                'night' => 'Noche',
                            ])
                            ->required()
                            ->native(false),
                        
                        Forms\Components\Select::make('status')
                            ->label('Estado Actual')
                            ->options([
                                'active' => 'Activo',
                                'withdrawn' => 'Retirado',
                                'expelled' => 'Expulsado',
                                'graduated' => 'Egresado',
                            ])
                            ->default('active')
                            ->required()
                            ->native(false),
                    ])->columns(2),

                Forms\Components\Section::make('Contacto y Riesgo (WhatsApp)')
                    ->schema([
                        Forms\Components\TextInput::make('parent_name')
                            ->label('Nombre del Apoderado')
                            ->required()
                            ->prefixIcon('heroicon-m-user'),
                        
                        Forms\Components\TextInput::make('parent_phone')
                            ->label('Celular Apoderado')
                            ->tel()
                            ->required()
                            ->prefixIcon('heroicon-m-phone')
                            ->helperText('Solo números.'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('school.name')
                    ->label('Colegio')
                    ->sortable()
                    ->searchable()
                    ->badge() // Se ve como etiqueta
                    ->color('gray'),

                Tables\Columns\TextColumn::make('dni')
                    ->label('DNI')
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nombres')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_name')
                    ->label('Apellidos')
                    ->searchable(),

                Tables\Columns\TextColumn::make('grade')
                    ->label('G/S')
                    ->formatStateUsing(fn($record) => "{$record->grade} - {$record->section}"),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'withdrawn' => 'warning',
                        'expelled' => 'danger',
                        'graduated' => 'info',
                    }),
            ])
            ->filters([
                // Filtro para ver alumnos de un solo colegio
                Tables\Filters\SelectFilter::make('school_id')
                    ->label('Filtrar por Colegio')
                    ->relationship('school', 'name'),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
