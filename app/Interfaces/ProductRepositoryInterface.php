<?php

namespace App\Interfaces;

interface ProductRepositoryInterface
{
    public function store(array $data);
    public function getAll();
    public function findById($id);
    public function update($id, array $data);
    public function delete($id);
    public function paginate($perPage, $filters = []);
}
