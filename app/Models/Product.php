<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'price',
        'original_price',
        'quantity',
        'sold',
        'category_id',
        'brand_id',
        'status',
    ];
    protected $appends = ['thumbnail_url'];


    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    //lay anh thumbnail
    public function getThumbnailUrlAttribute()
    {
        $thumbnail = $this->images->firstWhere('is_thumbnail', 1);
        return $thumbnail ? $thumbnail->image_url : optional($this->images->first())->image_url;
    }
}
