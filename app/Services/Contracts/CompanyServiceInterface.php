<?php

namespace App\Services\Contracts;

use App\Models\Provider;
use App\Models\User;

interface CompanyServiceInterface
{
    public function createProvider(array $data, User $actor): Provider;

    public function updateProvider(Provider $provider, array $data, User $actor): Provider;
}
