<?php

namespace App\Interfaces;

interface CategoryRepositoryInterface
{
    public function store(array $data);
    public function getAll();
    public function paginate($perPage, $search);
    public function findById($id);
    public function update($id, array $data);
    public function delete($id);
}
