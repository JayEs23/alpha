<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Models\Task;
use App\Models\WorkflowStatus;
use App\Services\Contracts\TaskServiceInterface;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationGroup = 'operations';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-check';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('project_id')->relationship('project', 'name')->required(),
            Forms\Components\TextInput::make('title')->required()->maxLength(255),
            Forms\Components\Textarea::make('description'),
            Forms\Components\Select::make('status_id')->relationship('status', 'name')->required(),
            Forms\Components\Select::make('priority_id')->relationship('priority', 'name')->required(),
            Forms\Components\Select::make('assignee_user_id')->relationship('assignee', 'name'),
            Forms\Components\DateTimePicker::make('due_at'),
            Forms\Components\TextInput::make('estimate_minutes')->numeric()->minValue(0),
            Forms\Components\TextInput::make('actual_minutes')->numeric()->minValue(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('project.key')->label('Project')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('task_number')->label('#')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('status.name')->label('Status')->toggleable(),
                Tables\Columns\TextColumn::make('priority.name')->label('Priority')->toggleable(),
                Tables\Columns\TextColumn::make('assignee.name')->label('Assignee')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('due_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_id')->relationship('status', 'name'),
                Tables\Filters\SelectFilter::make('priority_id')->relationship('priority', 'name'),
                Tables\Filters\SelectFilter::make('assignee_user_id')->relationship('assignee', 'name'),
                Tables\Filters\Filter::make('overdue')
                    ->query(fn ($query) => $query->whereNotNull('due_at')->where('due_at', '<', now())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('transition')
                    ->form([
                        Forms\Components\Select::make('to_status_id')
                            ->label('Move To Status')
                            ->options(fn () => WorkflowStatus::query()->orderBy('sort_order')->pluck('name', 'id')->all())
                            ->required(),
                    ])
                    ->action(function (Task $record, array $data): void {
                        /** @var TaskServiceInterface $taskService */
                        $taskService = app(TaskServiceInterface::class);
                        $taskService->transitionTask((int) $record->id, (int) $data['to_status_id'], auth()->user());
                    }),
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
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'view' => Pages\ViewTask::route('/{record}'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'project:id,key',
                'status:id,name',
                'priority:id,name',
                'assignee:id,name',
            ]);
    }
}
