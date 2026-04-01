<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetServicePlanResource\Pages;
use App\Models\Asset;
use App\Models\AssetServicePlan;
use App\Services\Contracts\AssetServiceInterface;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class AssetServicePlanResource extends Resource
{
    protected static ?string $model = AssetServicePlan::class;

    protected static ?string $navigationGroup = 'operations';

    protected static ?string $navigationIcon = 'heroicon-o-refresh';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('asset_id')
                ->relationship('asset', 'name')
                ->getOptionLabelFromRecordUsing(fn (Asset $record): string => $record->display_name)
                ->searchable(['name', 'asset_tag', 'serial'])
                ->required(),
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('service_interval_days')
                ->numeric()
                ->required(),
            Forms\Components\TextInput::make('reminder_days_before')
                ->numeric()
                ->default(7)
                ->required(),
            Forms\Components\Select::make('default_assigned_user_id')
                ->relationship('defaultAssignedUser', 'name')
                ->searchable(),
            Forms\Components\DateTimePicker::make('next_due_at'),
            Forms\Components\Textarea::make('instructions'),
            Forms\Components\Toggle::make('is_active')->default(true)->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Tables\Actions\Action::make('generate_due_tasks')
                    ->label('Generate Due Tasks')
                    ->icon('heroicon-o-refresh')
                    ->action(function (): void {
                        $user = auth()->user();
                        if ($user === null) {
                            return;
                        }

                        try {
                            /** @var AssetServiceInterface $assetService */
                            $assetService = app(AssetServiceInterface::class);
                            $count = $assetService->generateDueServiceTasks(now(), $user);

                            if ($count > 0) {
                                Notification::make()
                                    ->title(__('Due tasks generated'))
                                    ->body(__('Created :count new service task(s) from plans whose next due date is today or earlier.', ['count' => $count]))
                                    ->success()
                                    ->send();

                                return;
                            }

                            Notification::make()
                                ->title(__('No tasks generated'))
                                ->body(
                                    __('No matching plans: each plan must be active, have a "next due" date on or before now, and must not already have an open service task for that plan.')
                                )
                                ->warning()
                                ->send();
                        } catch (\InvalidArgumentException $e) {
                            Notification::make()
                                ->title(__('Could not generate tasks'))
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation(),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('asset.name')
                    ->label('Asset')
                    ->formatStateUsing(fn ($state, AssetServicePlan $record): string => $record->asset?->display_name ?? (string) $state)
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('service_interval_days')->toggleable(),
                Tables\Columns\TextColumn::make('reminder_days_before')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('defaultAssignedUser.name')->label('Default Assignee')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('next_due_at')->dateTime()->toggleable(),
                Tables\Columns\IconColumn::make('is_active')->boolean()->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssetServicePlans::route('/'),
            'create' => Pages\CreateAssetServicePlan::route('/create'),
            'edit' => Pages\EditAssetServicePlan::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'asset:id,name,asset_tag,serial,metadata,assigned_user_id',
                'asset.assignedUser:id,name',
                'defaultAssignedUser:id,name',
            ]);
    }
}
