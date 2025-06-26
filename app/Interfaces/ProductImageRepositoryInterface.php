<?php

namespace App\Interfaces;

interface ProductImageRepositoryInterface
{
    public function getByProduct($productId);
    public function storeImages($productId, array $images);
    public function setThumbnail($imageId);
    public function delete($id);
}
