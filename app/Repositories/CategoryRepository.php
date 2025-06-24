<?php

namespace App\Repositories;

use App\Models\Category;
use App\Interfaces\CategoryRepositoryInterface;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function store(array $data)
    {
        $slug = Str::slug($data['name']);

        if (Category::where('slug', $slug)->exists()) {
            throw ValidationException::withMessages(['name' => ['Tên danh mục đã tồn tại.']]);
        }

        $data['slug'] = $slug;

        // Nếu không gửi status thì mặc định là 1 (hiển thị)
        $data['status'] = $data['status'] ?? 1;

        return Category::create($data);
    }

    public function getAll()
    {
        return Category::orderByDesc('created_at')->get();
    }

    public function paginate($perPage, $search = null, $status = null)
    {
        $query = Category::with('parent')
            ->select('id', 'name', 'slug', 'parent_id', 'created_at', 'status');

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if (!is_null($status)) {
            $query->where('status', $status);
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    public function findById($id)
    {
        return Category::with('parent')->findOrFail($id);
    }

    public function update($id, array $data)
    {
        $category = $this->findById($id);
        $slug = Str::slug($data['name']);

        if (Category::where('slug', $slug)->where('id', '!=', $id)->exists()) {
            throw ValidationException::withMessages(['name' => ['Tên danh mục đã tồn tại.']]);
        }

        $data['slug'] = $slug;

        $category->update($data);

        return $category;
    }

    public function delete($id)
    {
        return Category::destroy($id);
    }
}
