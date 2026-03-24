<?php

namespace App\Services;

use App\Events\AssetUpserted;
use App\Events\AssetServiceReminderScheduled;
use App\Events\AssetServiceTaskGenerated;
use App\Models\Asset;
use App\Models\AssetServicePlan;
use App\Models\AssetServiceReminder;
use App\Models\AssetServiceTask;
use App\Models\AssetServiceTaskStatus;
use App\Models\Hardware;
use App\Models\Peripheral;
use App\Models\Provider;
use App\Models\Software;
use App\Models\User;
use App\Services\Contracts\AssetServiceInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AssetService implements AssetServiceInterface
{
    private const SERVICE_STATUS_OPEN = 'open';
    private const SERVICE_STATUS_COMPLETED = 'completed';

    public function createHardware(array $data, User $actor): Hardware
    {
        $companyId = $this->requiredCompanyId($actor);
        $this->assertProviderBelongsToCompany((int) $data['provaider_id'], $companyId);
        $this->assertAssigneeBelongsToCompany($data['user_id'] ?? null, $companyId);

        return DB::transaction(function () use ($data, $companyId, $actor): Hardware {
            $hardware = new Hardware();
            $hardware->fill(array_merge($data, ['company_id' => $companyId]));
            $hardware->save();

            event(new AssetUpserted('hardware', (int) $hardware->id, $companyId, (int) $actor->id, 'created'));

            return $hardware;
        });
    }

    public function updateHardware(Hardware $hardware, array $data, User $actor): Hardware
    {
        $companyId = $this->requiredCompanyId($actor);
        $this->assertTenantRecord($hardware->company_id, $companyId);
        $this->assertProviderBelongsToCompany((int) ($data['provaider_id'] ?? $hardware->provaider_id), $companyId);
        $this->assertAssigneeBelongsToCompany($data['user_id'] ?? $hardware->user_id, $companyId);

        return DB::transaction(function () use ($hardware, $data, $companyId, $actor): Hardware {
            $hardware->fill($data);
            $hardware->save();

            event(new AssetUpserted('hardware', (int) $hardware->id, $companyId, (int) $actor->id, 'updated'));

            return $hardware;
        });
    }

    public function createSoftware(array $data, User $actor): Software
    {
        $companyId = $this->requiredCompanyId($actor);
        $this->assertProviderBelongsToCompany((int) $data['provaider_id'], $companyId);

        return DB::transaction(function () use ($data, $companyId, $actor): Software {
            $software = new Software();
            $software->fill(array_merge($data, ['company_id' => $companyId]));
            $software->save();

            event(new AssetUpserted('software', (int) $software->id, $companyId, (int) $actor->id, 'created'));

            return $software;
        });
    }

    public function updateSoftware(Software $software, array $data, User $actor): Software
    {
        $companyId = $this->requiredCompanyId($actor);
        $this->assertTenantRecord($software->company_id, $companyId);
        $this->assertProviderBelongsToCompany((int) ($data['provaider_id'] ?? $software->provaider_id), $companyId);

        return DB::transaction(function () use ($software, $data, $companyId, $actor): Software {
            $software->fill($data);
            $software->save();

            event(new AssetUpserted('software', (int) $software->id, $companyId, (int) $actor->id, 'updated'));

            return $software;
        });
    }

    public function createPeripheral(array $data, User $actor): Peripheral
    {
        $companyId = $this->requiredCompanyId($actor);
        $this->assertProviderBelongsToCompany((int) $data['provaider_id'], $companyId);
        $this->assertAssigneeBelongsToCompany($data['user_id'] ?? null, $companyId);

        return DB::transaction(function () use ($data, $companyId, $actor): Peripheral {
            $peripheral = new Peripheral();
            $peripheral->fill(array_merge($data, ['company_id' => $companyId]));
            $peripheral->save();

            event(new AssetUpserted('peripheral', (int) $peripheral->id, $companyId, (int) $actor->id, 'created'));

            return $peripheral;
        });
    }

    public function updatePeripheral(Peripheral $peripheral, array $data, User $actor): Peripheral
    {
        $companyId = $this->requiredCompanyId($actor);
        $this->assertTenantRecord($peripheral->company_id, $companyId);
        $this->assertProviderBelongsToCompany((int) ($data['provaider_id'] ?? $peripheral->provaider_id), $companyId);
        $this->assertAssigneeBelongsToCompany($data['user_id'] ?? $peripheral->user_id, $companyId);

        return DB::transaction(function () use ($peripheral, $data, $companyId, $actor): Peripheral {
            $peripheral->fill($data);
            $peripheral->save();

            event(new AssetUpserted('peripheral', (int) $peripheral->id, $companyId, (int) $actor->id, 'updated'));

            return $peripheral;
        });
    }

    public function createServicePlan(int $assetId, array $data, User $actor): AssetServicePlan
    {
        $companyId = $this->requiredCompanyId($actor);

        $asset = Asset::query()
            ->whereKey($assetId)
            ->where('company_id', $companyId)
            ->firstOrFail();

        $this->assertAssigneeBelongsToCompany($data['default_assigned_user_id'] ?? null, $companyId);

        return DB::transaction(function () use ($asset, $data, $companyId): AssetServicePlan {
            $plan = new AssetServicePlan();
            $plan->fill(array_merge($data, [
                'company_id' => $companyId,
                'asset_id' => $asset->id,
            ]));
            $plan->save();

            return $plan;
        });
    }

    public function generateDueServiceTasks(\DateTimeInterface $asOf, User $actor): int
    {
        $companyId = $this->requiredCompanyId($actor);
        $openStatusId = $this->serviceStatusIdByCode(self::SERVICE_STATUS_OPEN);
        $generated = 0;

        $plans = AssetServicePlan::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->whereNotNull('next_due_at')
            ->where('next_due_at', '<=', $asOf)
            ->get();

        DB::transaction(function () use ($plans, $openStatusId, $asOf, $companyId, $actor, &$generated): void {
            foreach ($plans as $plan) {
                $alreadyOpen = AssetServiceTask::query()
                    ->where('company_id', $companyId)
                    ->where('service_plan_id', $plan->id)
                    ->where('status_id', $openStatusId)
                    ->exists();

                if ($alreadyOpen) {
                    continue;
                }

                $task = new AssetServiceTask();
                $task->fill([
                    'company_id' => $companyId,
                    'asset_id' => $plan->asset_id,
                    'service_plan_id' => $plan->id,
                    'status_id' => $openStatusId,
                    'assigned_to_user_id' => $plan->default_assigned_user_id,
                    'created_by_user_id' => $actor->id,
                    'title' => 'Scheduled service: '.$plan->name,
                    'description' => $plan->instructions,
                    'due_at' => $plan->next_due_at,
                ]);
                $task->save();

                $plan->next_due_at = \Carbon\Carbon::parse($plan->next_due_at)->addDays((int) $plan->service_interval_days);
                $plan->save();

                $this->scheduleReminderForTask($task, (int) $plan->reminder_days_before, $companyId);
                event(new AssetServiceTaskGenerated((int) $task->id, (int) $task->asset_id, $companyId));
                $generated++;
            }
        });

        return $generated;
    }

    public function completeServiceTask(AssetServiceTask $serviceTask, array $data, User $actor): AssetServiceTask
    {
        $companyId = $this->requiredCompanyId($actor);
        $this->assertTenantRecord((int) $serviceTask->company_id, $companyId);
        $completedStatusId = $this->serviceStatusIdByCode(self::SERVICE_STATUS_COMPLETED);

        return DB::transaction(function () use ($serviceTask, $data, $completedStatusId): AssetServiceTask {
            $serviceTask->fill($data);
            $serviceTask->status_id = $completedStatusId;
            $serviceTask->completed_at = now();
            $serviceTask->save();

            if ($serviceTask->service_plan_id) {
                AssetServicePlan::query()
                    ->whereKey($serviceTask->service_plan_id)
                    ->update(['last_completed_at' => now()]);
            }

            return $serviceTask;
        });
    }

    private function requiredCompanyId(User $actor): int
    {
        if (empty($actor->current_company_id)) {
            throw new InvalidArgumentException('A selected company is required for tenant-owned write operations.');
        }

        return (int) $actor->current_company_id;
    }

    private function assertTenantRecord(int $recordCompanyId, int $actorCompanyId): void
    {
        if ($recordCompanyId !== $actorCompanyId) {
            throw new InvalidArgumentException('Cross-tenant write operation denied.');
        }
    }

    private function assertProviderBelongsToCompany(int $providerId, int $companyId): void
    {
        $exists = Provider::query()
            ->whereKey($providerId)
            ->where('company_id', $companyId)
            ->exists();

        if (! $exists) {
            throw new InvalidArgumentException('Provider does not belong to current company.');
        }
    }

    private function assertAssigneeBelongsToCompany(?int $userId, int $companyId): void
    {
        if (is_null($userId)) {
            return;
        }

        $exists = DB::table('company_user')
            ->where('company_id', $companyId)
            ->where('user_id', $userId)
            ->exists();

        if (! $exists) {
            throw new InvalidArgumentException('Assignee must belong to current company.');
        }
    }

    private function serviceStatusIdByCode(string $code): int
    {
        return (int) AssetServiceTaskStatus::query()
            ->where('code', $code)
            ->value('id');
    }

    private function scheduleReminderForTask(AssetServiceTask $task, int $reminderDaysBefore, int $companyId): void
    {
        if (! $task->due_at || ! $task->assigned_to_user_id) {
            return;
        }

        $remindAt = \Carbon\Carbon::parse($task->due_at)->subDays(max(0, $reminderDaysBefore));

        $reminder = new AssetServiceReminder();
        $reminder->fill([
            'company_id' => $companyId,
            'service_task_id' => $task->id,
            'recipient_user_id' => $task->assigned_to_user_id,
            'remind_at' => $remindAt,
            'message' => 'Upcoming recurring service task is due soon.',
        ]);
        $reminder->save();

        event(new AssetServiceReminderScheduled((int) $reminder->id, (int) $task->id, $companyId));
    }
}
