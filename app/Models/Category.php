<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'slug',
        'parent_id',
    ];

    // 🔁 Quan hệ danh mục cha
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // 🔁 Quan hệ danh mục con
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // quan hệ sản phẩm sau này
    // public function products()
    // {
    //     return $this->hasMany(Product::class);
    // }
}
