<?php

namespace App\Services;

use App\Models\Provider;
use App\Models\User;
use App\Services\Contracts\CompanyServiceInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CompanyService implements CompanyServiceInterface
{
    public function createProvider(array $data, User $actor): Provider
    {
        $companyId = $this->requiredCompanyId($actor);

        return DB::transaction(function () use ($data, $companyId): Provider {
            $provider = new Provider;
            $provider->fill(array_merge($data, ['company_id' => $companyId]));
            $provider->save();

            return $provider;
        });
    }

    public function updateProvider(Provider $provider, array $data, User $actor): Provider
    {
        $companyId = $this->requiredCompanyId($actor);
        if ((int) $provider->company_id !== $companyId) {
            throw new InvalidArgumentException('Cross-tenant provider update denied.');
        }

        return DB::transaction(function () use ($provider, $data): Provider {
            $provider->fill($data);
            $provider->save();

            return $provider;
        });
    }

    private function requiredCompanyId(User $actor): int
    {
        if (empty($actor->current_company_id)) {
            throw new InvalidArgumentException('A selected company is required for tenant-owned write operations.');
        }

        return (int) $actor->current_company_id;
    }
}
