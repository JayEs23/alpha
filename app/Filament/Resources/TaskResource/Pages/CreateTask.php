<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use App\Models\Task;
use App\Services\Contracts\TaskServiceInterface;
use Filament\Resources\Pages\CreateRecord;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;

    protected function handleRecordCreation(array $data): Task
    {
        /** @var TaskServiceInterface $taskService */
        $taskService = app(TaskServiceInterface::class);

        return $taskService->createTask($data, auth()->user());
    }
}
