<?php

namespace App\Repositories;

use App\Models\Brand;
use App\Interfaces\BrandRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BrandRepository implements BrandRepositoryInterface
{
    public function store(array $data)
    {
        //Tạo slug từ name
        $slug = Str::slug($data['name']);

        //Kiểm tra slug đã tồn tại hay chưa
        if (Brand::where('slug', $slug)->exists()) {
            throw ValidationException::withMessages([
                'name' => ['Tên thương hiệu đã tồn tại.']
            ]);
        }

        // Gán slug vào data
        $data['slug'] = $slug;

        // hỉ xử lý ảnh nếu đã qua bước validate slug
        if (isset($data['logo']) && $data['logo']) {
            $file = $data['logo'];
            $path = $file->store('brands', 'public');
            $data['logo'] = Storage::url($path); // storage/brands
        }

        //Tạo bản ghi trong database
        return Brand::create($data);
    }
    public function getAll()
    {
        return Brand::orderBy('created_at', 'desc')->get();
    }

    public function findById($id)
    {
        return Brand::findOrFail($id);
    }

    public function update($id, array $data)
    {
        $brand = Brand::findOrFail($id);

        $slug = Str::slug($data['name']);

        // Check nếu slug mới đã tồn tại (trừ chính nó)
        if (Brand::where('slug', $slug)->where('id', '!=', $id)->exists()) {
            throw ValidationException::withMessages([
                'name' => ['Tên thương hiệu đã tồn tại.']
            ]);
        }

        $data['slug'] = $slug;

        // Xử lý cập nhật ảnh nếu có
        if (isset($data['logo']) && $data['logo']) {
            // Xóa ảnh cũ nếu tồn tại
            if ($brand->logo && Storage::disk('public')->exists(str_replace('/storage/', '', $brand->logo))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $brand->logo));
            }

            $file = $data['logo'];
            $path = $file->store('brands', 'public');
            $data['logo'] = Storage::url($path);
        }

        $brand->update($data);
        return $brand;
    }

    public function delete($id)
    {
        $brand = Brand::findOrFail($id);

        // Xóa ảnh nếu có
        if ($brand->logo && Storage::disk('public')->exists(str_replace('/storage/', '', $brand->logo))) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $brand->logo));
        }

        return $brand->delete();
    }
}
