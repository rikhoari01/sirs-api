<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @template TModel of Model
 */
interface BaseRepositoryInterface
{
    /**
     * Create a new model record.
     *
     * @param array $data
     * @return TModel
     */
    public function create(array $data);

    /**
     * Find a model by primary key.
     *
     * @param int|string $id
     * @return TModel|null
     */
    public function find(int|string $id);

    /**
     * Update a model by primary key.
     *
     * @param int|string $id
     * @param array $data
     * @return bool
     */
    public function update(int|string $id, array $data): bool;

    /**
     * Delete one or more records by ID.
     *
     * @param int|string|array $id
     * @return int
     */
    public function destroy(int|string|array $id): int;

    /**
     * Paginate records.
     *
     * @param int $page
     * @param int $perPage
     *
     * @return LengthAwarePaginator<TModel>
     */
    public function paginate(int $page = 1, int $perPage = 15,): LengthAwarePaginator;
}
