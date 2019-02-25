<?php

namespace App\Repositories\Contracts;

/**
 * Interface RepositoryInterface
 */
interface RepositoryInterface
{
    /**
     * Get all records
     *
     * @param array $columns
     * @return mixed
     */
    public function all($columns = ['*']);

    /**
     * Create pagination
     *
     * @param $perPage
     * @param array $columns
     * @return mixed
     */
    public function paginate($perPage = 1, $columns = ['*']);

    /**
     * Create new record
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data);

    /**
     * Save model
     *
     * @param array $data
     * @return bool
     */
    public function saveModel(array $data);

    /**
     * Update record
     *
     * @param array $data
     * @param $id
     * @return mixed
     */
    public function update(array $data, $id);

    /**
     *  Delete record
     *
     * @param $id
     * @return mixed
     */
    public function delete($id);

    /**
     * Find record
     *
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, $columns = ['*']);

    /**
     * Find record by field
     *
     * @param $field
     * @param $value
     * @param array $columns
     * @return mixed
     */
    public function findBy($field, $value, $columns = ['*']);

    /**
     * Find all records by field
     *
     * @param $field
     * @param $value
     * @param array $columns
     * @return mixed
     */
    public function findAllBy($field, $value, $columns = ['*']);

    /**
     * Find records by condition
     *
     * @param $where
     * @param array $columns
     * @return mixed
     */
    public function findWhere($where, $columns = ['*']);
}
