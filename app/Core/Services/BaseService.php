<?php

namespace App\Core\Services;

use App\Core\Repositories\Contracts\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

/**
 * Camada Service — orquestra regras de negócio, transações e eventos.
 * Controllers permanecem finos (apenas HTTP ↔ DTO ↔ Service).
 */
abstract class BaseService
{
    public function __construct(protected RepositoryInterface $repository)
    {
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function findOrFail(string $id): Model
    {
        return $this->repository->findOrFail($id);
    }

    public function create(array $data): Model
    {
        return $this->repository->create($data);
    }

    public function update(string $id, array $data): Model
    {
        return $this->repository->update($id, $data);
    }

    public function delete(string $id): bool
    {
        return $this->repository->delete($id);
    }
}
