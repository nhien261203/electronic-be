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
}
