<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetServicePlan;
use App\Models\AssetStatus;
use App\Models\Company;
use App\Models\Hardware;
use App\Models\Provider;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DevAssetsCsvSeeder extends Seeder
{
    private array $seenAssetSerials = [];

    private array $seenHardwareSerials = [];

    public function import(Company $company): void
    {
        $this->seenAssetSerials = [];
        $this->seenHardwareSerials = [];

        $provider = Provider::query()->firstOrCreate(
            ['company_id' => $company->id, 'name' => 'Leapsoft IT'],
            []
        );

        $assignee = User::query()->where('current_company_id', $company->id)->first();

        $base = public_path('devassets');

        $this->importAccessControl($company, $provider, $base);
        $this->importServerAndAudio($company, $provider, $base);
        $this->importTelcoRoom($company, $provider, $base);
        $this->importHeadsets($company, $provider, $base);
        $this->importPcInventory($company, $provider, $assignee?->id);
        $this->importFacilityInventory($company, $provider, $base);

        $firstAsset = Asset::query()->where('company_id', $company->id)
            ->where('asset_tag', 'like', 'LEAP-%')
            ->orderBy('asset_tag')
            ->first();

        if ($firstAsset) {
            AssetServicePlan::query()->firstOrCreate(
                ['company_id' => $company->id, 'asset_id' => $firstAsset->id, 'name' => 'Quarterly Preventive Maintenance'],
                [
                    'service_interval_days' => 90,
                    'reminder_days_before' => 7,
                    'default_assigned_user_id' => $assignee?->id,
                    'next_due_at' => now()->addDays(14),
                    'is_active' => true,
                    'instructions' => 'Perform standard preventive maintenance checklist.',
                ]
            );
        }
    }

    private function path(string $base, string $filename): string
    {
        return $base.DIRECTORY_SEPARATOR.$filename;
    }

    private function stableTag(string $prefix, string $seed): string
    {
        return 'LEAP-'.$prefix.'-'.substr(hash('sha256', $seed), 0, 16);
    }

    private function category(Company $company, string $code): AssetCategory
    {
        return AssetCategory::query()
            ->where('company_id', $company->id)
            ->where('code', $code)
            ->firstOrFail();
    }

    private function status(Company $company, string $code): AssetStatus
    {
        return AssetStatus::query()
            ->where('company_id', $company->id)
            ->where('code', $code)
            ->firstOrFail();
    }

    private function mapAssetStatus(Company $company, string $raw): AssetStatus
    {
        $r = strtolower(trim($raw));

        if ($r === '' || $r === 'n/a') {
            return $this->status($company, 'active');
        }

        if (str_contains($r, 'fault') || str_contains($r, 'not functional') || str_contains($r, 'not funct')) {
            return $this->status($company, 'faulty');
        }

        if (str_contains($r, 'decommission')) {
            return $this->status($company, 'retired');
        }

        if (str_contains($r, 'deployed')) {
            return $this->status($company, 'deployed');
        }

        if (str_contains($r, 'idle')) {
            return $this->status($company, 'idle');
        }

        if (in_array($r, ['working', 'good', 'functinal', 'functional', 'active', 'new', 'fair'], true)) {
            return $this->status($company, 'active');
        }

        return $this->status($company, 'active');
    }

    private function rememberSerial(?string $serial, callable $fn): void
    {
        $s = $serial ? trim($serial) : '';
        if ($s !== '') {
            if (isset($this->seenAssetSerials[$s])) {
                return;
            }
            $this->seenAssetSerials[$s] = true;
        }
        $fn();
    }

    private function importAccessControl(Company $company, Provider $provider, string $base): void
    {
        $file = $this->path($base, 'LEAPSOFT IT  INVENTORY (1) - Access Control.csv');
        if (! is_readable($file)) {
            return;
        }

        $fh = fopen($file, 'r');
        if (! $fh) {
            return;
        }

        fgetcsv($fh);
        $cat = $this->category($company, 'access_control');

        while (($row = fgetcsv($fh)) !== false) {
            if (count($row) < 4) {
                continue;
            }
            $office = trim($row[1] ?? '');
            $model = trim($row[2] ?? '');
            $statRaw = trim($row[3] ?? '');
            if ($office === '' && $model === '') {
                continue;
            }

            $name = $model !== '' ? $model : 'Access control device';
            $st = $this->mapAssetStatus($company, $statRaw);
            $tag = $this->stableTag('AC', $office.'|'.$name);

            Asset::query()->updateOrCreate(
                [
                    'company_id' => $company->id,
                    'asset_tag' => $tag,
                ],
                [
                    'category_id' => $cat->id,
                    'status_id' => $st->id,
                    'provider_id' => $provider->id,
                    'name' => $name,
                    'serial' => null,
                    'metadata' => [
                        'source' => 'access_control.csv',
                        'office' => $office,
                        'status_raw' => $statRaw,
                    ],
                ]
            );
        }

        fclose($fh);
    }

    private function importServerAndAudio(Company $company, Provider $provider, string $base): void
    {
        $file = $this->path($base, 'LEAPSOFT IT  INVENTORY (1) - SERVER & AUDIO CODES INVENTORY.csv');
        if (! is_readable($file)) {
            return;
        }

        $fh = fopen($file, 'r');
        if (! $fh) {
            return;
        }

        fgetcsv($fh);
        $catServer = $this->category($company, 'server_and_storage');
        $catNet = $this->category($company, 'network_equipment');
        $catAudio = $this->category($company, 'audio_codes');

        while (($row = fgetcsv($fh)) !== false) {
            if ($this->rowEmpty($row)) {
                continue;
            }

            $col0 = trim($row[0] ?? '');
            $col1 = trim($row[1] ?? '');
            $col2 = trim($row[2] ?? '');
            $col3 = trim($row[3] ?? '');
            $col4 = trim($row[4] ?? '');
            $col5 = trim($row[5] ?? '');
            $col6 = trim($row[6] ?? '');
            $col7 = trim($row[7] ?? '');
            $col8 = trim($row[8] ?? '');

            if (strtolower($col0) === 's/n') {
                continue;
            }

            // Alternate block (e.g. HPE / FortiGate rows)
            if ($col0 === '' && $col1 !== '') {
                $name = $col1;
                $serial = $col2 !== '' && preg_match('/^[A-Z0-9][A-Z0-9\-\/\s]+$/i', $col2) ? $col2 : null;
                $modelCode = $col3;
                $stRaw = $col4 !== '' ? $col4 : 'Working';
                $os = $col7;

                $upper = strtoupper($name);
                if (str_starts_with($upper, 'AUDIO CODES')) {
                    $cat = $catAudio;
                } elseif (str_contains($upper, 'FORTIGATE') || str_contains($upper, 'SWITCH') || str_contains($upper, 'ROUTER')) {
                    $cat = $catNet;
                } else {
                    $cat = $catServer;
                }

                $tag = $this->stableTag('SRV', $name.'|'.$col2.'|'.$col3.'|'.$col4);

                $this->rememberSerial($serial, function () use ($company, $provider, $cat, $name, $serial, $modelCode, $stRaw, $os, $tag): void {
                    $st = $this->mapAssetStatus($company, $stRaw);
                    Asset::query()->updateOrCreate(
                        [
                            'company_id' => $company->id,
                            'asset_tag' => $tag,
                        ],
                        [
                            'category_id' => $cat->id,
                            'status_id' => $st->id,
                            'provider_id' => $provider->id,
                            'name' => $name,
                            'serial' => $serial,
                            'metadata' => [
                                'source' => 'server_audio.csv',
                                'model_code' => $modelCode,
                                'os_version' => $os,
                                'status_raw' => $stRaw,
                                'row_style' => 'alternate',
                            ],
                        ]
                    );
                });

                continue;
            }

            $name = $col1 !== '' ? $col1 : 'Unnamed device';
            $desc = $col2;
            $qty = $col3;
            $stRaw = $col4;
            $license = $col5;
            $licStatus = $col6;
            $os = $col7;
            $rack = $col8;

            $upper = strtoupper($name);
            if (str_contains($upper, 'SWITCH') || str_contains($upper, 'PATCH') || str_contains($upper, 'ROUTER') || str_contains($upper, 'CYBEROAM') || str_contains($upper, 'FORTIGATE')) {
                $cat = $catNet;
            } elseif (str_contains($upper, 'AUDIO') || str_contains($upper, 'ALTIGEN')) {
                $cat = $catAudio;
            } else {
                $cat = $catServer;
            }

            $st = $this->mapAssetStatus($company, $stRaw);
            $tag = $this->stableTag('SRV', $name.'|'.$desc.'|'.$qty.'|'.$rack.'|'.$stRaw);

            Asset::query()->updateOrCreate(
                [
                    'company_id' => $company->id,
                    'asset_tag' => $tag,
                ],
                [
                    'category_id' => $cat->id,
                    'status_id' => $st->id,
                    'provider_id' => $provider->id,
                    'name' => $name,
                    'serial' => null,
                    'metadata' => [
                        'source' => 'server_audio.csv',
                        'description' => $desc,
                        'quantity' => $qty,
                        'rack' => $rack,
                        'license' => $license,
                        'license_status' => $licStatus,
                        'os_version' => $os,
                        'status_raw' => $stRaw,
                    ],
                ]
            );
        }

        fclose($fh);
    }

    private function importTelcoRoom(Company $company, Provider $provider, string $base): void
    {
        $file = $this->path($base, 'LEAPSOFT IT  INVENTORY (1) - TELCO ROOM.csv');
        if (! is_readable($file)) {
            return;
        }

        $fh = fopen($file, 'r');
        if (! $fh) {
            return;
        }

        fgetcsv($fh);
        $cat = $this->category($company, 'telco_room');

        while (($row = fgetcsv($fh)) !== false) {
            if ($this->rowEmpty($row)) {
                continue;
            }

            $name = trim($row[1] ?? '');
            if ($name === '' || strtolower($name) === 'device name') {
                continue;
            }

            $desc = trim($row[2] ?? '');
            $qty = trim($row[3] ?? '');
            $rack = trim($row[5] ?? '');
            $stRaw = trim($row[4] ?? '');
            $st = $this->mapAssetStatus($company, $stRaw);
            $tag = $this->stableTag('TEL', $name.'|'.$desc.'|'.$qty.'|'.$rack);

            Asset::query()->updateOrCreate(
                [
                    'company_id' => $company->id,
                    'asset_tag' => $tag,
                ],
                [
                    'category_id' => $cat->id,
                    'status_id' => $st->id,
                    'provider_id' => $provider->id,
                    'name' => $name,
                    'serial' => null,
                    'metadata' => [
                        'source' => 'telco_room.csv',
                        'description' => $desc,
                        'quantity' => $qty,
                        'rack' => $rack,
                        'status_raw' => $stRaw,
                    ],
                ]
            );
        }

        fclose($fh);
    }

    private function importHeadsets(Company $company, Provider $provider, string $base): void
    {
        $file = $this->path($base, 'LEAPSOFT IT  INVENTORY (1) - HEADSET INVENTORY.csv');
        if (! is_readable($file)) {
            return;
        }

        $fh = fopen($file, 'r');
        if (! $fh) {
            return;
        }

        fgetcsv($fh);
        $cat = $this->category($company, 'headset');

        while (($row = fgetcsv($fh)) !== false) {
            $serial = trim($row[1] ?? '');
            if ($serial === '' || strlen($serial) < 8 || ! preg_match('/^[A-Z0-9]+$/i', $serial)) {
                continue;
            }

            $statusRaw = trim($row[2] ?? '');
            $ws = trim($row[3] ?? '');
            $st = $this->mapAssetStatus($company, $statusRaw !== '' ? $statusRaw : 'DEPLOYED');

            $this->rememberSerial($serial, function () use ($company, $provider, $cat, $serial, $st, $ws, $statusRaw): void {
                $tag = 'LEAP-HS-'.$serial;
                Asset::query()->updateOrCreate(
                    [
                        'company_id' => $company->id,
                        'asset_tag' => $tag,
                    ],
                    [
                        'category_id' => $cat->id,
                        'status_id' => $st->id,
                        'provider_id' => $provider->id,
                        'name' => 'Headset '.$serial,
                        'serial' => $serial,
                        'metadata' => [
                            'source' => 'headset.csv',
                            'workstation' => $ws,
                            'status_raw' => $statusRaw,
                        ],
                    ]
                );
            });
        }

        fclose($fh);
    }

    /**
     * @param  array<int, string|null>  $row
     */
    private function rowEmpty(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    private function importPcInventory(Company $company, Provider $provider, ?int $userId): void
    {
        $file = $this->path(public_path('devassets'), 'LEAPSOFT IT  INVENTORY (1) - PC INVENTORY.csv');
        if (! is_readable($file)) {
            return;
        }

        $fh = fopen($file, 'r');
        if (! $fh) {
            return;
        }

        fgetcsv($fh);

        while (($row = fgetcsv($fh)) !== false) {
            if (count($row) < 2) {
                continue;
            }

            $serial = trim((string) ($row[1] ?? ''));
            if ($serial === '' || ! preg_match('/^[0-9A-F]{12}$/i', $serial)) {
                continue;
            }

            if (isset($this->seenHardwareSerials[$serial])) {
                continue;
            }
            $this->seenHardwareSerials[$serial] = true;

            $unit = trim((string) ($row[0] ?? ''));
            $dateRaw = trim((string) ($row[2] ?? ''));

            try {
                $purchased = $dateRaw !== '' ? Carbon::parse($dateRaw) : now()->subYear();
            } catch (\Throwable) {
                $purchased = now()->subYear();
            }

            Hardware::query()->updateOrCreate(
                ['company_id' => $company->id, 'serial' => $serial],
                [
                    'make' => 'HP',
                    'model' => 'All-in-One PC',
                    'os_name' => null,
                    'os_version' => null,
                    'type' => 'desktop',
                    'ram' => null,
                    'cpu' => null,
                    'status' => 'Active',
                    'current' => true,
                    'purchased_at' => $purchased,
                    'user_id' => $userId,
                    'provider_id' => $provider->id,
                ]
            );

            $cat = $this->category($company, 'pc_workstation');
            $st = $this->status($company, 'active');
            $tag = 'LEAP-PC-'.$serial;

            Asset::query()->updateOrCreate(
                [
                    'company_id' => $company->id,
                    'asset_tag' => $tag,
                ],
                [
                    'category_id' => $cat->id,
                    'status_id' => $st->id,
                    'provider_id' => $provider->id,
                    'name' => 'PC Workstation '.$serial,
                    'serial' => $serial,
                    'purchased_at' => $purchased->toDateString(),
                    'metadata' => [
                        'source' => 'pc_inventory.csv',
                        'unit_no' => $unit,
                    ],
                ]
            );
        }

        fclose($fh);
    }

    private function importFacilityInventory(Company $company, Provider $provider, string $base): void
    {
        $file = $this->path($base, 'facility_inventry(1) (1) - Sheet1.csv');
        if (! is_readable($file)) {
            return;
        }

        $fh = fopen($file, 'r');
        if (! $fh) {
            return;
        }

        fgetcsv($fh);
        fgetcsv($fh);
        fgetcsv($fh);

        $room = '';
        $category = '';
        $mode = 'standard';
        $catFacility = $this->category($company, 'facility_equipment');

        while (($row = fgetcsv($fh)) !== false) {
            if ($this->rowEmpty($row)) {
                continue;
            }

            $c0 = trim((string) ($row[0] ?? ''));
            $c1 = trim((string) ($row[1] ?? ''));
            $c2 = trim((string) ($row[2] ?? ''));
            $c3 = trim((string) ($row[3] ?? ''));
            $c4 = trim((string) ($row[4] ?? ''));

            if (strtolower($c0) === 'category' && str_contains(strtolower($c1), 'item')) {
                $mode = str_contains(strtolower((string) ($row[2] ?? '')), 'total no') ? 'call_centre' : 'standard';

                continue;
            }

            if ($this->isRoomHeader($row)) {
                $room = $c0;
                $category = '';
                if ($mode === 'call_centre') {
                    $mode = 'standard';
                }

                continue;
            }

            if ($mode === 'call_centre') {
                if ($c1 === '') {
                    continue;
                }
                $item = $c1;
                $facCat = $c0 !== '' ? $c0 : $category;
                if ($c0 !== '') {
                    $category = $c0;
                }

                $tag = $this->stableTag('FAC', $room.'|'.$facCat.'|'.$item.'|'.$c2.'|'.$c3);

                Asset::query()->updateOrCreate(
                    [
                        'company_id' => $company->id,
                        'asset_tag' => $tag,
                    ],
                    [
                        'category_id' => $catFacility->id,
                        'status_id' => $this->status($company, 'active')->id,
                        'provider_id' => $provider->id,
                        'name' => $item,
                        'serial' => null,
                        'metadata' => [
                            'source' => 'facility_inventory.csv',
                            'room' => $room,
                            'facility_category' => $facCat,
                            'mode' => 'call_centre',
                            'total_no' => $c2,
                            'damaged' => $c3,
                            'current_capacity' => $c4,
                        ],
                    ]
                );

                continue;
            }

            if ($c1 === '') {
                continue;
            }

            $item = $c1;
            $facCat = $c0 !== '' ? $c0 : $category;
            if ($c0 !== '') {
                $category = $c0;
            }

            $st = $this->mapAssetStatus($company, $c4);
            $tag = $this->stableTag('FAC', $room.'|'.$facCat.'|'.$item.'|'.$c2.'|'.$c3.'|'.$c4);

            Asset::query()->updateOrCreate(
                [
                    'company_id' => $company->id,
                    'asset_tag' => $tag,
                ],
                [
                    'category_id' => $catFacility->id,
                    'status_id' => $st->id,
                    'provider_id' => $provider->id,
                    'name' => $item,
                    'serial' => null,
                    'metadata' => [
                        'source' => 'facility_inventory.csv',
                        'room' => $room,
                        'facility_category' => $facCat,
                        'quantity' => $c2,
                        'type_detail' => $c3,
                        'status_raw' => $c4,
                    ],
                ]
            );
        }

        fclose($fh);
    }

    /**
     * @param  array<int, string|null>  $row
     */
    private function isRoomHeader(array $row): bool
    {
        $a = trim((string) ($row[0] ?? ''));
        $b = trim((string) ($row[1] ?? ''));
        $c = trim((string) ($row[2] ?? ''));

        if ($a === '' || strtolower($a) === 'category') {
            return false;
        }

        return $b === '' && $c === '';
    }
}
