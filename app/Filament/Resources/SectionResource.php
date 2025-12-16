<?php

namespace App\Filament\Resources;

use Illuminate\Validation\Rules\Unique; 
use Filament\Forms\Get;

use App\Filament\Resources\SectionResource\Pages;
use App\Models\Section;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SectionResource extends Resource
{
    protected static ?string $model = Section::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationGroup = 'Gestión Académica';
    protected static ?string $navigationLabel = 'Secciones';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        // Selector de Colegio (Obligatorio en Admin)
                        Forms\Components\Select::make('school_id')
                            ->relationship('school', 'name')
                            ->label('Colegio')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('name')
                            ->label('Nombre de Sección')
                            ->placeholder('Ej: A, B, Única, Roja')
                            ->required()
                            ->maxLength(255)
                            ->unique(
                                ignoreRecord: true,
                                modifyRuleUsing: function (Unique $rule, Get $get) {
                                    return $rule->where('school_id', $get('school_id'));
                                }
                            )
                            ->validationMessages([
                                'unique' => 'Esta sección ya existe en el colegio seleccionado.',
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
                    ->badge(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Sección')
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
                Tables\Actions\DeleteAction::make()
                    ->disabled(fn (Section $record) => $record->students()->exists())
                    ->tooltip(fn (Section $record) => $record->students()->exists() 
                        ? 'No se puede eliminar: Tiene alumnos asignados' 
                        : 'Eliminar Sección'),
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
            'index' => Pages\ListSections::route('/'),
            'create' => Pages\CreateSection::route('/create'),
            'edit' => Pages\EditSection::route('/{record}/edit'),
        ];
    }
}