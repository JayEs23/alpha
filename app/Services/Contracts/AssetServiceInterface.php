<?php

namespace App\Services\Contracts;

use App\Models\Hardware;
use App\Models\Asset;
use App\Models\AssetServicePlan;
use App\Models\AssetServiceTask;
use App\Models\Peripheral;
use App\Models\Software;
use App\Models\User;

interface AssetServiceInterface
{
    public function createAsset(array $data, User $actor): Asset;

    public function updateAsset(Asset $asset, array $data, User $actor): Asset;

    public function createHardware(array $data, User $actor): Hardware;

    public function updateHardware(Hardware $hardware, array $data, User $actor): Hardware;

    public function createSoftware(array $data, User $actor): Software;

    public function updateSoftware(Software $software, array $data, User $actor): Software;

    public function createPeripheral(array $data, User $actor): Peripheral;

    public function updatePeripheral(Peripheral $peripheral, array $data, User $actor): Peripheral;

    public function createServicePlan(int $assetId, array $data, User $actor): AssetServicePlan;

    public function updateServicePlan(AssetServicePlan $servicePlan, array $data, User $actor): AssetServicePlan;

    public function createServiceTask(array $data, User $actor): AssetServiceTask;

    public function updateServiceTask(AssetServiceTask $serviceTask, array $data, User $actor): AssetServiceTask;

    public function generateDueServiceTasks(\DateTimeInterface $asOf, User $actor): int;

    public function completeServiceTask(AssetServiceTask $serviceTask, array $data, User $actor): AssetServiceTask;
}
