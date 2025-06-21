<?php

namespace App\Interfaces;

interface BrandRepositoryInterface
{
    public function store(array $data);
    public function getAll(); 
}
