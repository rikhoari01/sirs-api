<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Interfaces\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @template TModel of Model
 * @implements BaseRepositoryInterface<TModel>
 */
class BaseRepository implements BaseRepositoryInterface
{
    /**
     * @var Model|Builder
     */
    public Model|Builder $model;

    /**
     * @param array $data
     * @return TModel
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * @param int|string $id
     * @return TModel|null
     */
    public function find(int|string $id)
    {
        return $this->model->find($id);
    }

    /**
     * @param int|string $id
     * @param array $data
     * @return bool
     */
    public function update(int|string $id, array $data): bool
    {
        $record = $this->find($id);
        return $record && $record->update($data);
    }

    /**
     * @param int|string|array $id
     * @return int
     */
    public function destroy(int|string|array $id): int
    {
        return $this->model->destroy($id);
    }

    /**
     * @param int $page
     * @param int $perPage
     * @return LengthAwarePaginator<TModel>
     */
    public function paginate(int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->paginate(perPage: $perPage, page: $page);
    }
}
