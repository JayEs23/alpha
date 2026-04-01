<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PeripheralResource\Pages;
use App\Models\Peripheral;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PeripheralResource extends Resource
{
    protected static ?string $model = Peripheral::class;
    protected static ?string $modelLabel = 'Peripheral';
    protected static ?string $pluralModelLabel = 'Peripherals';

    protected static ?string $navigationGroup = 'meta';

    protected static ?string $navigationIcon = 'heroicon-o-camera';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('provider_id')
                    ->relationship('provider', 'name')
                    ->label('Provider')
                    ->required(),
                Forms\Components\TextInput::make('make')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('model')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('serial')
                    ->maxLength(255),
                Forms\Components\TextInput::make('type')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('current')
                    ->required(),
                Forms\Components\DateTimePicker::make('purchased_at')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->toggleable(),
                Tables\Columns\TextColumn::make('provider.name')
                    ->label('Provider')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('make')->toggleable(),
                Tables\Columns\TextColumn::make('model')->toggleable(),
                Tables\Columns\TextColumn::make('serial')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('type')->toggleable(),
                Tables\Columns\IconColumn::make('current')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('purchased_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
                \pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction::make(),
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
            'index' => Pages\ListPeripherals::route('/'),
            'create' => Pages\CreatePeripheral::route('/create'),
            'edit' => Pages\EditPeripheral::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'user:id,name',
                'provider:id,name',
            ])
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
