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
     * Xử lý lưu logo mới: resize, chuyển sang WebP và xoá logo cũ nếu có
     */
    private function handleLogoUpload($file, $oldPath = null)
    {
        if ($oldPath) {
            $oldRelativePath = str_replace('/storage/', '', $oldPath);
            if (Storage::disk('public')->exists($oldRelativePath)) {
                Storage::disk('public')->delete($oldRelativePath);
            }
        }

        $image = Image::make($file)
            ->resize(300, 300, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->encode('webp', 80);

        $filename = 'brands/' . uniqid('brand_') . '.webp';
        Storage::disk('public')->put($filename, $image);

        return Storage::url($filename); // Trả về đường dẫn public /storage/brands/...
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
        $data['status'] = $data['status'] ?? 1; // Mặc định là hiển thị

        if (!empty($data['logo'])) {
            $data['logo'] = $this->handleLogoUpload($data['logo']);
        }

        return Brand::create($data);
    }

    public function getAll()
    {
        return Brand::where('status', 1)
            ->select(['id', 'name', 'slug', 'logo', 'country'])
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
        $data['status'] = $data['status'] ?? $brand->status;

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

    public function paginate($perPage = 10, $search = null, $country = null, $status = null)
    {
        $query = Brand::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($country) {
            $query->where('country', $country);
        }

        if (!is_null($status)) {
            $query->where('status', $status);
        }

        return $query->select(['id', 'name', 'slug', 'logo', 'country', 'status'])
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
