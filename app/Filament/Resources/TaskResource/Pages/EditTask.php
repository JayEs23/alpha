<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use App\Models\Task;
use App\Services\Contracts\TaskServiceInterface;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTask extends EditRecord
{
    protected static string $resource = TaskResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate($record, array $data): Task
    {
        /** @var TaskServiceInterface $taskService */
        $taskService = app(TaskServiceInterface::class);

        return $taskService->updateTask((int) $record->id, $data, auth()->user());
    }
}
