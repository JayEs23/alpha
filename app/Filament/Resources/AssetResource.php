<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetResource\Pages;
use App\Models\Asset;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AssetResource extends Resource
{
    protected static ?string $model = Asset::class;

    protected static ?string $navigationGroup = 'operations';

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\TextInput::make('asset_tag')->maxLength(255),
            Forms\Components\TextInput::make('serial')->maxLength(255),
            Forms\Components\Select::make('category_id')
                ->relationship('category', 'name')
                ->required(),
            Forms\Components\Select::make('status_id')
                ->relationship('status', 'name')
                ->required(),
            Forms\Components\Select::make('provider_id')
                ->relationship('provider', 'name'),
            Forms\Components\Select::make('assigned_user_id')
                ->relationship('assignedUser', 'name'),
            Forms\Components\DatePicker::make('purchased_at'),
            Forms\Components\DatePicker::make('retired_at'),
            Forms\Components\KeyValue::make('metadata')
                ->keyLabel('Field')
                ->valueLabel('Value'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->wrap()
                    ->limit(48),
                Tables\Columns\TextColumn::make('asset_tag')
                    ->searchable()
                    ->toggleable()
                    ->visibleFrom('sm'),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->toggleable()
                    ->visibleFrom('md'),
                Tables\Columns\BadgeColumn::make('status.name')
                    ->label('Status')
                    ->colors([
                        'success' => fn ($state): bool => is_string($state) && in_array(strtolower((string) $state), ['active', 'working', 'good', 'deployed', 'new'], true),
                        'warning' => fn ($state): bool => is_string($state) && in_array(strtolower((string) $state), ['idle', 'maintenance'], true),
                        'danger' => fn ($state): bool => is_string($state) && str_contains(strtolower((string) $state), 'fault'),
                        'secondary' => fn ($state): bool => is_string($state) && str_contains(strtolower((string) $state), 'retired'),
                    ]),
                Tables\Columns\TextColumn::make('provider.name')
                    ->label('Provider')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visibleFrom('lg'),
                Tables\Columns\TextColumn::make('assignedUser.name')
                    ->label('Assigned')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visibleFrom('xl'),
                Tables\Columns\TextColumn::make('purchased_at')
                    ->date()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visibleFrom('xl'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visibleFrom('2xl'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('category_id')->relationship('category', 'name'),
                Tables\Filters\SelectFilter::make('status_id')->relationship('status', 'name'),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
                    ->label('Actions')
                    ->icon('heroicon-o-dots-vertical')
                    ->tooltip('Actions'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssets::route('/'),
            'create' => Pages\CreateAsset::route('/create'),
            'edit' => Pages\EditAsset::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
