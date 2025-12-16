<?php

namespace App\Filament\Resources;

use Illuminate\Validation\Rules\Unique; // <--- AGREGA ESTO ARRIBA CON LOS IMPORTS
use Filament\Forms\Get;

use App\Filament\Resources\GradeResource\Pages;
use App\Models\Grade;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GradeResource extends Resource
{
    protected static ?string $model = Grade::class;

    protected static ?string $navigationIcon = 'heroicon-o-bookmark';
    protected static ?string $navigationGroup = 'Gestión Académica';
    protected static ?string $navigationLabel = 'Grados';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('school_id')
                            ->relationship('school', 'name')
                            ->label('Colegio')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('name')
                            ->label('Nombre del Grado')
                            ->placeholder('Ej: 5to Secundaria / 3 Años')
                            ->required()
                            ->maxLength(255)
                            ->unique(
                            ignoreRecord: true, 
                            modifyRuleUsing: function (Unique $rule, Get $get) {
                                $schoolId = $get('school_id');
                               
                                return $rule->where('school_id', $schoolId);
                                }
                            )
                            ->validationMessages([
                            'unique' => 'Este nombre de grado ya existe en este colegio. Por favor, ingresa uno diferente.',
                            ]),
                            
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),
                    ])
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
                    ->badge(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Grado')
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('school_id')
                    ->relationship('school', 'name')
                    ->label('Filtrar por Colegio'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                // SOLUCIÓN AL PANTALLAZO NEGRO:
                // En lugar de intentar borrar y fallar, deshabilitamos el botón.
                Tables\Actions\DeleteAction::make()
                    ->modalHeading('Eliminar Grado')

                    // 2. Mensaje de advertencia (Cuerpo del modal)
                    ->modalDescription('¿Estás seguro de que deseas eliminar este grado? Esta acción no se puede deshacer.')

                    // 3. Texto del botón de confirmar
                    ->modalSubmitActionLabel('Sí, eliminar')

                    // 4. Texto del botón de cancelar
                    ->modalCancelActionLabel('Cancelar')

                    // Tu lógica existente (MANTENER ESTO)
                    ->disabled(fn(Grade $record) => $record->students()->exists())
                    ->tooltip(fn(Grade $record) => $record->students()->exists()
                        ? 'No se puede eliminar: Tiene alumnos matriculados'
                        : 'Eliminar Grado'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGrades::route('/'),
            'create' => Pages\CreateGrade::route('/create'),
            'edit' => Pages\EditGrade::route('/{record}/edit'),
        ];
    }
}
