<?php

namespace App\Core\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Contrato Repository — abstrai persistência para testes e troca de storage futura.
 */
interface RepositoryInterface
{
    public function all(array $columns = ['*']): Collection;

    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;

    public function find(string $id, array $columns = ['*']): ?Model;

    public function findOrFail(string $id, array $columns = ['*']): Model;

    public function create(array $data): Model;

    public function update(string $id, array $data): Model;

    public function delete(string $id): bool;
}
