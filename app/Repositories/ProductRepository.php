<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\ProductImage;
use App\Interfaces\ProductRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Facades\Image;
use DB;

class ProductRepository implements ProductRepositoryInterface
{
    private function handleImageUpload($file)
    {
        $image = Image::make($file)
            ->resize(600, 600, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->encode('webp', 80);

        $filename = 'products/' . uniqid('product_') . '.webp';
        Storage::disk('public')->put($filename, $image);

        return Storage::url($filename);
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $product = Product::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'price' => $data['price'],
                'original_price' => $data['original_price'] ?? null,
                'quantity' => $data['quantity'] ?? 0,
                'sold' => $data['sold'] ?? 0,
                'category_id' => $data['category_id'],
                'brand_id' => $data['brand_id'],
                'status' => $data['status'] ?? 1,
            ]);

            // Xử lý upload ảnh sản phẩm nếu có
            if (!empty($data['images']) && is_array($data['images'])) {
                $thumbnailIndex = isset($data['thumbnail_index']) ? (int)$data['thumbnail_index'] : 0;

                foreach ($data['images'] as $index => $imageFile) {
                    $imageUrl = $this->handleImageUpload($imageFile);

                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_url' => $imageUrl,
                        'is_thumbnail' => $index === $thumbnailIndex,
                    ]);
                }
            }

            return $product->load('images', 'brand', 'category');
        });
    }

    public function getAll()
    {
        return Product::with(['images', 'brand', 'category'])
            ->where('status', 1)
            ->orderByDesc('created_at')
            ->get();
    }

    public function findById($id)
    {
        return Product::with(['brand', 'category', 'images'])->findOrFail($id);
    }

    public function update($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $product = $this->findById($id);

            $product->update([
                'name' => $data['name'] ?? $product->name,
                'description' => $data['description'] ?? $product->description,
                'price' => $data['price'] ?? $product->price,
                'original_price' => $data['original_price'] ?? $product->original_price,
                'quantity' => $data['quantity'] ?? $product->quantity,
                'sold' => $data['sold'] ?? $product->sold,
                'category_id' => $data['category_id'] ?? $product->category_id,
                'brand_id' => $data['brand_id'] ?? $product->brand_id,
                'status' => $data['status'] ?? $product->status,
            ]);

            if (!empty($data['images']) && is_array($data['images'])) {
                $thumbnailIndex = isset($data['thumbnail_index']) ? (int)$data['thumbnail_index'] : 0;

                // Xoá ảnh cũ
                foreach ($product->images as $oldImage) {
                    $relativePath = str_replace('/storage/', '', $oldImage->image_url);
                    if (Storage::disk('public')->exists($relativePath)) {
                        Storage::disk('public')->delete($relativePath);
                    }
                    $oldImage->delete();
                }

                // Upload ảnh mới
                foreach ($data['images'] as $index => $imageFile) {
                    $imageUrl = $this->handleImageUpload($imageFile);

                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_url' => $imageUrl,
                        'is_thumbnail' => $index === $thumbnailIndex,
                    ]);
                }
            }

            return $product->load('images', 'brand', 'category');
        });
    }


    public function delete($id)
    {
        $product = $this->findById($id);

        // Xóa ảnh liên quan
        foreach ($product->images as $image) {
            $relativePath = str_replace('/storage/', '', $image->image_url);
            if (Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->delete($relativePath);
            }
        }

        return $product->delete();
    }

    public function paginate($perPage = 10, $filters = [])
    {
        $query = Product::with(['images', 'brand', 'category']);

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        if (!empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }
}
