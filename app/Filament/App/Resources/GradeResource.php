<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\GradeResource\Pages;
use App\Models\Grade;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;
use Filament\Facades\Filament;

class GradeResource extends Resource
{
    protected static ?string $model = Grade::class;
    protected static ?string $navigationIcon = 'heroicon-o-bookmark';
    protected static ?string $navigationLabel = 'Mis Grados';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // NOTA: No pedimos school_id. Filament lo inyecta automático.
                
                Forms\Components\TextInput::make('name')
                    ->label('Nombre del Grado')
                    ->placeholder('Ej: 1ro Primaria')
                    ->required()
                    ->maxLength(255)
                    ->unique(
                        ignoreRecord: true,
                        modifyRuleUsing: function (Unique $rule) {
                            // Validar duplicados SOLO en este colegio
                            return $rule->where('school_id', Filament::getTenant()->id);
                        }
                    )
                    ->validationMessages([
                        'unique' => 'Ya existe un grado con este nombre en tu colegio.',
                    ]),

                Forms\Components\Toggle::make('is_active')
                    ->label('Visible')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Grado')->searchable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn (Grade $record) => $record->students()->exists()),
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