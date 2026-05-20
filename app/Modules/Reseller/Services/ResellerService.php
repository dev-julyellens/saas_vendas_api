<?php

declare(strict_types=1);

namespace App\Modules\Reseller\Services;

use App\Modules\Reseller\Models\Reseller;
use App\Modules\Reseller\Repositories\ResellerRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ResellerService
{
    public function __construct(private ResellerRepository $repository)
    {
    }

    public function paginate(int $perPage, array $filters = []): LengthAwarePaginator
    {
        return $this->repository->paginateWithFilters($perPage, $filters);
    }

    public function find(string $id): Reseller
    {
        return Reseller::query()
            ->with('representative:id,name')
            ->findOrFail($id);
    }

    public function store(array $data): Reseller
    {
        return $this->repository->create($data);
    }

    public function update(string $id, array $data): Reseller
    {
        return $this->repository->update($id, $data);
    }

    public function delete(string $id): void
    {
        $this->repository->delete($id);
    }
}
