<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetServiceTaskResource\Pages;
use App\Models\Asset;
use App\Models\AssetServiceTask;
use App\Services\Contracts\AssetServiceInterface;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class AssetServiceTaskResource extends Resource
{
    protected static ?string $model = AssetServiceTask::class;

    protected static ?string $navigationGroup = 'operations';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-list';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('asset_id')
                ->relationship('asset', 'name')
                ->getOptionLabelFromRecordUsing(fn (Asset $record): string => $record->display_name)
                ->searchable(['name', 'asset_tag', 'serial'])
                ->required(),
            Forms\Components\Select::make('service_plan_id')
                ->relationship('servicePlan', 'name')
                ->searchable(),
            Forms\Components\Select::make('status_id')
                ->relationship('status', 'name')
                ->searchable()
                ->required(),
            Forms\Components\Select::make('assigned_to_user_id')
                ->relationship('assignee', 'name')
                ->searchable(),
            Forms\Components\TextInput::make('title')
                ->required()
                ->maxLength(255),
            Forms\Components\Textarea::make('description'),
            Forms\Components\DateTimePicker::make('due_at'),
            Forms\Components\DateTimePicker::make('completed_at'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('asset.name')
                    ->label('Asset')
                    ->formatStateUsing(fn ($state, AssetServiceTask $record): string => $record->asset?->display_name ?? (string) $state)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status.name')->label('Status')->toggleable(),
                Tables\Columns\TextColumn::make('assignee.name')->label('Assignee')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('due_at')->dateTime()->toggleable(),
                Tables\Columns\TextColumn::make('completed_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_id')->relationship('status', 'name'),
                Tables\Filters\SelectFilter::make('assigned_to_user_id')->relationship('assignee', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('complete')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (AssetServiceTask $record): void {
                        /** @var AssetServiceInterface $assetService */
                        $assetService = app(AssetServiceInterface::class);
                        $assetService->completeServiceTask($record, [], auth()->user());
                    })
                    ->requiresConfirmation(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\BulkAction::make('complete_selected')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function ($records): void {
                        /** @var AssetServiceInterface $assetService */
                        $assetService = app(AssetServiceInterface::class);

                        foreach ($records as $record) {
                            $assetService->completeServiceTask($record, [], auth()->user());
                        }
                    })
                    ->requiresConfirmation(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssetServiceTasks::route('/'),
            'create' => Pages\CreateAssetServiceTask::route('/create'),
            'edit' => Pages\EditAssetServiceTask::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'asset:id,name,asset_tag,serial,metadata,assigned_user_id',
                'asset.assignedUser:id,name',
                'status:id,name',
                'assignee:id,name',
            ]);
    }
}
