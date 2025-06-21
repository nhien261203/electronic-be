<?php

namespace App\Interfaces;

interface BrandRepositoryInterface
{
    public function store(array $data);
    public function getAll();
    public function findById($id);
    public function update($id, array $data);
    public function delete($id);
}
