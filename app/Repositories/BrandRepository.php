<?php

namespace App\Repositories;

use App\Models\Brand;
use App\Interfaces\BrandRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Facades\Image;

class BrandRepository implements BrandRepositoryInterface
{
    /**
     * Resize, chuyển sang .webp và lưu ảnh logo mới
     */
    private function handleLogoUpload($file, $oldPath = null)
    {
        // Xoá ảnh cũ nếu tồn tại
        if ($oldPath) {
            $oldRelativePath = str_replace('/storage/', '', $oldPath);
            if (Storage::disk('public')->exists($oldRelativePath)) {
                Storage::disk('public')->delete($oldRelativePath);
            }
        }

        // Resize và convert ảnh sang WebP
        $image = Image::make($file)
            ->resize(300, 300, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->encode('webp', 80); // 80% chất lượng, cân bằng tốc độ & độ nét

        $filename = 'brands/' . uniqid('brand_') . '.webp';

        // Lưu vào storage/app/public/brands
        Storage::disk('public')->put($filename, $image);

        return Storage::url($filename); // Trả về dạng /storage/brands/xxx.webp
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

        if (!empty($data['logo'])) {
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

        if (!empty($data['logo'])) {
            $data['logo'] = $this->handleLogoUpload($data['logo'], $brand->logo);
        }

        $brand->update($data);
        return $brand;
    }

    public function delete($id)
    {
        $brand = $this->findById($id);

        if ($brand->logo) {
            $relativePath = str_replace('/storage/', '', $brand->logo);
            if (Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->delete($relativePath);
            }
        }

        return $brand->delete();
    }

    public function paginate($perPage = 10, $search = null, $country = null)
    {
        $query = Brand::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($country) {
            $query->where('country', $country);
        }

        return $query->select(['id', 'name', 'slug', 'logo', 'country'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function getDistinctCountries()
    {
        return Brand::select('country')
            ->whereNotNull('country')
            ->distinct()
            ->pluck('country');
    }
}
