<?php

namespace App\Repositories;

use App\Models\Brand;
use App\Interfaces\BrandRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BrandRepository implements BrandRepositoryInterface
{
    // ✅ Hàm xử lý upload và xóa ảnh (tái sử dụng)
    private function handleLogoUpload($file, $oldPath = null)
    {
        if ($oldPath && Storage::disk('public')->exists(str_replace('/storage/', '', $oldPath))) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $oldPath));
        }

        $path = $file->store('brands', 'public');
        return Storage::url($path);
    }

    public function store(array $data)
    {
        $slug = Str::slug($data['name']);

        if (Brand::where('slug', $slug)->exists()) {
            throw ValidationException::withMessages([
                'name' => ['Tên thương hiệu đã tồn tại.']
            ]);
        }

        $data['slug'] = $slug;

        if (isset($data['logo']) && $data['logo']) {
            $data['logo'] = $this->handleLogoUpload($data['logo']);
        }

        return Brand::create($data);
    }

    public function getAll()
    {
        return Brand::select(['id', 'name', 'slug', 'logo', 'country'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function findById($id)
    {
        return Brand::findOrFail($id);
    }

    public function update($id, array $data)
    {
        $brand = $this->findById($id);
        $slug = Str::slug($data['name']);

        if (Brand::where('slug', $slug)->where('id', '!=', $id)->exists()) {
            throw ValidationException::withMessages([
                'name' => ['Tên thương hiệu đã tồn tại.']
            ]);
        }

        $data['slug'] = $slug;

        if (isset($data['logo']) && $data['logo']) {
            $data['logo'] = $this->handleLogoUpload($data['logo'], $brand->logo);
        }

        $brand->update($data);
        return $brand;
    }

    public function delete($id)
    {
        $brand = $this->findById($id);

        if ($brand->logo) {
            $this->handleLogoUpload(null, $brand->logo); // chỉ xóa ảnh cũ
        }

        return $brand->delete();
    }

    public function paginate($perPage = 10)
    {
        return Brand::select(['id', 'name', 'slug', 'logo', 'country'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }
}
