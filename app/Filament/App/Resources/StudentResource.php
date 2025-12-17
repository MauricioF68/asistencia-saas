<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\StudentResource\Pages;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;
use Filament\Facades\Filament;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Matrícula';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos del Alumno')
                    ->schema([
                        Forms\Components\TextInput::make('dni')
                            ->label('DNI / Cédula')
                            ->required()
                            ->maxLength(20)
                            ->unique(
                                ignoreRecord: true,
                                modifyRuleUsing: fn (Unique $rule) => $rule->where('school_id', Filament::getTenant()->id)
                            ),
                        Forms\Components\TextInput::make('name')->required()->label('Nombres'),
                        Forms\Components\TextInput::make('last_name')->required()->label('Apellidos'),
                        Forms\Components\DatePicker::make('birth_date')
                            ->label('Fecha Nacimiento')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->closeOnDateSelection(),
                    ])->columns(2),

                Forms\Components\Section::make('Académico')
                    ->schema([
                        // --- AQUÍ ESTÁ EL ARREGLO (GRADO) ---
                        Forms\Components\Select::make('grade_id')
                            ->label('Grado')
                            ->required()
                            ->preload()
                            ->searchable()
                            // Usamos relationship con filtro personalizado
                            ->relationship(
                                name: 'grade',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query->where('school_id', Filament::getTenant()->id)
                            )
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->required()->label('Nombre Grado'),
                            ])
                            ->createOptionUsing(function (array $data) {
                                // Aseguramos que al crear rápido se asigne al colegio
                                $data['school_id'] = Filament::getTenant()->id;
                                return \App\Models\Grade::create($data)->getKey();
                            }),
                            
                        // --- AQUÍ ESTÁ EL ARREGLO (SECCIÓN) ---
                        Forms\Components\Select::make('section_id')
                            ->label('Sección')
                            ->required()
                            ->preload()
                            ->searchable()
                            ->relationship(
                                name: 'section',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query->where('school_id', Filament::getTenant()->id)
                            )
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->required()->label('Nombre Sección'),
                            ])
                            ->createOptionUsing(function (array $data) {
                                $data['school_id'] = Filament::getTenant()->id;
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
                            ->label('Estado')
                            ->options([
                                'active' => 'Activo',
                                'withdrawn' => 'Retirado',
                            ])
                            ->default('active')
                            ->required()
                            ->native(false),
                    ])->columns(2),

                Forms\Components\Section::make('Contacto Apoderado')
                    ->description('Datos para comunicación y emergencias.')
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
                            ->helperText('Solo números para WhatsApp.'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Alumno')->searchable(),
                Tables\Columns\TextColumn::make('grade.name')->label('Grado'),
                Tables\Columns\TextColumn::make('section.name')->label('Sección'),
            ])
            ->actions([ Tables\Actions\EditAction::make() ]);
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